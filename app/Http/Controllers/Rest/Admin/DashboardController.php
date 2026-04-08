<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

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
     * Get full dashboard summary data.
     *
     * Accepts an optional `period` query param: today | week | month (default: month).
     * Period-sensitive data (stats, top products, top forms) is filtered to that window.
     * Monthly chart and recent payments are always unfiltered.
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

        $date_range = $this->resolve_date_range( $period );

        return new WP_REST_Response( [
            'period'          => $period,
            'totals'          => smartpay_dashboard_get_totals(),
            'period_stats'    => smartpay_dashboard_get_period_stats( $date_range ),
            'monthly_chart'   => smartpay_dashboard_get_monthly_chart(),
            'top_products'    => smartpay_dashboard_get_top_products( $date_range ),
            'top_forms'       => smartpay_dashboard_get_top_forms( $date_range ),
            'recent_payments' => smartpay_dashboard_get_recent_payments(),
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
}
