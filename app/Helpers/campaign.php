<?php
/**
 * Campaign helpers.
 *
 * A campaign is a term of the `smartpay_campaign` taxonomy on the
 * `smartpay_form` post type. It groups forms under one goal: money raised by
 * any attached form moves the same bar. Nothing on the payments table changes;
 * a payment belongs to a campaign through the form id stored in its `data` JSON.
 *
 * @package SmartPay
 */

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Payment as PaymentModel;

if ( ! defined( 'SMARTPAY_CAMPAIGN_TAXONOMY' ) ) {
	define( 'SMARTPAY_CAMPAIGN_TAXONOMY', 'smartpay_campaign' );
}

/**
 * Campaign goal types and the per-form goal type each one maps to.
 *
 * Per-form goals only know `amount` and `quantity`; `donors` is new and is a
 * by-product of the same query (a distinct count of customers).
 *
 * @return array<string,string> campaign goal type => form goal type
 */
function smartpay_campaign_goal_types(): array {
	return array(
		'amount'    => 'amount',
		'donations' => 'quantity',
		'donors'    => 'donors',
	);
}

/**
 * Campaign id a form is attached to, or 0 when it has none.
 *
 * @param int $form_id Form post ID.
 * @return int
 */
function smartpay_get_form_campaign_id( int $form_id ): int {
	if ( $form_id <= 0 || ! taxonomy_exists( SMARTPAY_CAMPAIGN_TAXONOMY ) ) {
		return 0;
	}

	$terms = get_the_terms( $form_id, SMARTPAY_CAMPAIGN_TAXONOMY );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return 0;
	}

	return (int) $terms[0]->term_id;
}

/**
 * Form ids attached to a campaign (all statuses except trash).
 *
 * @param int $campaign_id Campaign term ID.
 * @return int[]
 */
function smartpay_get_campaign_form_ids( int $campaign_id ): array {
	if ( $campaign_id <= 0 ) {
		return array();
	}

	$ids = get_posts(
		array(
			'post_type'      => 'smartpay_form',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Campaign membership is a taxonomy by design.
			'tax_query'      => array(
				array(
					'taxonomy' => SMARTPAY_CAMPAIGN_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $campaign_id,
				),
			),
		)
	);

	return array_map( 'intval', $ids );
}

/**
 * Normalised campaign record.
 *
 * @param int|\WP_Term $campaign Campaign term or term ID.
 * @return array|null Null when the term does not exist.
 */
function smartpay_get_campaign( $campaign ): ?array {
	$term = $campaign instanceof \WP_Term ? $campaign : get_term( (int) $campaign, SMARTPAY_CAMPAIGN_TAXONOMY );

	if ( ! $term instanceof \WP_Term || SMARTPAY_CAMPAIGN_TAXONOMY !== $term->taxonomy ) {
		return null;
	}

	$id        = (int) $term->term_id;
	$goal_type = (string) get_term_meta( $id, 'smartpay_campaign_goal_type', true );
	$goal_type = array_key_exists( $goal_type, smartpay_campaign_goal_types() ) ? $goal_type : 'amount';
	$end_date  = (string) get_term_meta( $id, 'smartpay_campaign_end_date', true );
	$behavior  = 'stop' === get_term_meta( $id, 'smartpay_campaign_goal_behavior', true ) ? 'stop' : 'keep';
	$cover_id  = (int) get_term_meta( $id, 'smartpay_campaign_cover_id', true );
	$form_ids  = smartpay_get_campaign_form_ids( $id );

	// The default form must still be in the campaign — a detached or deleted
	// form falls back to the first attached one.
	$default_form = (int) get_term_meta( $id, 'smartpay_campaign_default_form', true );
	if ( ! in_array( $default_form, $form_ids, true ) ) {
		$default_form = $form_ids[0] ?? 0;
	}

	$link = get_term_link( $term );

	return array(
		'id'              => $id,
		'title'           => $term->name,
		'slug'            => $term->slug,
		'description'     => $term->description,
		'story'           => (string) get_term_meta( $id, 'smartpay_campaign_story', true ),
		'cover_id'        => $cover_id,
		'cover_url'       => $cover_id ? (string) wp_get_attachment_image_url( $cover_id, 'full' ) : '',
		'goal_type'       => $goal_type,
		'goal_target'     => (float) get_term_meta( $id, 'smartpay_campaign_goal_target', true ),
		'end_date'        => $end_date,
		'goal_behavior'   => $behavior,
		'default_form_id' => $default_form,
		'form_ids'        => $form_ids,
		'status'          => ( $end_date && gmdate( 'Y-m-d' ) > $end_date ) ? 'ended' : 'active',
		'url'             => is_wp_error( $link ) ? '' : $link,
	);
}

