<?php
/**
 * Donor helpers.
 *
 * A donor is a lens on the customers table, not a table of its own: a
 * customer becomes a donor with their first completed gift. A gift is a
 * completed payment on a donation form (a form in any campaign, or a form
 * flagged "donation form"), plus the renewals of such a payment. Purchases
 * never count as giving.
 *
 * Donor type is derived from gifts — the admin never sets it:
 * - monthly    — has an active recurring gift (wins over repeat)
 * - repeat     — 2+ completed gifts, no active recurring gift
 * - first_time — exactly one completed gift
 *
 * Per-gift donor fields live in the payment's `extra.donation` JSON:
 * anonymous, comment, frequency, tribute (and `hidden`, set by Pro moderation).
 *
 * @package SmartPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Donor type slugs and labels.
 *
 * @return array<string,string>
 */
function smartpay_donor_types(): array {
	return array(
		'first_time' => __( 'First-time', 'smartpay' ),
		'repeat'     => __( 'Repeat', 'smartpay' ),
		'monthly'    => __( 'Monthly', 'smartpay' ),
	);
}

/**
 * Pro's subscriptions table name, or '' when it does not exist (no Monthly type then).
 *
 * @return string
 */
function smartpay_donor_subscriptions_table(): string {
	global $wpdb;
	static $table = null;

	if ( null === $table ) {
		$name = $wpdb->prefix . 'smartpay_subscriptions';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema probe, memoised.
		$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $name ) ) === $name ? $name : '';
	}

	return $table;
}

/**
 * Build the derived donor table SQL (one row per donor with type + totals).
 *
 * @param int[]  $scope_form_ids Forms whose gifts are summed (a campaign's forms, or all donation forms).
 * @param int[]  $all_form_ids   Every donation form (drives the global donor type).
 * @param string $flag           '' | 'comments' | 'anonymous' — only donors with such a gift in scope.
 * @param string $search         Name/email search.
 * @param int[]  $customer_ids   Limit to these customers ([] = all).
 * @return string SQL, already prepared.
 */
function smartpay_donor_base_sql( array $scope_form_ids, array $all_form_ids, string $flag = '', string $search = '', array $customer_ids = array() ): string {
	global $wpdb;

	$payments  = $wpdb->prefix . 'smartpay_payments';
	$customers = $wpdb->prefix . 'smartpay_customers';
	$scope     = smartpay_gift_payments_where_sql( $scope_form_ids, 'p' );
	$all       = smartpay_gift_payments_where_sql( $all_form_ids, 'p' );

	if ( 'anonymous' === $flag ) {
		$scope .= $wpdb->prepare( ' AND p.extra LIKE %s', '%' . $wpdb->esc_like( '"donation":{' ) . '%' . $wpdb->esc_like( '"anonymous":true' ) . '%' );
	} elseif ( 'comments' === $flag ) {
		$scope .= $wpdb->prepare( ' AND p.extra LIKE %s', '%' . $wpdb->esc_like( '"donation":{' ) . '%' . $wpdb->esc_like( '"comment":"' ) . '_%' );
	}

	$subs = smartpay_donor_subscriptions_table();
	if ( $subs ) {
		$gift_parent = smartpay_payments_form_match_sql( $all_form_ids, 'p.data' );
		$monthly     = "SELECT p.customer_id, MAX(p.amount) AS amt FROM {$subs} s JOIN {$payments} p ON p.id = s.parent_payment_id
			WHERE s.status IN ('active','trialling') AND {$gift_parent} GROUP BY p.customer_id";
	} else {
		$monthly = 'SELECT NULL AS customer_id, NULL AS amt FROM DUAL WHERE 1=0';
	}

	$where        = '';
	$customer_ids = array_values( array_filter( array_map( 'absint', $customer_ids ) ) );
	if ( $customer_ids ) {
		$where = ' WHERE c.id IN (' . implode( ',', $customer_ids ) . ')';
	}
	if ( '' !== $search ) {
		$like   = '%' . $wpdb->esc_like( $search ) . '%';
		$where .= ( $where ? ' AND' : ' WHERE' ) . $wpdb->prepare(
			" ( c.email LIKE %s OR c.first_name LIKE %s OR c.last_name LIKE %s OR CONCAT(c.first_name, ' ', c.last_name) LIKE %s )",
			$like,
			$like,
			$like,
			$like
		);
	}

	return "SELECT c.id, c.first_name, c.last_name, c.email, c.user_id, c.extra, c.created_at,
			s.gifts, s.total, s.latest, a.gifts AS all_gifts, a.total AS all_total, a.first_gift AS donor_since,
			m.amt AS monthly_amount,
			CASE WHEN m.customer_id IS NOT NULL THEN 'monthly' WHEN a.gifts >= 2 THEN 'repeat' ELSE 'first_time' END AS donor_type
		FROM {$customers} c
		JOIN ( SELECT p.customer_id, COUNT(*) AS gifts, SUM(p.amount) AS total, MAX(p.created_at) AS latest
			FROM {$payments} p WHERE {$scope} GROUP BY p.customer_id ) s ON s.customer_id = c.id
		JOIN ( SELECT p.customer_id, COUNT(*) AS gifts, SUM(p.amount) AS total, MIN(p.created_at) AS first_gift
			FROM {$payments} p WHERE {$all} GROUP BY p.customer_id ) a ON a.customer_id = c.id
		LEFT JOIN ( {$monthly} ) m ON m.customer_id = c.id{$where}";
}

