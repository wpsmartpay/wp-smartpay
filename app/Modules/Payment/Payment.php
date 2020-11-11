<?php

namespace SmartPay\Modules\Payment;

use SmartPay\Models\Product;

use SmartPay\Http\Controllers\Rest\Admin\PaymentController;
use WP_REST_Server;

class Payment
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);

        add_action('wp_ajax_smartpay_process_payment', [$this, 'ajax_process_payment']);

        add_action('wp_ajax_nopriv_smartpay_process_payment', [$this, 'ajax_process_payment']);
    }

    public function adminScripts()
    {
        //
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
                'callback'  => [$paymentController, 'view'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$paymentController, 'update'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$paymentController, 'delete'],
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
        ));
    }

    private function _get_payment_data($_data)
    {
        $payment_type = $_data['smartpay_payment_type'] ?? '';

        switch ($payment_type) {

            case 'product_purchase':

                $product_id = $_data['smartpay_product_id'] ?? '';

                $variation_id = $_data['smartpay_product_variation_id'] ?? '';

                $product = Product::where('id', $product_id)->first();

                if (empty($product_id) || empty($product)) return [];

                $product_price = $product->sale_price ?? $product->base_price;

                if (count($product->variations) > 0 && !empty($variation_id)) {

                    $variation = Product::where('id', $variation_id)->first();

                    return array(
                        'product_id'        => $product_id,
                        'variation_id'      => $variation_id,
                        'variation_name'    => $variation->name,
                        'product_price'     => $product_price,
                        'additional_amount' => $variation->additional_amount,
                        'total_amount'      => $product_price + $variation->additional_amount,
                    );
                } else {

                    return array(
                        'product_id'    => $product->ID,
                        'product_price' => $product_price,
                        'total_amount'  => $product_price,
                    );
                }
                break;

            case 'form_payment':

                $form_id = $_data['smartpay_form_id'] ?? '';

                $form = smartpay_get_form($form_id);

                if (empty($form_id) || empty($form)) return [];

                return [
                    'form_id' => $form->ID,
                    'total_amount' => $_data['smartpay_form_amount'] ?? 0,
                ];
                break;

            default:
                return [];
                break;
        }
    }

    private function _get_payment_customer($_data)
    {
        $customer = new SmartPay_Customer($_data['smartpay_email']);

        if ($customer->ID) {
            $customer_id = $customer->ID;
        } else {
            $customer->user_id      = is_user_logged_in() ? get_current_user_id() : 0;
            $customer->first_name   = $_data['smartpay_first_name'];
            $customer->last_name    = $_data['smartpay_last_name'];
            $customer->email        = $_data['smartpay_email'];

            $customer_id = $customer->insert();
        }

        return [
            'customer_id' => $customer_id ?? 0,
            'first_name'  => $_data['smartpay_first_name'] ?? '',
            'last_name'   => $_data['smartpay_last_name'] ?? '',
            'email'       => $_data['smartpay_email'] ?? '',
        ];
    }
}