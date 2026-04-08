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
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.', 'smartpay'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    /**
     * Get all forms with pagination
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $page     = (int) $request->get_param('page') ?: 1;
        $per_page = (int) $request->get_param('per_page') ?: 10;
        $search   = sanitize_text_field($request->get_param('search') ?? '');
        $sort_by  = sanitize_text_field($request->get_param('sort_by') ?? 'id:desc');

        $query = Form::query();

        // Search
        if (!empty($search)) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        // Sorting
        $sort_parts = explode(':', $sort_by);
        $sort_column = in_array($sort_parts[0], ['id', 'title', 'created_at', 'updated_at']) ? $sort_parts[0] : 'id';
        $sort_order  = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'asc' ? 'ASC' : 'DESC';
        $query->orderBy($sort_column, $sort_order);

        // Get total count
        $total = $query->count();
        $last_page = max(1, (int) ceil($total / $per_page));

        // Paginate
        $offset = ($page - 1) * $per_page;
        $forms = $query->skip($offset)->take($per_page)->get();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $per_page, $total);

        return new WP_REST_Response([
            'forms' => [
                'data'         => $forms,
                'current_page' => $page,
                'per_page'     => $per_page,
                'last_page'    => $last_page,
                'total'        => $total,
                'from'         => $from,
                'to'           => $to,
            ],
        ]);
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
        $form->extra    = $request->extra ?? [];
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
        $form->extra   = $request->extra;
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

        $form->delete();
        return new WP_REST_Response(['message' => __('Form deleted', 'smartpay')]);
    }
}