<?php
defined( 'ABSPATH' ) || exit;

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Form;
use SmartPay\Models\Payment;
use SmartPay\Models\Product;

/**
 * Get all-time entity counts: customers, products, forms.
 *
 * @return array{ total_customers: int, total_products: int, total_forms: int }
 */
function smartpay_dashboard_get_totals(): array {
	global $wpdb;

	$prefix = esc_sql( $wpdb->prefix );

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total_customers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_customers" );
	$total_products  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_products" );
	$total_forms     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}smartpay_forms" );
    // phpcs:enable

	return array(
		'total_customers' => $total_customers,
		'total_products'  => $total_products,
		'total_forms'     => $total_forms,
	);
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
function smartpay_dashboard_get_period_stats( array $date_range ): array {
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

	return array(
		'revenue'         => $revenue,
		'completed_count' => $completed_count,
		'pending_count'   => $pending_count,
		'failed_count'    => $failed_count,
	);
}

/**
 * Get daily revenue breakdown for each day of the current month.
 *
 * @return array
 */
function smartpay_dashboard_get_monthly_chart(): array {
	$total_days = cal_days_in_month( CAL_GREGORIAN, (int) gmdate( 'm' ), (int) gmdate( 'Y' ) );

	$chart = array();
	foreach ( range( 1, $total_days ) as $day ) {
		$chart[] = array(
			'date'             => $day . '-' . gmdate( 'm-y' ),
			'product_purchase' => 0,
			'form_payment'     => 0,
		);
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
function smartpay_dashboard_get_top_products( array $date_range ): array {
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
		return array();
	}

	$product_ids = array_map( 'intval', array_column( $rows, 'product_id' ) );
	$products    = Product::whereIn( 'id', $product_ids )->get();
	$title_map   = array();

	foreach ( $products as $product ) {
		$title_map[ (int) $product->id ] = $product->title;
	}

	$result = array();
	foreach ( $rows as $row ) {
		$id       = (int) $row->product_id;
		$result[] = array(
			'product_id' => $id,
			'title'      => $title_map[ $id ] ?? null,
			'total'      => (float) $row->total,
			'count'      => (int) $row->count,
		);
	}

	return $result;
}

/**
 * Get the top 5 forms by total revenue within the given date range.
 *
 * @param array $date_range { start: string, end: string }.
 * @return array
 */
function smartpay_dashboard_get_top_forms( array $date_range ): array {
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
		return array();
	}

	$form_ids  = array_map( 'intval', array_column( $rows, 'form_id' ) );
	$forms     = Form::whereIn( 'id', $form_ids )->get();
	$title_map = array();

	foreach ( $forms as $form ) {
		$title_map[ (int) $form->id ] = $form->title;
	}

	$result = array();
	foreach ( $rows as $row ) {
		$id       = (int) $row->form_id;
		$result[] = array(
			'form_id' => $id,
			'title'   => $title_map[ $id ] ?? null,
			'total'   => (float) $row->total,
			'count'   => (int) $row->count,
		);
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
	$buckets = array();

	if ( 'today' === $period ) {
		for ( $h = 0; $h <= 23; $h++ ) {
			$key             = sprintf( '%02d', $h );
			$buckets[ $key ] = array(
				'label'   => sprintf( '%dh', $h ),
				'revenue' => 0.0,
				'orders'  => 0,
			);
		}
	} elseif ( 'week' === $period ) {
		$start_ts = strtotime( $start );
		for ( $d = 0; $d < 7; $d++ ) {
			$day_ts          = strtotime( "+{$d} days", $start_ts );
			$key             = gmdate( 'Y-m-d', $day_ts );
			$buckets[ $key ] = array(
				'label'   => gmdate( 'D', $day_ts ),
				'revenue' => 0.0,
				'orders'  => 0,
			);
		}
	} else {
		// month: one bucket per day from the 1st up through today.
		$ts     = strtotime( $start );
		$end_ts = min( strtotime( $end ), $now_ts );
		while ( $ts <= $end_ts ) {
			$key             = gmdate( 'Y-m-d', $ts );
			$buckets[ $key ] = array(
				'label'   => gmdate( 'M d', $ts ),
				'revenue' => 0.0,
				'orders'  => 0,
			);
			$ts              = strtotime( '+1 day', $ts );
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
function smartpay_dashboard_get_recent_payments(): array {
	$month_start = gmdate( 'Y-m-01 00:00:00' );
	$month_end   = current_time( 'mysql' );

	$payments = Payment::where( 'status', Payment::COMPLETED )
		->whereBetween( 'completed_at', $month_start, $month_end )
		->orderBy( 'id', 'DESC' )
		->limit( 10 )
		->get();

	// Collect product and form IDs in one pass so we can batch-load titles.
	$product_ids = array();
	$form_ids    = array();

	foreach ( $payments as $payment ) {
		$data = $payment->data ?? array();
		if ( Payment::PRODUCT_PURCHASE === $payment->type && ! empty( $data['product_id'] ) ) {
			$product_ids[] = (int) $data['product_id'];
		} elseif ( Payment::FORM_PAYMENT === $payment->type && ! empty( $data['form_id'] ) ) {
			$form_ids[] = (int) $data['form_id'];
		}
	}

	// Batch load titles.
	$product_titles = array();
	if ( ! empty( $product_ids ) ) {
		$products = Product::whereIn( 'id', array_unique( $product_ids ) )->get();
		foreach ( $products as $p ) {
			$product_titles[ (int) $p->id ] = $p->title;
		}
	}

	$form_titles = array();
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
	$result     = array();

	foreach ( $payments as $payment ) {
		$data        = $payment->data ?? array();
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

		$result[] = array(
			'id'           => (int) $payment->id,
			'amount'       => (float) $payment->amount,
			'email'        => $payment->email,
			'type'         => $payment->type,
			'source_name'  => $source_name,
			'source_url'   => $source_url,
			'source_type'  => $source_type,
			'completed_at' => $payment->completed_at,
			'view_url'     => $admin_base . '#/payments/' . (int) $payment->id,
		);
	}

	return $result;
}

/*
|--------------------------------------------------------------------------
| Frontend user dashboard ([smartpay_dashboard])
|--------------------------------------------------------------------------
| User-scoped queries for the WooCommerce-style account area. Every query
| filters by the resolved customer id so one user can never see another
| user's rows. Raw status is selected (lowercase) so views can map it to a
| CSS modifier without tripping the Payment/Subscription status accessor,
| which capitalises the value.
*/

/**
 * Resolve the SmartPay customer id for the current logged-in user.
 *
 * @return int Customer id, or 0 when none / not logged in.
 */
function smartpay_dashboard_current_customer_id(): int {
	if ( ! is_user_logged_in() || ! function_exists( 'smartpay_get_customer_by_user_id' ) ) {
		return 0;
	}

	$customer = smartpay_get_customer_by_user_id( get_current_user_id() );

	return $customer ? (int) $customer->id : 0;
}

/**
 * Whether the subscriptions area is available.
 *
 * Subscriptions are a Pro feature backed by the `smartpay_subscriptions` table,
 * which only exists when the Pro plugin is active. On a free-only site the table
 * is absent, so the Subscriptions tab and its queries are skipped to avoid a
 * fatal "table doesn't exist" error.
 */
function smartpay_dashboard_subscriptions_enabled(): bool {
	static $enabled = null;

	if ( null !== $enabled ) {
		return $enabled;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'smartpay_subscriptions';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	$enabled = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

	return $enabled;
}

/**
 * Allowed dashboard sub-views. Each maps a `sp_view` query value to a partial.
 * The `subscriptions` view is only available when Pro (the subscriptions table)
 * is present.
 *
 * @return string[]
 */
function smartpay_dashboard_views(): array {
	$views = array( 'overview', 'orders', 'order' );

	if ( smartpay_dashboard_subscriptions_enabled() ) {
		$views[] = 'subscriptions';
	}

	return $views;
}

/**
 * Current order id from the query string (for the `order` detail view).
 */
function smartpay_dashboard_current_order_id(): int {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only detail view, no state change.
	return isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
}

/**
 * Build the in-dashboard order detail URL for a payment.
 */
function smartpay_dashboard_order_url( int $payment_id ): string {
	$url = smartpay_dashboard_view_url( 'order' );

	return $url ? esc_url_raw( add_query_arg( 'id', $payment_id, $url ) ) : '';
}

/**
 * Fetch a single payment scoped to the customer (returns null if not theirs).
 *
 * @return object|null
 */
function smartpay_get_user_payment( int $customer_id, int $payment_id ) {
	global $wpdb;

	if ( $customer_id <= 0 || $payment_id <= 0 ) {
		return null;
	}

	$prefix = $wpdb->prefix;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, uuid, type, amount, currency, gateway, status, created_at, completed_at, data, email
			 FROM {$prefix}smartpay_payments
			 WHERE id = %d AND customer_id = %d",
			$payment_id,
			$customer_id
		)
	);
	// phpcs:enable

	return $row ?: null;
}

/**
 * Current dashboard sub-view, sanitised against the allow-list.
 */
function smartpay_dashboard_current_view(): string {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch, no state change.
	$view = isset( $_GET['sp_view'] ) ? sanitize_key( wp_unslash( $_GET['sp_view'] ) ) : 'overview';

	return in_array( $view, smartpay_dashboard_views(), true ) ? $view : 'overview';
}

/**
 * Build a full-page-load URL for a dashboard sub-view on the current page.
 */
function smartpay_dashboard_view_url( string $view ): string {
	// Always resolve the configured dashboard page so the nav works from any
	// page that renders the sidebar (e.g. the profile page) — never the current
	// page, which would keep the user stuck on that page.
	$settings = get_option( 'smartpay_settings', array() );
	$base     = get_permalink( (int) ( $settings['customer_dashboard_page'] ?? 0 ) );

	if ( ! $base ) {
		$base = get_permalink();
	}

	if ( 'overview' === $view ) {
		return $base ? esc_url_raw( $base ) : '';
	}

	return $base ? esc_url_raw( add_query_arg( 'sp_view', $view, $base ) ) : '';
}

/**
 * Public receipt URL for a payment uuid (success/receipt page).
 */
function smartpay_dashboard_receipt_url( string $uuid ): string {
	if ( '' === $uuid || ! function_exists( 'smartpay_get_payment_success_page_uri' ) ) {
		return '';
	}

	return esc_url_raw( add_query_arg( 'smartpay-payment', $uuid, smartpay_get_payment_success_page_uri() ) );
}

/**
 * Get a customer's payments, newest first.
 *
 * @param int $customer_id Resolved customer id.
 * @param int $limit       0 = no limit.
 * @return array<int,object>
 */
function smartpay_get_user_payments( int $customer_id, int $limit = 0 ): array {
	global $wpdb;

	if ( $customer_id <= 0 ) {
		return array();
	}

	$prefix = $wpdb->prefix;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
	$sql  = "SELECT id, uuid, type, amount, currency, gateway, status, created_at, completed_at
             FROM {$prefix}smartpay_payments
             WHERE customer_id = %d
             ORDER BY created_at DESC, id DESC";
	$args = array( $customer_id );

	if ( $limit > 0 ) {
		$sql   .= ' LIMIT %d';
		$args[] = $limit;
	}

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
    // phpcs:enable

	return is_array( $rows ) ? $rows : array();
}

/**
 * Get a customer's subscriptions, newest first.
 *
 * Covers both payment-backed subscriptions (linked via parent_payment_id) and
 * manual subscriptions (linked directly via customer_id). Currency is pulled
 * from the parent payment when present, else the store default.
 *
 * @param int $customer_id Resolved customer id.
 * @return array<int,object>
 */
function smartpay_get_user_subscriptions( int $customer_id ): array {
	global $wpdb;

	if ( $customer_id <= 0 || ! smartpay_dashboard_subscriptions_enabled() ) {
		return array();
	}

	$prefix = $wpdb->prefix;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT s.id, s.period, s.initial_amount, s.recurring_amount, s.status,
                    s.expiration, s.created_at, s.gateway, s.bill_times, p.currency, p.uuid AS payment_uuid
             FROM {$prefix}smartpay_subscriptions s
             LEFT JOIN {$prefix}smartpay_payments p ON p.id = s.parent_payment_id
             WHERE s.customer_id = %d
                OR s.parent_payment_id IN (
                    SELECT id FROM {$prefix}smartpay_payments WHERE customer_id = %d
                )
             ORDER BY s.created_at DESC, s.id DESC",
			$customer_id,
			$customer_id
		)
	);
    // phpcs:enable

	return is_array( $rows ) ? $rows : array();
}

/**
 * Aggregate summary numbers for the dashboard overview.
 *
 * @param int $customer_id Resolved customer id.
 * @return array{
 *   total_payments:int, completed_payments:int, total_spent:float, currency:string,
 *   active_subscriptions:int, total_subscriptions:int, recent_payments:array<int,object>
 * }
 */
function smartpay_get_user_dashboard_summary( int $customer_id ): array {
	global $wpdb;

	$default_currency = function_exists( 'smartpay_get_option' ) ? (string) smartpay_get_option( 'currency', 'USD' ) : 'USD';

	$empty = array(
		'total_payments'       => 0,
		'completed_payments'   => 0,
		'total_spent'          => 0.0,
		'currency'             => $default_currency,
		'active_subscriptions' => 0,
		'total_subscriptions'  => 0,
		'recent_payments'      => array(),
	);

	if ( $customer_id <= 0 ) {
		return $empty;
	}

	$prefix = $wpdb->prefix;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$totals = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
                COUNT(*) AS total_payments,
                SUM( CASE WHEN status = %s THEN 1 ELSE 0 END ) AS completed_payments,
                SUM( CASE WHEN status = %s THEN amount ELSE 0 END ) AS total_spent,
                MAX( currency ) AS currency
             FROM {$prefix}smartpay_payments
             WHERE customer_id = %d",
			'completed',
			'completed',
			$customer_id
		)
	);

	$sub_totals = null;
	if ( smartpay_dashboard_subscriptions_enabled() ) {
		$sub_totals = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
                COUNT(*) AS total_subscriptions,
                SUM( CASE WHEN status = %s THEN 1 ELSE 0 END ) AS active_subscriptions
             FROM {$prefix}smartpay_subscriptions
             WHERE customer_id = %d
                OR parent_payment_id IN (
                    SELECT id FROM {$prefix}smartpay_payments WHERE customer_id = %d
                )",
				'active',
				$customer_id,
				$customer_id
			)
		);
	}
    // phpcs:enable

	return array(
		'total_payments'       => (int) ( $totals->total_payments ?? 0 ),
		'completed_payments'   => (int) ( $totals->completed_payments ?? 0 ),
		'total_spent'          => (float) ( $totals->total_spent ?? 0 ),
		'currency'             => $totals->currency ?: $default_currency,
		'active_subscriptions' => (int) ( $sub_totals->active_subscriptions ?? 0 ),
		'total_subscriptions'  => (int) ( $sub_totals->total_subscriptions ?? 0 ),
		'recent_payments'      => smartpay_get_user_payments( $customer_id, 5 ),
	);
}

/**
 * Map a raw lowercase status to a badge CSS modifier + human label.
 *
 * @return array{class:string,label:string}
 */
function smartpay_dashboard_status_badge( string $status ): array {
	$status = strtolower( $status );

	$map = array(
		'completed'  => __( 'Completed', 'smartpay' ),
		'active'     => __( 'Active', 'smartpay' ),
		'processing' => __( 'Processing', 'smartpay' ),
		'trialling'  => __( 'Trialling', 'smartpay' ),
		'pending'    => __( 'Pending', 'smartpay' ),
		'failing'    => __( 'Failing', 'smartpay' ),
		'failed'     => __( 'Failed', 'smartpay' ),
		'refunded'   => __( 'Refunded', 'smartpay' ),
		'cancelled'  => __( 'Cancelled', 'smartpay' ),
		'expired'    => __( 'Expired', 'smartpay' ),
		'abandoned'  => __( 'Abandoned', 'smartpay' ),
		'revoked'    => __( 'Revoked', 'smartpay' ),
		'suspended'  => __( 'Suspended', 'smartpay' ),
	);

	return array(
		'class' => 'is-' . ( $status ?: 'pending' ),
		'label' => $map[ $status ] ?? ucfirst( $status ?: 'pending' ),
	);
}
