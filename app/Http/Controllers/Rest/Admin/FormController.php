<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Form;
use WP_REST_Response;

class FormController extends RestController
{
    /**
     * Check permissions for the posts.
     *
     * @param \WP_REST_Request $request.
     */
    public function middleware(\WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    public function index(\WP_REST_Request $request)
    {
        return new WP_REST_Response(Form::all());
    }

    public function store(\WP_REST_Request $request)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');

        try {
            $request = json_decode($request->get_body());
            $form = new Form();
            $form->title = $request->title ?? __('Untitled Form', 'smartpay');
            $form->body = $request->body;
            $form->save();

            return new WP_REST_Response([
                'form'      => $form,
                'message'   => 'Form created'
            ]);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }
}