/**
 * Query donors.
 *
 * Args: form_ids (int[] scope, defaults to every donation form), customer_ids (int[]), type ('' or a
 * smartpay_donor_types() key), flag ('' | comments | anonymous), search,
 * orderby (latest | total | gifts | name | oldest), page, per_page (max 100).
 *
 * @param array $args Query arguments, see above.
 * @return array{data: array, total: int, current_page: int, per_page: int, last_page: int, from: int, to: int, counts: array}
 */
function smartpay_query_donors( array $args = array() ): array {
	global $wpdb;

	$all_ids  = smartpay_get_donation_form_ids();
	$scope    = isset( $args['form_ids'] ) ? array_map( 'intval', (array) $args['form_ids'] ) : $all_ids;
	$type     = array_key_exists( $args['type'] ?? '', smartpay_donor_types() ) ? $args['type'] : '';
	$flag     = in_array( $args['flag'] ?? '', array( 'comments', 'anonymous' ), true ) ? $args['flag'] : '';
	$search   = sanitize_text_field( (string) ( $args['search'] ?? '' ) );
	$page     = max( 1, (int) ( $args['page'] ?? 1 ) );
	$per_page = min( 100, max( 1, (int) ( $args['per_page'] ?? 20 ) ) );
	$orders   = array(
		'latest' => 't.latest DESC',
		'total'  => 't.total DESC',
		'gifts'  => 't.gifts DESC',
		'name'   => 't.first_name ASC, t.last_name ASC',
		'oldest' => 't.latest ASC',
	);
	$order    = $orders[ $args['orderby'] ?? 'latest' ] ?? $orders['latest'];

	$empty = array(
		'data'         => array(),
		'total'        => 0,
		'current_page' => $page,
		'per_page'     => $per_page,
		'last_page'    => 1,
		'from'         => 0,
		'to'           => 0,
		'counts'       => array_fill_keys( array_merge( array( 'all' ), array_keys( smartpay_donor_types() ) ), 0 ),
	);

	if ( empty( $scope ) || empty( $all_ids ) ) {
		return $empty;
	}

	$base       = smartpay_donor_base_sql( $scope, $all_ids, $flag, $search, (array) ( $args['customer_ids'] ?? array() ) );
	$type_where = $type ? $wpdb->prepare( ' WHERE t.donor_type = %s', $type ) : '';
	$offset     = ( $page - 1 ) * $per_page;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- $base/$type_where are prepared above; $order is whitelisted.
	$count_rows = $wpdb->get_results( "SELECT t.donor_type, COUNT(*) AS n FROM ( {$base} ) t GROUP BY t.donor_type", ARRAY_A );
	$rows       = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM ( {$base} ) t{$type_where} ORDER BY {$order}, t.id DESC LIMIT %d, %d", $offset, $per_page ),
		ARRAY_A
	);
	// phpcs:enable

	$counts = $empty['counts'];
	foreach ( (array) $count_rows as $row ) {
		$counts[ $row['donor_type'] ] = (int) $row['n'];
		$counts['all']               += (int) $row['n'];
	}

	$total = $type ? $counts[ $type ] : $counts['all'];

	return array(
		'data'         => smartpay_hydrate_donor_rows( (array) $rows, $scope ),
		'total'        => $total,
		'current_page' => $page,
		'per_page'     => $per_page,
		'last_page'    => max( 1, (int) ceil( $total / $per_page ) ),
		'from'         => $total > 0 ? $offset + 1 : 0,
		'to'           => min( $offset + $per_page, $total ),
		'counts'       => $counts,
	);
}

