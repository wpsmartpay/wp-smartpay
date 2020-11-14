<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;

class ReportController extends RestController
{
    /**
     * Check permissions for the posts.
     *
     * @param \WP_REST_Request $request.
     */
    public function middleware($request)
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
        $total_days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

        $report = [];
        foreach (range(1, $total_days) as $i) {
            $index = $i . '-' . date('m-y');
            $report[] = ['date' => $index, 'product_purchase' => 0, 'form_payment' => 0];
        }

        $report_data = Payment::where('completed_at', '>=', date('Y-m-d') . ' 00:00:00')->where('status', 'completed')->get();

        foreach ($report_data as $index => $data) {
            if (!$data->completed_at) continue;

            $date = date('d', strtotime($data->completed_at));
            $report[$date][$data->type] += $data->amount ?? 0;
        }

        return new \WP_REST_Response($report);
    }
}