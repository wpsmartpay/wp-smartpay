<?php

namespace SmartPay\Http\Controllers\Rest\Admin;
defined('ABSPATH') || exit;

use SmartPay\Http\Controllers\RestController;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

class DashboardController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return true|\WP_Error
     */
    public function middleware( $request )
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new \WP_Error(
                'rest_forbidden',
                esc_html__( 'You cannot view the resource.', 'smartpay' ),
                [ 'status' => is_user_logged_in() ? 403 : 401 ]
            );
        }

        return true;
    }

    /**
     * Get dashboard summary data.
     *
     * Accepts an optional `period` query param: today | week | month (default: month).
     * Returns current and previous period stats (for % change badges), all-time totals,
     * and the 10 most recent completed payments.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return WP_REST_Response
     */
    public function index( WP_REST_Request $request ): WP_REST_Response
    {
        $period = sanitize_key( $request->get_param( 'period' ) ?: 'month' );
        if ( ! in_array( $period, [ 'today', 'week', 'month' ], true ) ) {
            $period = 'month';
        }

        $date_range      = $this->resolve_date_range( $period );
        $prev_date_range = $this->resolve_previous_date_range( $period );

        return new WP_REST_Response( [
            'period'                => $period,
            'date_range'            => $date_range,
            'previous_date_range'   => $prev_date_range,
            'totals'                => smartpay_dashboard_get_totals(),
            'period_stats'          => smartpay_dashboard_get_period_stats( $date_range ),
            'previous_period_stats' => smartpay_dashboard_get_period_stats( $prev_date_range ),
            'recent_payments'       => smartpay_dashboard_get_recent_payments(),
            'chart_data'            => smartpay_dashboard_get_chart_data( $date_range, $period ),
        ] );
    }

    /**
     * Resolve start/end datetimes for the requested period (site local time).
     *
     * @param string $period  'today' | 'week' | 'month'.
     * @return array{ start: string, end: string }
     */
    private function resolve_date_range( string $period ): array
    {
        $now       = current_time( 'mysql' );
        $timestamp = current_time( 'timestamp' );

        switch ( $period ) {
            case 'today':
                $start = gmdate( 'Y-m-d', $timestamp ) . ' 00:00:00';
                break;
            case 'week':
                $monday_ts = strtotime( 'monday this week', $timestamp );
                $start     = gmdate( 'Y-m-d', $monday_ts ) . ' 00:00:00';
                break;
            case 'month':
            default:
                $start = gmdate( 'Y-m', $timestamp ) . '-01 00:00:00';
                break;
        }

        return [ 'start' => $start, 'end' => $now ];
    }

    /**
     * Resolve the full start/end window for the period immediately before the current one.
     *
     * today → yesterday 00:00:00–23:59:59
     * week  → previous Mon 00:00:00 → previous Sun 23:59:59
     * month → 1st of previous month → last day of previous month 23:59:59
     *
     * @param string $period 'today' | 'week' | 'month'.
     * @return array{ start: string, end: string }
     */
    private function resolve_previous_date_range( string $period ): array
    {
        $timestamp = current_time( 'timestamp' );

        switch ( $period ) {
            case 'today':
                $ts    = strtotime( '-1 day', $timestamp );
                $start = gmdate( 'Y-m-d', $ts ) . ' 00:00:00';
                $end   = gmdate( 'Y-m-d', $ts ) . ' 23:59:59';
                break;

            case 'week':
                $start = gmdate( 'Y-m-d', strtotime( 'monday last week', $timestamp ) ) . ' 00:00:00';
                $end   = gmdate( 'Y-m-d', strtotime( 'sunday last week', $timestamp ) ) . ' 23:59:59';
                break;

            case 'month':
            default:
                $start = gmdate( 'Y-m-d', strtotime( 'first day of previous month', $timestamp ) ) . ' 00:00:00';
                $end   = gmdate( 'Y-m-d', strtotime( 'last day of previous month', $timestamp ) ) . ' 23:59:59';
                break;
        }

        return [ 'start' => $start, 'end' => $end ];
    }
}