/**
 * SQL condition matching payments made on any of the given forms.
 *
 * The form id lives inside the `data` JSON, so this is a LIKE scan. Each id is
 * matched with its closing delimiter so form 1 never matches form 12 or 100.
 * Both int (`"form_id":12,`) and string (`"form_id":"12"`) encodings are
 * matched because older payments stored the id as a string.
 *
 * ponytail: LIKE scan per form id; add a real form_id column on payments the
 * day campaign totals get slow.
 *
 * @param int[]  $form_ids Form post IDs.
 * @param string $column   Column holding the JSON (e.g. `data` or `p.data`).
 * @return string Prepared SQL fragment; `1=0` when no ids are given.
 */
function smartpay_payments_form_match_sql( array $form_ids, string $column = 'data' ): string {
	global $wpdb;

	$form_ids = array_values( array_unique( array_filter( array_map( 'absint', $form_ids ) ) ) );
	$column   = preg_replace( '/[^a-z_.]/', '', $column );

	if ( empty( $form_ids ) ) {
		return '1=0';
	}

	$parts = array();
	foreach ( $form_ids as $id ) {
		$key     = $wpdb->esc_like( '"form_id":' . $id );
		$parts[] = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $column is whitelisted above.
			"({$column} LIKE %s OR {$column} LIKE %s OR {$column} LIKE %s)",
			'%' . $key . ',%',
			'%' . $key . '}%',
			'%' . $wpdb->esc_like( '"form_id":"' . $id . '"' ) . '%'
		);
	}

	return '(' . implode( ' OR ', $parts ) . ')';
}

/**
 * SQL WHERE matching completed gifts on the given forms, including renewals.
 *
 * Subscription renewals are child payments (`parent_id`) created without
 * `data`, so they carry no form id. They are matched through their parent.
 *
 * @param int[]  $form_ids Form post IDs.
 * @param string $alias    Table alias of the payments table in the outer query ('' for none).
 * @return string Prepared SQL fragment.
 */