/**
 * Add per-gift details (latest comment, anonymous count, campaigns) to donor rows.
 *
 * @param array $rows  Raw rows from smartpay_donor_base_sql().
 * @param int[] $scope Forms in scope.
 * @return array
 */
function smartpay_hydrate_donor_rows( array $rows, array $scope ): array {
	global $wpdb;

	if ( empty( $rows ) ) {
		return array();
	}

	$ids          = array_map( 'intval', wp_list_pluck( $rows, 'id' ) );
	$payments     = $wpdb->prefix . 'smartpay_payments';
	$where        = smartpay_gift_payments_where_sql( $scope, 'p' );
	$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $where is prepared; ids use placeholders.
	$gifts = $wpdb->get_results( $wpdb->prepare( "SELECT p.id, p.customer_id, p.parent_id, p.data, p.extra, p.created_at FROM {$payments} p WHERE p.customer_id IN ({$placeholders}) AND {$where} ORDER BY p.created_at DESC", $ids ), ARRAY_A );

	$by_customer = array();
	foreach ( (array) $gifts as $gift ) {
		$by_customer[ (int) $gift['customer_id'] ][] = $gift;
	}

	$campaign_names = array();
	$form_campaign  = array();

	return array_map(
		static function ( array $row ) use ( $by_customer, &$campaign_names, &$form_campaign ) {
			$comment    = '';
			$comment_id = 0;
			$anonymous  = 0;
			$hidden     = false;
			$pending    = false;
			$campaigns  = array();

			foreach ( $by_customer[ (int) $row['id'] ] ?? array() as $gift ) {
				$donation = smartpay_get_payment_donation( $gift['extra'] );

				if ( ! empty( $donation['anonymous'] ) ) {
					++$anonymous;
				}
				if ( '' === $comment && '' !== $donation['comment'] ) {
					$comment    = $donation['comment'];
					$comment_id = (int) $gift['id'];
					$hidden     = ! empty( $donation['hidden'] );
					$pending    = ! $donation['approved'];
				}

				$data    = json_decode( (string) $gift['data'], true );
				$form_id = (int) ( is_array( $data ) ? ( $data['form_id'] ?? 0 ) : 0 );
				if ( $form_id ) {
					if ( ! array_key_exists( $form_id, $form_campaign ) ) {
						$form_campaign[ $form_id ] = smartpay_get_form_campaign_id( $form_id );
					}
					$cid = $form_campaign[ $form_id ];
					if ( $cid && ! isset( $campaigns[ $cid ] ) ) {
						if ( ! array_key_exists( $cid, $campaign_names ) ) {
							$term                   = get_term( $cid, SMARTPAY_CAMPAIGN_TAXONOMY );
							$campaign_names[ $cid ] = $term instanceof \WP_Term ? $term->name : '';
						}
						$campaigns[ $cid ] = array(
							'id'    => $cid,
							'title' => $campaign_names[ $cid ],
						);
					}
				}
			}

			$extra = json_decode( (string) $row['extra'], true );

			return array(
				'id'              => (int) $row['id'],
				'first_name'      => (string) $row['first_name'],
				'last_name'       => (string) $row['last_name'],
				'name'            => trim( $row['first_name'] . ' ' . $row['last_name'] ),
				'email'           => (string) $row['email'],
				'type'            => (string) $row['donor_type'],
				'monthly_amount'  => null !== $row['monthly_amount'] ? (float) $row['monthly_amount'] : null,
				'gifts'           => (int) $row['gifts'],
				'total'           => (float) $row['total'],
				'latest'          => (string) $row['latest'],
				'all_gifts'       => (int) $row['all_gifts'],
				'all_total'       => (float) $row['all_total'],
				'donor_since'     => (string) $row['donor_since'],
				'comment'         => $comment,
				'comment_id'      => $comment_id,
				'comment_hidden'  => $hidden,
				'comment_pending' => $pending,
				'anonymous'       => $anonymous,
				'hide_name'       => ! empty( $extra['donor']['hide_name'] ),
				'campaigns'       => array_values( $campaigns ),
			);
		},
		$rows
	);
}

