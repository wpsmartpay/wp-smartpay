<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Form;

use WP_REST_Request;
use WP_REST_Response;

class FormController extends RestController
{
    /**
     * Check permissions for the posts.
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
        $forms = Form::all();

        return new WP_REST_Response($forms);
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
        $form->title = $request->title ?? 'Untitled form';
        $form->body = $request->body;
        $form->status = Form::PUBLISH;
        $form->save();

        return new WP_REST_Response(['form' => $form, 'message' => 'Form created']);
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
            return new WP_REST_Response(['message' => 'Form not found'], 404);
        }

        return new WP_REST_Response($form);
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
            return new WP_REST_Response(['message' => 'Form not found'], 404);
        }

        $request = json_decode($request->get_body());

        $form->title = $request->title ?? 'Untitled form';
        $form->body = $request->body;
        $form->status = Form::PUBLISH;
        $form->save();

        return new WP_REST_Response(['form' => $form, 'message' => 'Form updated']);
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
            return new WP_REST_Response(['message' => 'Form not found'], 404);
        }

        $form->delete();
        return new WP_REST_Response(['message' => 'Form deleted']);
    }
}