function smartpay_gift_payments_where_sql( array $form_ids, string $alias = '' ): string {
	global $wpdb;

	$table  = $wpdb->prefix . 'smartpay_payments';
	$prefix = $alias ? preg_replace( '/[^a-z_]/', '', $alias ) . '.' : '';
	$outer  = smartpay_payments_form_match_sql( $form_ids, $prefix . 'data' );
	$inner  = smartpay_payments_form_match_sql( $form_ids, 'sp_parent.data' );

	return $wpdb->prepare( "{$prefix}status = %s", PaymentModel::COMPLETED ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		. " AND ( {$outer} OR {$prefix}parent_id IN ( SELECT sp_parent.id FROM {$table} sp_parent WHERE {$inner} ) )";
}

/**
 * Raw gift totals for a set of forms: count, amount and distinct donors.
 *
 * @param int[] $form_ids Form post IDs.
 * @return array{donations:int, raised:float, donors:int}
 */
function smartpay_get_forms_gift_stats( array $form_ids ): array {
	global $wpdb;

	$empty = array(
		'donations' => 0,
		'raised'    => 0.0,
		'donors'    => 0,
	);

	if ( empty( $form_ids ) ) {
		return $empty;
	}

	$table = $wpdb->prefix . 'smartpay_payments';
	$where = smartpay_gift_payments_where_sql( $form_ids );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is prepared; callers cache.
	$row = $wpdb->get_row( "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total, COUNT(DISTINCT customer_id) AS donors FROM {$table} WHERE {$where}", ARRAY_A );

	if ( ! $row ) {
		return $empty;
	}

	return array(
		'donations' => (int) $row['cnt'],
		'raised'    => (float) $row['total'],
		'donors'    => (int) $row['donors'],
	);
}

/**
 * Campaign totals and goal progress (cached for a minute).
 *
 * @param int $campaign_id Campaign term ID.
 * @return array{raised:float, donations:int, donors:int, current:float, target:float, percentage:float, type:string, goal_reached:bool}
 */
function smartpay_calculate_campaign_progress( int $campaign_id ): array {
	$campaign = smartpay_get_campaign( $campaign_id );

	$stats = get_transient( "smartpay_campaign_stats_{$campaign_id}" );
	if ( ! is_array( $stats ) ) {
		$stats = smartpay_get_forms_gift_stats( $campaign ? $campaign['form_ids'] : array() );
		set_transient( "smartpay_campaign_stats_{$campaign_id}", $stats, MINUTE_IN_SECONDS );
	}

	$type   = $campaign['goal_type'] ?? 'amount';
	$target = (float) ( $campaign['goal_target'] ?? 0 );

	if ( 'donations' === $type ) {
		$current = (float) $stats['donations'];
	} elseif ( 'donors' === $type ) {
		$current = (float) $stats['donors'];
	} else {
		$current = (float) $stats['raised'];
	}

	return array(
		'raised'       => (float) $stats['raised'],
		'donations'    => (int) $stats['donations'],
		'donors'       => (int) $stats['donors'],
		'current'      => $current,
		'target'       => $target,
		'percentage'   => $target > 0 ? round( min( 100, ( $current / $target ) * 100 ), 1 ) : 0,
		'type'         => $type,
		'goal_reached' => $target > 0 && $current >= $target,
	);
}

/**
 * Drop a campaign's cached totals.
 *
 * @param int $campaign_id Campaign term ID.
 */
function smartpay_invalidate_campaign_cache( int $campaign_id ): void {
	if ( $campaign_id > 0 ) {
		delete_transient( "smartpay_campaign_stats_{$campaign_id}" );
	}
}

/**
 * The goal that applies to a form.
 *
 * While a form is attached to a campaign, its own goal is paused and the
 * campaign's goal is used instead (in the same shape as a per-form goal so
 * existing readers keep working). Detaching restores the form's own goal.
 *
 * @param int $form_id Form post ID.
 * @return array Goal settings plus `campaign_id` (0 for a form's own goal).
 */
function smartpay_get_form_goal( int $form_id ): array {
	$campaign_id = smartpay_get_form_campaign_id( $form_id );
	$campaign    = $campaign_id ? smartpay_get_campaign( $campaign_id ) : null;

	if ( $campaign ) {
		return array(
			'enabled'             => $campaign['goal_target'] > 0,
			'type'                => smartpay_campaign_goal_types()[ $campaign['goal_type'] ],
			'target'              => $campaign['goal_target'],
			'showToPublic'        => true,
			'behaviorWhenGoalMet' => 'stop' === $campaign['goal_behavior'] ? 'stop_orders' : 'allow_orders',
			'stopCollectionDate'  => $campaign['end_date'],
			'goalMetMessage'      => __( 'This campaign has reached its goal. Thank you!', 'smartpay' ),
			'campaign_id'         => $campaign_id,
		);
	}

	$settings = get_post_meta( $form_id, '_smartpay_settings', true );
	$settings = is_string( $settings ) ? json_decode( $settings, true ) : $settings;
	$goal     = is_array( $settings ) ? ( $settings['goal'] ?? array() ) : array();
	$goal     = is_string( $goal ) ? json_decode( $goal, true ) : $goal;
	$goal     = is_array( $goal ) ? $goal : array();

	$goal['campaign_id'] = 0;

	return $goal;
}

/**
 * Whether a form is a donation form: in a campaign, or flagged by the admin.
 *
 * @param int $form_id Form post ID.
 * @return bool
 */
function smartpay_is_donation_form( int $form_id ): bool {
	return smartpay_get_form_campaign_id( $form_id ) > 0
		|| (bool) get_post_meta( $form_id, '_smartpay_is_donation_form', true );
}

/**
 * Ids of every donation form (attached to any campaign, or flagged).
 *
 * @return int[]
 */
function smartpay_get_donation_form_ids(): array {
	$ids = get_posts(
		array(
			'post_type'      => 'smartpay_form',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Small set, admin/reporting only.
			'tax_query'      => array(
				array(
					'taxonomy' => SMARTPAY_CAMPAIGN_TAXONOMY,
					'operator' => 'EXISTS',
				),
			),
		)
	);

	$flagged = get_posts(
		array(
			'post_type'      => 'smartpay_form',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_smartpay_is_donation_form', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small set, admin/reporting only.
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Small set, admin/reporting only.
		)
	);

	return array_values( array_unique( array_map( 'intval', array_merge( $ids, $flagged ) ) ) );
}

/**
 * Format a campaign goal value for display (money for `amount`, a count otherwise).
 *
 * @param float  $value Value.
 * @param string $type  Campaign goal type.
 * @return string
 */
function smartpay_campaign_format_value( float $value, string $type ): string {
	return 'amount' === $type ? smartpay_amount_format( $value ) : number_format_i18n( $value );
}
