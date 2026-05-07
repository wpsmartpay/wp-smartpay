<?php

namespace SmartPay\Modules\Payment;
defined('ABSPATH') || exit;

use Ramsey\Uuid\Uuid;
use SmartPay\Models\Product;
use SmartPay\Models\Form;

use SmartPay\Http\Controllers\Rest\Admin\PaymentController;
use SmartPay\Models\Customer;
use SmartPay\Models\Payment as PaymentModel;
use SmartPay\Modules\Customer\CreateUser;
use WP_REST_Server;

class Payment
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);

        $this->app->addAction('wp_ajax_smartpay_process_payment', [$this, 'ajax_process_payment']);
        $this->app->addAction('wp_ajax_nopriv_smartpay_process_payment', [$this, 'ajax_process_payment']);
        $this->app->addAction('smartpay_update_payment_status', [$this, 'onPaymentComplete'], 10, 3);
        $this->app->addAction('smartpay_update_payment_status', [$this, 'onPaymentCancel'], 10, 3);
    }

    public function registerRestRoutes()
    {
        $paymentController = $this->app->make(PaymentController::class);

        register_rest_route('smartpay/v1', 'payments', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$paymentController, 'index'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$paymentController, 'store'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1', 'payments/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$paymentController, 'show'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$paymentController, 'update'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$paymentController, 'destroy'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1', 'payments/(?P<id>[\d]+)/logs', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$paymentController, 'logs'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$paymentController, 'addLog'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
        ]);
    }

    function ajax_process_payment()
    {
        // TODO: Convert response to JSON
	    // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!isset($_POST['data']['smartpay_action']) || 'smartpay_process_payment' != sanitize_text_field(wp_unslash($_POST['data']['smartpay_action']))) {
            echo '<p class="text-danger">Payment process action not acceptable!</p>';
            die();
        }

        if (!isset($_POST['data']['smartpay_process_payment']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['smartpay_process_payment'])), 'smartpay_process_payment')) {
            echo '<p class="text-danger">Payment process nonce verification failed!</p>';
            die();
        }

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $validate = $this->_checkValidation(sanitize_post($_POST['data']) ?? []);

        if (!$validate) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $payment_data = $this->_prepare_payment_data(sanitize_post($_POST['data']) ?? []);

        if (!$payment_data || !is_array($payment_data)) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

        // Set session payment data
        // FIXME: Reform validation
        //smartpay_set_session_payment_data($payment_data);

        // Fire action before processing payment
        do_action('smartpay_before_payment_processing', $payment_data);

        // Goal gate — block payment if form goal is met and stop_orders is active
        $form_id = $payment_data['payment_data']['form_id'] ?? 0;
        if ( $form_id > 0 ) {
            $progress = smartpay_calculate_goal_progress( $form_id );
            $settings = get_post_meta( $form_id, '_smartpay_settings', true );
            $settings = is_string( $settings ) ? json_decode( $settings, true ) : ( $settings ?: [] );
            $goal     = $settings['goal'] ?? [];

            if ( ! empty( $goal['enabled'] ) ) {
                $blocked = false;
                $stop_message = '';

                // Block if goal reached and stop_orders behavior is set
                if ( ( $goal['behaviorWhenGoalMet'] ?? 'allow_orders' ) === 'stop_orders'
                    && ( $progress['goal_reached'] ?? false )
                ) {
                    $blocked     = true;
                    $stop_message = $goal['goalMetMessage'] ?? __( 'This form has reached its goal and is no longer accepting payments.', 'smartpay' );
                }

                // Block if stop collection date is set and today is past that date
                if ( ! $blocked && ! empty( $goal['stopCollectionDate'] ) ) {
                    $today      = gmdate( 'Y-m-d' );
                    $cutoff     = $goal['stopCollectionDate'];
                    if ( $cutoff && $today > $cutoff ) {
                        $blocked     = true;
                        $stop_message = $goal['goalMetMessage'] ?: __( 'This form is no longer accepting payments.', 'smartpay' );
                    }
                }

                if ( $blocked ) {
                    echo '<p class="text-danger">' . esc_html( $stop_message ) . '</p>';
                    die();
                }
            }
        }

        // Send payment data toprocess gateway payment
        $this->_process_gateway_payment($payment_data);

        die();
    }

    private function _process_gateway_payment($paymentData, $ajax = true)
    {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
        $gateway = isset($_POST['data']['smartpay_gateway']) ? sanitize_text_field(wp_unslash($_POST['data']['smartpay_gateway'])) : '';

        // Zero-amount payments must always use the free gateway regardless of what
        // the frontend sent — no payment processor should be called for free orders.
        if ( isset( $paymentData['amount'] ) && floatval( $paymentData['amount'] ) == 0 ) {
            $gateway = 'free';
        }

        if ('free'!==$gateway && (!is_string($gateway) || !smartpay_is_gateway_active($gateway))) {
            echo '<p class="text-danger">Gateway is not active or not exist!</p>';
            return;
        }

        $paymentData['gateway_nonce'] = wp_create_nonce('smartpay-gateway');

        // gateway must match the ID used when registering the gateway
        if ($ajax) {
            do_action('smartpay_' . $gateway . '_ajax_process_payment', $paymentData);
        } else {
            do_action('smartpay_' . $gateway . '_process_payment', $paymentData);
        }
    }

    private function _checkValidation($_data)
    {
        // TODO: Reform validation
        $email = $_data['smartpay_email'] ?? '';
        if (!is_email($email)) return false;

        return true;
    }

    private function _prepare_payment_data($_data)
    {
        $payment_data = $this->_get_payment_data($_data);
        // TODO: Make another method to get extra

        $extra = [];
        if ('form_payment' === $_data['smartpay_payment_type']) {
            $extra['form_data'] = $_data['smartpay_form_data'] ?? [];
            $extra['form_fields'] = Form::find($_data['smartpay_form_id'])->fields ?? [];
        }

        return apply_filters('smartpay_prepare_payment_data', array(
            'payment_type'  => $_data['smartpay_payment_type'],
            'payment_data'  => $payment_data,
            'date'          => gmdate('Y-m-d H:i:s', time()),
            'amount'        => $payment_data['total_amount'] ?? '',
            'currency'      => smartpay_get_currency() ?? 'USD',
            'gateway'       => $_data['smartpay_gateway'],
            'customer'      => $this->_get_payment_customer($_data),
            'email'         => $_data['smartpay_email'],
			'mobile'        => $_data['smartpay_payment_mobile'] ?? '',
            'key'           => strtolower(md5($_data['smartpay_email'] . gmdate('Y-m-d H:i:s') . wp_rand(1, 10))),
            'extra'         => $extra
        ), $_data);
    }

    private function _get_payment_data($_data)
    {
        $payment_type = $_data['smartpay_payment_type'] ?? '';

        // declare variables
        $additional_amount = 0;
        $total_billing_cycle = 0;

        switch ($payment_type) {

            case 'product_purchase':

                $productId = $_data['smartpay_product_id'] ?? 0;

                $product = Product::where('id', $productId)->first();

                if (empty($productId) || empty($product)) return [];

                $additional_amount = $product->extra['additional_charge'] ?? 0;
				if (empty($product->extra['additional_charge'])) {
					$additional_amount = 0;
				}
                $total_billing_cycle = $product->extra['total_billing_cycle'] ?? 0;
	            if (empty($product->extra['total_billing_cycle'])) {
		            $total_billing_cycle = 0;
	            }

                $payment_default_data = [
                    'product_id'    => $product->id,
                    'product_price' => $product->price,
                    'total_amount'  => $_data['smartpay_product_price'],
                    'billing_type'   => $_data['smartpay_product_billing_type'],
                ];

	            if ($_data['smartpay_product_billing_type'] == \SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION) {
		            $payment_default_data['additional_info'] =  [
			            'additional_charge' => $additional_amount,
			            'total_billing_cycle' => $total_billing_cycle,
		            ];
	            }

                return smartpay_get_additional_payment_data($payment_default_data);

            case 'form_payment':

                $formId = $_data['smartpay_form_id'] ?? 0;

                $form = Form::where('id', $formId)->first();

                foreach ($form->amounts as $amount) {
                    if ($amount['key'] === $_data['smartpay_amount_key']) {
                        $additional_amount = $amount['additional_charge'];
                        $total_billing_cycle = $amount['total_billing_cycle'];
                        break;
                    }
                }

                if (empty($formId) || empty($form)) return [];

                $payment_data = [
                    'form_id'           => $form->id,
                    'total_amount'      => $_data['smartpay_amount'] ?? 0,
                    'billing_type'      => $_data['smartpay_form_billing_type'],
                    'is_custom_amount'  => $_data['smartpay_is_custom_amount'] ?? false,
                    ];

				if ($_data['smartpay_form_billing_type'] == \SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION) {
					if ( !filter_var($_data['smartpay_is_custom_amount'], FILTER_VALIDATE_BOOLEAN) ) {
						$payment_data['additional_info']  = [
							'additional_charge' => $additional_amount,
							'total_billing_cycle' => $total_billing_cycle,
						];
					}
				}

                return $payment_data;

            default:
                return [];
        }
    }

    private function _get_payment_customer($_data)
    {
        // Sanitize Data.
        $first_name = sanitize_text_field( $_data['smartpay_first_name'] ?? '' );
        $last_name  = sanitize_text_field( $_data['smartpay_last_name'] ?? '' );
        $email      = sanitize_email( sanitize_text_field( $_data['smartpay_email'] ?? '' ) );

        $customer = Customer::where('email', $email)->first();

        if ($customer && $customer->id) {
            $customer_id = $customer->id;
        } else {
            $customer = new Customer();
            $customer->user_id      = is_user_logged_in() ? get_current_user_id() : 0;
            $customer->first_name   = $first_name;
            $customer->last_name    = $last_name;
            $customer->email        = $email;

            $customer->save();
            $customer_id = $customer->id;
        }

        return [
            'customer_id' => $customer_id ?? 0,
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'email'       => $email,
        ];
    }

    public function insertPayment($paymentData)
    {
        if (empty($paymentData)) return;
        $payment = new \SmartPay\Models\Payment();
        $payment->type           = $paymentData['payment_type'];
        $payment->data           = $paymentData['payment_data'];
        $payment->amount         = $paymentData['amount'];
        $payment->currency       = $paymentData['currency'] ?? smartpay_get_currency();
        $payment->gateway        = $paymentData['gateway'] ?? smartpay_get_default_gateway();
        $payment->customer_id    = $paymentData['customer']['customer_id'];
        $payment->email          = $paymentData['email'];
        $payment->key            = $paymentData['key'];
        $payment->uuid            = Uuid::uuid4()->toString();
        $payment->extra          = apply_filters('smartpay_payment_extra_data', $paymentData['extra']);
        $payment->mode           = smartpay_is_test_mode() ? 'test' : 'live';
        $payment->parent_id      = !empty($paymentData['parent_id']) ? absint($paymentData['parent_id']) : 0;
        $payment->status         = $paymentData['status'] ?? 'pending';

        $payment->save();

        // TODO: Move to model
        do_action('smartpay_payment_created', $payment);

        if (!empty($payment->id)) {
            // check create WP user is enabled
            $enable_user_creation = isset(smartpay_get_settings()['create_wp_user']) && smartpay_get_settings()
                ['create_wp_user'];
            if ($enable_user_creation){
                CreateUser::create_user($payment);
            }
            // Set session payment id
            //smartpay_set_session_payment_id($payment->ID);

            // Attach payment to customer
            // $this->attach_customer_payment($payment);

            return $payment;
        }

        // Return false if no payment was inserted
        return false;
    }

    public function onPaymentComplete($payment, $newStatus, $oldStatus)
    {
        if (PaymentModel::COMPLETED !== $newStatus || $payment->completed_at) {
            return;
        }

        $completed_at = current_time('mysql');

        // Direct DB update avoids re-triggering the model's saving() lifecycle,
        // which would fire a duplicate status_changed log entry.
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $wpdb->prefix . 'smartpay_payments',
            array( 'completed_at' => $completed_at ),
            array( 'id'           => (int) $payment->id ),
            array( '%s' ),
            array( '%d' )
        );

        // Invalidate goal cache so progress bar reflects new completed payment.
        $form_id = $payment->data['form_id'] ?? 0;
        if ( $form_id > 0 && function_exists( 'smartpay_invalidate_goal_cache' ) ) {
            smartpay_invalidate_goal_cache( (int) $form_id );
        }
        $payment->completed_at = $completed_at;

        do_action('smartpay_payment_completed', $payment);
    }

    //trigger when payment is being cancelled
    public function onPaymentCancel($payment, $newStatus, $oldStatus)
    {
        if($newStatus == PaymentModel::PENDING && $oldStatus == PaymentModel::COMPLETED){
            do_action('smartpay_payment_cancelled', $payment);
        }elseif (in_array($newStatus, [PaymentModel::ABANDONED, PaymentModel::REVOKED, PaymentModel::REFUNDED])) {
            do_action('smartpay_payment_cancelled', $payment);
        }

        // Add specific hooks for different status changes
        if ($newStatus == PaymentModel::FAILED) {
            do_action('smartpay_payment_failed', $payment);
        } elseif ($newStatus == PaymentModel::REFUNDED) {
            do_action('smartpay_payment_refunded', $payment);
        } elseif ($newStatus == PaymentModel::ABANDONED) {
            do_action('smartpay_payment_abandoned', $payment);
        }
    }
}
