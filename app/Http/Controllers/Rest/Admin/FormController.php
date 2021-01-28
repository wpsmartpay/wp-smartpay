<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Form;
use WP_REST_Request;
use WP_REST_Response;

class FormController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
     * @return \WP_Error|bool
     */
    public function middleware(WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    /**
     * Get all parent forms
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $forms = Form::orderBy('id', 'DESC')->get();

        return new WP_REST_Response(['forms' => $forms]);
    }

    /**
     * Create new form
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        $request = json_decode($request->get_body());

        $form = new Form();
        $form->title    = $request->title ?? 'Untitled form';
        $form->amounts  = $request->amounts;
        $form->body     = $request->body;
        $form->fields   = $request->fields;
        $form->settings = $request->settings;
        $form->status   = Form::PUBLISH;
        $form->save();

        return new WP_REST_Response(['form' => $form, 'message' => __('Form created', 'smartpay')]);
    }

    /**
     * Get a single form with variations
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $form = Form::find($request->get_param('id'));

        if (!$form) {
            return new WP_REST_Response(['message' => __('Form not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['form' => $form]);
    }

    /**
     * Update the form
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $form = Form::find($request->get_param('id'));

        if (!$form) {
            return new WP_REST_Response(['message' => __('Form not found', 'smartpay')], 404);
        }

        $request = json_decode($request->get_body());

        $form->title    = $request->title ?? __('Untitled form', 'smartpay');
        $form->amounts  = $request->amounts;
        $form->body     = $request->body;
        $form->fields   = $request->fields;
        $form->settings = $request->settings;
        $form->status   = $request->status ?? Form::PUBLISH;
        $form->save();

        return new WP_REST_Response(['form' => $form, 'message' => __('Form updated', 'smartpay')]);
    }

    /**
     * Delete the form
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $form = Form::find($request->get_param('id'));

        if (!$form) {
            return new WP_REST_Response(['message' => __('Form not found', 'smartpay')], 404);
        }

        $extraFields = $form->extra ?? null;
        if( is_array($extraFields) && array_key_exists('form_preview_page_id',$extraFields) ) {
            wp_delete_post( $extraFields['form_preview_page_id'] );
        }
        $form->delete();
        return new WP_REST_Response(['message' => __('Form deleted', 'smartpay')]);
    }
}