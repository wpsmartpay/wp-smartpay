<?php

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Form;
use SmartPay\Models\Payment;
use SmartPay\Models\Product;

/**
 * Get all-time entity counts: customers, products, forms.
 *
 * @return array{ total_customers: int, total_products: int, total_forms: int }
 */
function smartpay_dashboard_get_totals(): array
{
    global $wpdb;

    $prefix = $wpdb->prefix;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $total_customers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_customers" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $total_products  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_products" );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $total_forms     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_forms" );     // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    // phpcs:enable

    return [
        'total_customers' => $total_customers,
        'total_products'  => $total_products,
        'total_forms'     => $total_forms,
    ];
}

/**
 * Get payment stats filtered to the given date range.
 *
 * Revenue and completed count use completed_at; pending/failed use created_at
 * since those statuses never reach completed_at.
 *
 * @param array $date_range { start: string, end: string }.
 * @return array{ revenue: float, completed_count: int, pending_count: int, failed_count: int }
 */
function smartpay_dashboard_get_period_stats( array $date_range ): array
{
    global $wpdb;

    $prefix = $wpdb->prefix;
    $start  = $date_range['start'];
    $end    = $date_range['end'];

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $revenue = (float) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE( SUM( amount ), 0 )
             FROM {$prefix}smartpay_payments
             WHERE status = %s AND completed_at BETWEEN %s AND %s",
            Payment::COMPLETED,
            $start,
            $end
        )
    );

    $completed_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$prefix}smartpay_payments
             WHERE status = %s AND completed_at BETWEEN %s AND %s",
            Payment::COMPLETED,
            $start,
            $end
        )
    );

    $pending_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$prefix}smartpay_payments
             WHERE status = %s AND created_at BETWEEN %s AND %s",
            Payment::PENDING,
            $start,
            $end
        )
    );

    $failed_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$prefix}smartpay_payments
             WHERE status = %s AND created_at BETWEEN %s AND %s",
            Payment::FAILED,
            $start,
            $end
        )
    );
    // phpcs:enable

    return [
        'revenue'         => $revenue,
        'completed_count' => $completed_count,
        'pending_count'   => $pending_count,
        'failed_count'    => $failed_count,
    ];
}

/**
 * Get daily revenue breakdown for each day of the current month.
 *
 * @return array
 */
function smartpay_dashboard_get_monthly_chart(): array
{
    $total_days = cal_days_in_month( CAL_GREGORIAN, (int) gmdate( 'm' ), (int) gmdate( 'Y' ) );

    $chart = [];
    foreach ( range( 1, $total_days ) as $day ) {
        $chart[] = [
            'date'             => $day . '-' . gmdate( 'm-y' ),
            'product_purchase' => 0,
            'form_payment'     => 0,
        ];
    }

    $start_date = gmdate( 'Y-m-01' ) . ' 00:00:00';
    $end_date   = current_time( 'mysql' );

    $payments = Payment::whereBetween( 'completed_at', $start_date, $end_date )
        ->where( 'status', Payment::COMPLETED )
        ->orderBy( 'completed_at', 'DESC' )
        ->get();

    foreach ( $payments as $payment ) {
        if ( ! $payment->completed_at ) {
            continue;
        }

        $day_index = (int) gmdate( 'j', strtotime( $payment->completed_at ) ) - 1;
        $type      = $payment->getType();

        if ( isset( $chart[ $day_index ][ $type ] ) ) {
            $chart[ $day_index ][ $type ] += $payment->amount ?? 0;
        }
    }

    return $chart;
}

/**
 * Get the top 5 products by total revenue within the given date range.
 *
 * @param array $date_range { start: string, end: string }.
 * @return array
 */
function smartpay_dashboard_get_top_products( array $date_range ): array
{
    global $wpdb;

    $prefix = $wpdb->prefix;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT
                CAST( JSON_UNQUOTE( JSON_EXTRACT( data, '$.product_id' ) ) AS UNSIGNED ) AS product_id,
                SUM( amount ) AS total,
                COUNT(*) AS count
             FROM {$prefix}smartpay_payments
             WHERE type = %s
               AND status = %s
               AND completed_at BETWEEN %s AND %s
               AND JSON_EXTRACT( data, '$.product_id' ) IS NOT NULL
             GROUP BY product_id
             ORDER BY total DESC
             LIMIT 5",
            Payment::PRODUCT_PURCHASE,
            Payment::COMPLETED,
            $date_range['start'],
            $date_range['end']
        )
    );

    if ( empty( $rows ) ) {
        return [];
    }

    $product_ids = array_map( 'intval', array_column( $rows, 'product_id' ) );
    $products    = Product::whereIn( 'id', $product_ids )->get();
    $title_map   = [];

    foreach ( $products as $product ) {
        $title_map[ (int) $product->id ] = $product->title;
    }

    $result = [];
    foreach ( $rows as $row ) {
        $id       = (int) $row->product_id;
        $result[] = [
            'product_id' => $id,
            'title'      => $title_map[ $id ] ?? null,
            'total'      => (float) $row->total,
            'count'      => (int) $row->count,
        ];
    }

    return $result;
}

/**
 * Get the top 5 forms by total revenue within the given date range.
 *
 * @param array $date_range { start: string, end: string }.
 * @return array
 */
function smartpay_dashboard_get_top_forms( array $date_range ): array
{
    global $wpdb;

    $prefix = $wpdb->prefix;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT
                CAST( JSON_UNQUOTE( JSON_EXTRACT( data, '$.form_id' ) ) AS UNSIGNED ) AS form_id,
                SUM( amount ) AS total,
                COUNT(*) AS count
             FROM {$prefix}smartpay_payments
             WHERE type = %s
               AND status = %s
               AND completed_at BETWEEN %s AND %s
               AND JSON_EXTRACT( data, '$.form_id' ) IS NOT NULL
             GROUP BY form_id
             ORDER BY total DESC
             LIMIT 5",
            Payment::FORM_PAYMENT,
            Payment::COMPLETED,
            $date_range['start'],
            $date_range['end']
        )
    );

    if ( empty( $rows ) ) {
        return [];
    }

    $form_ids  = array_map( 'intval', array_column( $rows, 'form_id' ) );
    $forms     = Form::whereIn( 'id', $form_ids )->get();
    $title_map = [];

    foreach ( $forms as $form ) {
        $title_map[ (int) $form->id ] = $form->title;
    }

    $result = [];
    foreach ( $rows as $row ) {
        $id       = (int) $row->form_id;
        $result[] = [
            'form_id' => $id,
            'title'   => $title_map[ $id ] ?? null,
            'total'   => (float) $row->total,
            'count'   => (int) $row->count,
        ];
    }

    return $result;
}

/**
 * Get the 10 most recent completed payments.
 *
 * @return array
 */
function smartpay_dashboard_get_recent_payments(): array
{
    $payments = Payment::where( 'status', Payment::COMPLETED )
        ->orderBy( 'id', 'DESC' )
        ->limit( 10 )
        ->get();

    $result = [];
    foreach ( $payments as $payment ) {
        $result[] = [
            'id'         => (int) $payment->id,
            'amount'     => (float) $payment->amount,
            'email'      => $payment->email,
            'type'       => $payment->type,
            'created_at' => $payment->created_at,
        ];
    }

    return $result;
}