/**
 * Donor fields saved on a gift (payments.extra.donation), normalised.
 *
 * @param array|string|null $extra Payment extra (array or JSON).
 * @return array{anonymous: bool, comment: string, frequency: string, tribute: array, hidden: bool, approved: bool}
 */
function smartpay_get_payment_donation( $extra ): array {
	$extra    = is_array( $extra ) ? $extra : json_decode( (string) $extra, true );
	$donation = is_array( $extra ) && is_array( $extra['donation'] ?? null ) ? $extra['donation'] : array();
	$tribute  = is_array( $donation['tribute'] ?? null ) ? $donation['tribute'] : array();

	return array(
		'anonymous' => ! empty( $donation['anonymous'] ),
		'comment'   => (string) ( $donation['comment'] ?? '' ),
		'frequency' => (string) ( $donation['frequency'] ?? '' ),
		'tribute'   => array(
			'type' => (string) ( $tribute['type'] ?? '' ),
			'name' => (string) ( $tribute['name'] ?? '' ),
		),
		'hidden'    => ! empty( $donation['hidden'] ),
		// Comments are approved unless moderation (Pro) held them.
		'approved'  => ! array_key_exists( 'approved', $donation ) || ! empty( $donation['approved'] ),
	);
}

/**
 * Public wall name for a donor: full name.
 *
 * @param string $first First name.
 * @param string $last  Last name.
 * @return string
 */
function smartpay_donor_public_name( string $first, string $last ): string {
	return trim( trim( $first ) . ' ' . trim( $last ) );
}

/**
 * A donor's giving: every payment on a donation form (any status, newest
 * first, max 200) plus stats and per-campaign totals from completed gifts.
 *
 * @param int $customer_id Customer ID.
 * @return array{stats: array, history: array[], campaigns: array[], monthly: array[]}
 */
