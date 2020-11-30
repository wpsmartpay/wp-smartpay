<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use WP_REST_Request;
use WP_REST_Response;

class ReportController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
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

    /**
     * Get monthly report
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {

        return new \WP_REST_Response([
            'monthly_report' => $this->monthlyReport(),
            'recent_payments' => $this->recentPayments(),
        ]);
    }

    public function monthlyReport()
    {
        $total_days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

        $report = [];
        foreach (range(1, $total_days) as $i) {
            $index = $i . '-' . date('m-y');
            $report[] = ['date' => $index, 'product_purchase' => 0, 'form_payment' => 0];
        }

        $startDate = date('Y-m-01') . ' 00:00:00';
        $endDate = current_time('mysql');

        $report_data = Payment::whereBetween('completed_at', $startDate, $endDate)->where('status', Payment::COMPLETED)->orderBy('id', 'DESC')->get();

        foreach ($report_data as $index => $data) {
            if (!$data->completed_at) continue;

            $date = date('j', strtotime($data->completed_at));
            // FIXME
            $report[$date][$data->getType()] += $data->amount ?? 0;
        }

        return $report;
    }

    public function recentPayments()
    {
        return Payment::where('status', Payment::COMPLETED)->orderBy('id', 'DESC')->limit(20)->get();
    }
}
