<?php

namespace SmartPay\Modules\Payment;

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
    }

    function ajax_process_payment()
    {
        // TODO: Convert response to JSON
        if (!isset($_POST['data']['smartpay_action']) || 'smartpay_process_payment' != sanitize_text_field($_POST['data']['smartpay_action'])) {
            echo '<p class="text-danger">Payment process action not acceptable!</p>';
            die();
        }

        if (!isset($_POST['data']['smartpay_process_payment']) || !wp_verify_nonce($_POST['data']['smartpay_process_payment'], 'smartpay_process_payment')) {
            echo '<p class="text-danger">Payment process nonce verification failed!</p>';
            die();
        }

        $validate = $this->_checkValidation(sanitize_post($_POST['data']) ?? []);

        if (!$validate) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

        $payment_data = $this->_prepare_payment_data(sanitize_post($_POST['data']) ?? []);

        if (!$payment_data || !is_array($payment_data)) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

        // Set session payment data
        // FIXME: Reform validation
        //smartpay_set_session_payment_data($payment_data);

        // Send payment data toprocess gateway payment
        $this->_process_gateway_payment($payment_data);

        die();
    }

    private function _process_gateway_payment($paymentData, $ajax = true)
    {
        $gateway = sanitize_text_field($_POST['data']['smartpay_gateway']) ?? '';

        if (!is_string($gateway) || !smartpay_is_gateway_active($gateway)) {
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
            'date'          => date('Y-m-d H:i:s', time()),
            'amount'        => $payment_data['total_amount'] ?? '',
            'currency'      => smartpay_get_currency() ?? 'USD',
            'gateway'       => $_data['smartpay_gateway'],
            'customer'      => $this->_get_payment_customer($_data),
            'email'         => $_data['smartpay_email'],
            'key'           => strtolower(md5($_data['smartpay_email'] . date('Y-m-d H:i:s') . rand(1, 10))),
            'extra'         => $extra
        ), $_data);
    }

    private function _get_payment_data($_data)
    {
        $payment_type = $_data['smartpay_payment_type'] ?? '';

        switch ($payment_type) {

            case 'product_purchase':

                $productId = $_data['smartpay_product_id'] ?? 0;

                $product = Product::where('id', $productId)->first();

                if (empty($productId) || empty($product)) return [];

                return [
                    'product_id'    => $product->id,
                    'product_price' => $product->price,
                    'total_amount'  => $_data['smartpay_product_price'],
                    'billing_type'   => $_data['smartpay_product_billing_type']
                ];

                break;

            case 'form_payment':

                $formId = $_data['smartpay_form_id'] ?? 0;

                $form = Form::where('id', $formId)->first();

                if (empty($formId) || empty($form)) return [];

                return [
                    'form_id' => $form->id,
                    'total_amount' => $_data['smartpay_amount'] ?? 0,
                    'billing_type'   => $_data['smartpay_form_billing_type']
                ];
                break;

            default:
                return [];
                break;
        }
    }

    private function _get_payment_customer($_data)
    {
        $customer = Customer::where('email', $_data['smartpay_email'])->first();

        if ($customer->id) {
            $customer_id = $customer->id;
        } else {
            $customer = new Customer();
            $customer->user_id      = is_user_logged_in() ? get_current_user_id() : 0;
            $customer->first_name   = $_data['smartpay_first_name'];
            $customer->last_name    = $_data['smartpay_last_name'];
            $customer->email        = $_data['smartpay_email'];

            $customer->save();
            $customer_id = $customer->id;
        }

        return [
            'customer_id' => $customer_id ?? 0,
            'first_name'  => $_data['smartpay_first_name'] ?? '',
            'last_name'   => $_data['smartpay_last_name'] ?? '',
            'email'       => $_data['smartpay_email'] ?? '',
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
        $payment->extra          = apply_filters('smartpay_payment_extra_data', $paymentData['extra']);
        $payment->mode           = smartpay_is_test_mode() ? 'test' : 'live';
        $payment->parent_id      = !empty($paymentData['parent_id']) ? absint($paymentData['parent_id']) : 0;
        $payment->status         = $paymentData['status'] ?? 'pending';

        $payment->save();

        // TODO: Move to model
        do_action('smartpay_after_insert_payment', $payment);

        if (!empty($payment->id)) {
            $create_user = new CreateUser();
            $create_user->create_user($payment);
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

        $payment->completed_at = current_time('mysql');
        $payment->save();

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
    }
}