function smartpay_get_donor_giving( int $customer_id ): array {
	global $wpdb;

	// Every payment on a donation form (any status) for the history table;
	// only completed ones count in the stats.
	$form_ids = smartpay_get_donation_form_ids();
	$table    = $wpdb->prefix . 'smartpay_payments';
	$match    = smartpay_payments_form_match_sql( $form_ids, 'p.data' );
	$parent   = smartpay_payments_form_match_sql( $form_ids, 'sp_parent.data' );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fragments are prepared by the helpers.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.id, p.amount, p.status, p.created_at, p.parent_id, p.data, p.extra, p.currency FROM {$table} p
		WHERE p.customer_id = %d AND ( {$match} OR p.parent_id IN ( SELECT sp_parent.id FROM {$table} sp_parent WHERE {$parent} ) )
		ORDER BY p.created_at DESC, p.id DESC LIMIT 200",
			$customer_id
		),
		ARRAY_A
	);
	// phpcs:enable

	$parents   = array();
	$history   = array();
	$campaigns = array();
	$status    = array();
	$amounts   = array();

	foreach ( (array) $rows as $row ) {
		$data = json_decode( (string) $row['data'], true );
		if ( ! empty( $row['parent_id'] ) ) {
			if ( ! array_key_exists( $row['parent_id'], $parents ) ) {
				$parent_row                   = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM {$table} WHERE id = %d", $row['parent_id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$parents[ $row['parent_id'] ] = json_decode( (string) $parent_row, true );
			}
			$data = $parents[ $row['parent_id'] ];
		}

		$form_id     = (int) ( is_array( $data ) ? ( $data['form_id'] ?? 0 ) : 0 );
		$campaign_id = smartpay_get_form_campaign_id( $form_id );
		$campaign    = $campaign_id ? get_term( $campaign_id, SMARTPAY_CAMPAIGN_TAXONOMY ) : null;
		$donation    = smartpay_get_payment_donation( $row['extra'] );
		$completed   = \SmartPay\Models\Payment::COMPLETED === $row['status'];

		$status[ $row['status'] ] = ( $status[ $row['status'] ] ?? 0 ) + 1;

		if ( $completed ) {
			$amounts[] = (float) $row['amount'];
			if ( $campaign instanceof \WP_Term ) {
				$c                         = $campaigns[ $campaign_id ] ?? array(
					'id'     => $campaign_id,
					'title'  => $campaign->name,
					'total'  => 0.0,
					'gifts'  => 0,
					'latest' => '',
				);
				$c['total']               += (float) $row['amount'];
				$c['gifts']               += 1;
				$c['latest']               = max( $c['latest'], (string) $row['created_at'] );
				$campaigns[ $campaign_id ] = $c;
			}
		}

		$history[] = array(
			'id'         => (int) $row['id'],
			'amount'     => (float) $row['amount'],
			'status'     => (string) $row['status'],
			'created_at' => (string) $row['created_at'],
			'form'       => $form_id ? get_the_title( $form_id ) : '',
			'campaign'   => $campaign instanceof \WP_Term ? array(
				'id'    => $campaign_id,
				'title' => $campaign->name,
			) : null,
			'frequency'  => ( ! empty( $row['parent_id'] ) || \SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === ( $data['billing_type'] ?? '' ) ) ? 'monthly' : 'one_time',
			'anonymous'  => $donation['anonymous'],
			'comment'    => $donation['comment'],
		);
	}

	return array(
		'stats'     => array(
			'lifetime' => array_sum( $amounts ),
			'gifts'    => count( $amounts ),
			'average'  => $amounts ? array_sum( $amounts ) / count( $amounts ) : 0,
			'largest'  => $amounts ? max( $amounts ) : 0,
			'status'   => $status,
		),
		'history'   => $history,
		'campaigns' => array_values( $campaigns ),
		'monthly'   => smartpay_get_donor_monthly_gifts( $customer_id ),
	);
}

/**
 * A donor's recurring gifts (Pro subscriptions whose first payment was a gift).
 *
 * @param int $customer_id Customer ID.
 * @return array[] id, status, amount, period, created_at, campaign.
 */
function smartpay_get_donor_monthly_gifts( int $customer_id ): array {
	global $wpdb;

	$subs = smartpay_donor_subscriptions_table();
	if ( ! $subs || $customer_id <= 0 ) {
		return array();
	}

	$table = $wpdb->prefix . 'smartpay_payments';
	$match = smartpay_payments_form_match_sql( smartpay_get_donation_form_ids(), 'p.data' );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $match is prepared; table names are ours.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT s.id, s.status, s.recurring_amount, s.period, s.created_at, p.data FROM {$subs} s
			JOIN {$table} p ON p.id = s.parent_payment_id
			WHERE p.customer_id = %d AND {$match} ORDER BY s.id DESC",
			$customer_id
		),
		ARRAY_A
	);
	// phpcs:enable

	return array_map(
		static function ( $row ) {
			$data        = json_decode( (string) $row['data'], true );
			$campaign_id = smartpay_get_form_campaign_id( (int) ( $data['form_id'] ?? 0 ) );
			$term        = $campaign_id ? get_term( $campaign_id, SMARTPAY_CAMPAIGN_TAXONOMY ) : null;

			return array(
				'id'         => (int) $row['id'],
				'status'     => (string) $row['status'],
				'amount'     => (float) $row['recurring_amount'],
				'period'     => (string) $row['period'],
				'created_at' => (string) $row['created_at'],
				'campaign'   => $term instanceof \WP_Term ? $term->name : '',
			);
		},
		(array) $rows
	);
}

