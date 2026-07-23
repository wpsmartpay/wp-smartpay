<?php
defined('ABSPATH') || exit;

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

    $prefix = esc_sql( $wpdb->prefix );

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $total_customers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_customers" );
    $total_products  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_products" );
    $total_forms     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_forms" );
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

    $prefix = esc_sql( $wpdb->prefix );
    $start  = $date_range['start'];
    $end    = $date_range['end'];

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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

    $prefix = esc_sql( $wpdb->prefix );

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
    // phpcs:enable

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

    $prefix = esc_sql( $wpdb->prefix );

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
    // phpcs:enable

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
 * Get time-series chart data for Revenue and Orders grouped by the given period.
 *
 * today → 24 hourly buckets (00–23)
 * week  → 7 daily buckets (Mon–Sun)
 * month → one bucket per calendar day up to today
 *
 * @param array  $date_range { start: string, end: string } in site local time.
 * @param string $period     'today' | 'week' | 'month'.
 * @return array<array{ label: string, revenue: float, orders: int }>
 */
function smartpay_dashboard_get_chart_data( array $date_range, string $period ): array {
    global $wpdb;

    $prefix = esc_sql( $wpdb->prefix );
    $start  = $date_range['start'];
    $end    = $date_range['end'];
    $now_ts = current_time( 'timestamp' );

    // ── Build empty bucket map ────────────────────────────────────────────────
    $buckets = [];

    if ( 'today' === $period ) {
        for ( $h = 0; $h <= 23; $h++ ) {
            $key             = sprintf( '%02d', $h );
            $buckets[ $key ] = [
                'label'   => sprintf( '%dh', $h ),
                'revenue' => 0.0,
                'orders'  => 0,
            ];
        }
    } elseif ( 'week' === $period ) {
        $start_ts = strtotime( $start );
        for ( $d = 0; $d < 7; $d++ ) {
            $day_ts          = strtotime( "+{$d} days", $start_ts );
            $key             = gmdate( 'Y-m-d', $day_ts );
            $buckets[ $key ] = [
                'label'   => gmdate( 'D', $day_ts ),
                'revenue' => 0.0,
                'orders'  => 0,
            ];
        }
    } else {
        // month: one bucket per day from the 1st up through today.
        $ts     = strtotime( $start );
        $end_ts = min( strtotime( $end ), $now_ts );
        while ( $ts <= $end_ts ) {
            $key             = gmdate( 'Y-m-d', $ts );
            $buckets[ $key ] = [
                'label'   => gmdate( 'M d', $ts ),
                'revenue' => 0.0,
                'orders'  => 0,
            ];
            $ts = strtotime( '+1 day', $ts );
        }
    }

    // ── Query completed payments and fill buckets ─────────────────────────────
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if ( 'today' === $period ) {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT( completed_at, '%%H' ) AS bucket,
                        COALESCE( SUM( amount ), 0 )        AS revenue,
                        COUNT(*)                             AS orders
                 FROM {$prefix}smartpay_payments
                 WHERE status = %s AND completed_at BETWEEN %s AND %s
                 GROUP BY bucket",
                Payment::COMPLETED,
                $start,
                $end
            )
        );
    } else {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE( completed_at )                AS bucket,
                        COALESCE( SUM( amount ), 0 )        AS revenue,
                        COUNT(*)                             AS orders
                 FROM {$prefix}smartpay_payments
                 WHERE status = %s AND completed_at BETWEEN %s AND %s
                 GROUP BY bucket",
                Payment::COMPLETED,
                $start,
                $end
            )
        );
    }
    // phpcs:enable

    foreach ( $rows as $row ) {
        $key = (string) $row->bucket;
        if ( isset( $buckets[ $key ] ) ) {
            $buckets[ $key ]['revenue'] = (float) $row->revenue;
            $buckets[ $key ]['orders']  = (int) $row->orders;
        }
    }

    return array_values( $buckets );
}

/**
 * Get the 10 most recent completed payments, enriched with source name and admin URL.
 *
 * Each row includes:
 *   - id, amount, email, type, completed_at
 *   - source_name  : product or form title (null when not found)
 *   - view_url     : SPA deep-link to the payment detail page
 *
 * @return array
 */
function smartpay_dashboard_get_recent_payments(): array
{
    $month_start = gmdate( 'Y-m-01 00:00:00' );
    $month_end   = current_time( 'mysql' );

    $payments = Payment::where( 'status', Payment::COMPLETED )
        ->whereBetween( 'completed_at', $month_start, $month_end )
        ->orderBy( 'id', 'DESC' )
        ->limit( 10 )
        ->get();

    // Collect product and form IDs in one pass so we can batch-load titles.
    $product_ids = [];
    $form_ids    = [];

    foreach ( $payments as $payment ) {
        $data = $payment->data ?? [];
        if ( Payment::PRODUCT_PURCHASE === $payment->type && ! empty( $data['product_id'] ) ) {
            $product_ids[] = (int) $data['product_id'];
        } elseif ( Payment::FORM_PAYMENT === $payment->type && ! empty( $data['form_id'] ) ) {
            $form_ids[] = (int) $data['form_id'];
        }
    }

    // Batch load titles.
    $product_titles = [];
    if ( ! empty( $product_ids ) ) {
        $products = Product::whereIn( 'id', array_unique( $product_ids ) )->get();
        foreach ( $products as $p ) {
            $product_titles[ (int) $p->id ] = $p->title;
        }
    }

    $form_titles = [];
    if ( ! empty( $form_ids ) ) {
        // Legacy forms (smartpay_forms DB table).
        $forms = Form::whereIn( 'id', array_unique( $form_ids ) )->get();
        foreach ( $forms as $f ) {
            $form_titles[ (int) $f->id ] = $f->title;
        }

        // Native forms (smartpay_form CPT) — fallback for IDs not in legacy table.
        foreach ( array_unique( $form_ids ) as $form_id ) {
            if ( isset( $form_titles[ $form_id ] ) ) {
                continue;
            }
            $post = get_post( $form_id );
            if ( $post && 'smartpay_form' === $post->post_type ) {
                $form_titles[ $form_id ] = $post->post_title;
            }
        }
    }

    $admin_base = admin_url( 'admin.php?page=smartpay' );
    $result     = [];

    foreach ( $payments as $payment ) {
        $data        = $payment->data ?? [];
        $source_name = null;
        $source_url  = null;
        $source_type = null;

        if ( Payment::PRODUCT_PURCHASE === $payment->type && ! empty( $data['product_id'] ) ) {
            $source_name = $product_titles[ (int) $data['product_id'] ] ?? null;
            $source_url  = $admin_base . '#/products/' . (int) $data['product_id'];
            $source_type = 'Product';
        } elseif ( Payment::FORM_PAYMENT === $payment->type && ! empty( $data['form_id'] ) ) {
            $source_name = $form_titles[ (int) $data['form_id'] ] ?? null;
            $source_url  = $admin_base . '#/payments?form=' . (int) $data['form_id'];
            $source_type = 'Form';
        }

        $result[] = [
            'id'          => (int) $payment->id,
            'amount'      => (float) $payment->amount,
            'email'       => $payment->email,
            'type'        => $payment->type,
            'source_name' => $source_name,
            'source_url'  => $source_url,
            'source_type'  => $source_type,
            'completed_at' => $payment->completed_at,
            'view_url'    => $admin_base . '#/payments/' . (int) $payment->id,
        ];
    }

    return $result;
}
