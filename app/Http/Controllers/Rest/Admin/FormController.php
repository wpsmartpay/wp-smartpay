<?php

namespace SmartPay\Http\Controllers\Rest\Admin;
defined('ABSPATH') || exit;

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
        $data = json_decode($request->get_body(), true);

        $status = sanitize_text_field($data['status'] ?? 'publish');
        if (!in_array($status, ['publish', 'draft'], true)) {
            return new WP_REST_Response(['message' => esc_html__('Invalid form status.', 'smartpay')], 422);
        }

        $form = new Form();
        $form->title    = sanitize_text_field($data['title'] ?? 'Untitled form');
        $form->body     = wp_kses_post($data['body'] ?? '');
        $form->amounts  = $this->sanitize_amounts((array) ($data['amounts'] ?? []));
        $form->fields   = $this->sanitize_recursive((array) ($data['fields'] ?? []));
        $form->settings = $this->sanitize_recursive((array) ($data['settings'] ?? []));
        $form->extra    = $this->sanitize_recursive((array) ($data['extra'] ?? []));
        $form->status   = $status;
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

        $data = json_decode($request->get_body(), true);

        $status = sanitize_text_field($data['status'] ?? 'publish');
        if (!in_array($status, ['publish', 'draft'], true)) {
            return new WP_REST_Response(['message' => esc_html__('Invalid form status.', 'smartpay')], 422);
        }

        $form->title    = sanitize_text_field($data['title'] ?? __('Untitled form', 'smartpay'));
        $form->body     = wp_kses_post($data['body'] ?? '');
        $form->amounts  = $this->sanitize_amounts((array) ($data['amounts'] ?? []));
        $form->fields   = $this->sanitize_recursive((array) ($data['fields'] ?? []));
        $form->settings = $this->sanitize_recursive((array) ($data['settings'] ?? []));
        $form->extra    = $this->sanitize_recursive((array) ($data['extra'] ?? []));
        $form->status   = $status;
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

    /**
     * Sanitize the amounts array.
     * Each amount: key (sanitize_key), label (sanitize_text_field),
     * amount (max 0 float), billing_type (enum), billing_period (sanitize_text_field).
     *
     * @param array $amounts
     * @return array
     */
    private function sanitize_amounts(array $amounts): array
    {
        $allowed_billing_types = ['One Time', 'Subscription'];

        return array_map(function ($item) use ($allowed_billing_types) {
            if (!is_array($item)) {
                return [];
            }
            $billing_type = sanitize_text_field($item['billing_type'] ?? 'One Time');
            if (!in_array($billing_type, $allowed_billing_types, true)) {
                $billing_type = 'One Time';
            }
            return [
                'key'            => sanitize_key($item['key'] ?? ''),
                'label'          => sanitize_text_field($item['label'] ?? ''),
                'amount'         => max(0, (float) ($item['amount'] ?? 0)),
                'billing_type'   => $billing_type,
                'billing_period' => sanitize_text_field($item['billing_period'] ?? ''),
            ] + $this->sanitize_recursive(array_diff_key($item, array_flip(['key', 'label', 'amount', 'billing_type', 'billing_period'])));
        }, array_values($amounts));
    }

    /**
     * Recursively sanitize an array: sanitize_text_field on string leaves,
     * cast numeric leaves to their type, recurse into nested arrays.
     *
     * @param array $data
     * @return array
     */
    private function sanitize_recursive(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean_key = sanitize_text_field((string) $key);
            if (is_array($value)) {
                $clean[$clean_key] = $this->sanitize_recursive($value);
            } elseif (is_bool($value)) {
                $clean[$clean_key] = $value;
            } elseif (is_int($value)) {
                $clean[$clean_key] = (int) $value;
            } elseif (is_float($value)) {
                $clean[$clean_key] = (float) $value;
            } else {
                $clean[$clean_key] = sanitize_text_field((string) $value);
            }
        }
        return $clean;
    }
}