/**
 * SQL condition that hides donors from the Customers list.
 *
 * Hidden: people on the Donors list (a completed gift on a donation form)
 * who never made a sale. Anyone who also bought something stays a customer,
 * so they show on both lists. Nobody can drop off both lists: a person who is
 * not on the Donors list always stays here. Returns '' until the
 * `is_donation` column exists.
 *
 * @param string $column Customer id column in the outer query.
 * @return string SQL fragment, or '' for no condition.
 */
function smartpay_customers_exclude_donors_sql( string $column = 'id' ): string {
	global $wpdb;

	$form_ids = get_option( 'smartpay_payments_is_donation_column' ) ? smartpay_get_donation_form_ids() : array();
	if ( empty( $form_ids ) ) {
		return '';
	}

	$column   = preg_replace( '/[^a-z_.]/', '', $column );
	$payments = $wpdb->prefix . 'smartpay_payments';
	$gift     = smartpay_gift_payments_where_sql( $form_ids, 'p' );

	// IS NOT NULL: one NULL in a NOT IN list would match nothing.
	return "{$column} NOT IN ( SELECT p.customer_id FROM {$payments} p WHERE p.customer_id IS NOT NULL AND {$gift}
		AND p.customer_id NOT IN ( SELECT s.customer_id FROM {$payments} s WHERE s.customer_id IS NOT NULL AND s.is_donation = 0 ) )";
}

/**
 * Contact roles for the Contacts › All list, matching the Customers and
 * Donors tabs: a donor has a completed gift on a donation form; a customer
 * is anyone the Customers tab lists (see smartpay_customers_exclude_donors_sql()).
 *
 * @param int[] $customer_ids Customer IDs on the current page.
 * @return array<int, array{customer: bool, donor: bool}>
 */
function smartpay_get_contact_roles( array $customer_ids ): array {
	global $wpdb;

	$ids = array_values( array_filter( array_map( 'absint', $customer_ids ) ) );
	if ( ! $ids ) {
		return array();
	}

	$payments = $wpdb->prefix . 'smartpay_payments';
	$in       = implode( ',', $ids );
	$form_ids = smartpay_get_donation_form_ids();
	$donors   = array();
	$buyers   = array();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in is absint-joined, $gift is prepared.
	if ( $form_ids ) {
		$gift   = smartpay_gift_payments_where_sql( $form_ids, 'p' );
		$donors = array_map( 'intval', $wpdb->get_col( "SELECT DISTINCT p.customer_id FROM {$payments} p WHERE p.customer_id IN ({$in}) AND {$gift}" ) );
		if ( get_option( 'smartpay_payments_is_donation_column' ) ) {
			$buyers = array_map( 'intval', $wpdb->get_col( "SELECT DISTINCT customer_id FROM {$payments} WHERE customer_id IN ({$in}) AND is_donation = 0" ) );
		}
	}
	// phpcs:enable

	$no_split = ! get_option( 'smartpay_payments_is_donation_column' );
	$roles    = array();
	foreach ( $ids as $id ) {
		$donor        = in_array( $id, $donors, true );
		$roles[ $id ] = array(
			'customer' => $no_split || ! $donor || in_array( $id, $buyers, true ),
			'donor'    => $donor,
		);
	}

	return $roles;
}
