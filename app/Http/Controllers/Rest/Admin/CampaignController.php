<?php
/**
 * Campaign REST controller.
 *
 * @package SmartPay
 */

namespace SmartPay\Http\Controllers\Rest\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Admin REST API for campaigns (smartpay/v1/campaigns…).
 *
 * @package SmartPay\Http\Controllers\Rest\Admin
 */
class CampaignController extends RestController {

	/**
	 * Admins only.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|\WP_Error
	 */
	public function middleware( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the resource.', 'smartpay' ),
				array( 'status' => is_user_logged_in() ? 403 : 401 )
			);
		}

		return true;
	}

	/**
	 * GET campaigns — search, status filter, pagination.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$page     = max( 1, absint( $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1 ) );
		$per_page = min( 100, max( 1, absint( $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 20 ) ) );
		$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$status   = sanitize_key( (string) $request->get_param( 'status' ) );

		$terms = get_terms(
			array(
				'taxonomy'   => SMARTPAY_CAMPAIGN_TAXONOMY,
				'hide_empty' => false,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'search'     => $search,
			)
		);
		$terms = is_wp_error( $terms ) ? array() : $terms;

		// ponytail: status is computed from the end date, so it is filtered and
		// paginated in PHP; fine for hundreds of campaigns.
		$campaigns = array_values(
			array_filter(
				array_map( 'smartpay_get_campaign', $terms ),
				static function ( $campaign ) use ( $status ) {
					return $campaign && ( ! in_array( $status, array( 'active', 'ended' ), true ) || $campaign['status'] === $status );
				}
			)
		);

		$total  = count( $campaigns );
		$offset = ( $page - 1 ) * $per_page;
		$rows   = array_map( array( $this, 'present' ), array_slice( $campaigns, $offset, $per_page ) );

		return new WP_REST_Response(
			array(
				'campaigns' => array(
					'data'         => $rows,
					'current_page' => $page,
					'per_page'     => $per_page,
					'last_page'    => max( 1, (int) ceil( $total / $per_page ) ),
					'total'        => $total,
					'from'         => $total > 0 ? $offset + 1 : 0,
					'to'           => min( $offset + $per_page, $total ),
				),
			)
		);
	}

	/**
	 * POST campaigns — create.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function store( WP_REST_Request $request ): WP_REST_Response {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );

		if ( '' === $title ) {
			return new WP_REST_Response( array( 'message' => __( 'Campaign title is required.', 'smartpay' ) ), 422 );
		}

		$term = wp_insert_term(
			$title,
			SMARTPAY_CAMPAIGN_TAXONOMY,
			array( 'description' => sanitize_textarea_field( (string) $request->get_param( 'description' ) ) )
		);

		if ( is_wp_error( $term ) ) {
			return new WP_REST_Response( array( 'message' => $term->get_error_message() ), 422 );
		}

		$this->save_meta( (int) $term['term_id'], $request );

		return new WP_REST_Response(
			array(
				'campaign' => $this->present( smartpay_get_campaign( (int) $term['term_id'] ) ),
				'message'  => __( 'Campaign created', 'smartpay' ),
			),
			201
		);
	}

	/**
	 * GET campaigns/{id}.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function show( WP_REST_Request $request ): WP_REST_Response {
		$campaign = smartpay_get_campaign( absint( $request->get_param( 'id' ) ) );

		if ( ! $campaign ) {
			return $this->not_found();
		}

		return new WP_REST_Response( array( 'campaign' => $this->present( $campaign ) ) );
	}

	/**
	 * PUT campaigns/{id} — update details, goal and slug.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		$id       = absint( $request->get_param( 'id' ) );
		$campaign = smartpay_get_campaign( $id );

		if ( ! $campaign ) {
			return $this->not_found();
		}

		$args = array();
		if ( null !== $request->get_param( 'title' ) ) {
			$args['name'] = sanitize_text_field( (string) $request->get_param( 'title' ) );
			if ( '' === $args['name'] ) {
				return new WP_REST_Response( array( 'message' => __( 'Campaign title is required.', 'smartpay' ) ), 422 );
			}
		}
		if ( null !== $request->get_param( 'description' ) ) {
			$args['description'] = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		}
		if ( null !== $request->get_param( 'slug' ) ) {
			$args['slug'] = sanitize_title( (string) $request->get_param( 'slug' ) );
		}

		if ( $args ) {
			$result = wp_update_term( $id, SMARTPAY_CAMPAIGN_TAXONOMY, $args );
			if ( is_wp_error( $result ) ) {
				return new WP_REST_Response( array( 'message' => $result->get_error_message() ), 422 );
			}
		}

		$this->save_meta( $id, $request );
		smartpay_invalidate_campaign_cache( $id );

		/**
		 * Fires after a campaign is saved from the admin, so add-ons can save their own fields.
		 *
		 * @param int             $id      Campaign term ID.
		 * @param WP_REST_Request $request Request.
		 */
		do_action( 'smartpay_campaign_updated', $id, $request );

		return new WP_REST_Response(
			array(
				'campaign' => $this->present( smartpay_get_campaign( $id ) ),
				'message'  => __( 'Campaign updated', 'smartpay' ),
			)
		);
	}

	/**
	 * DELETE campaigns/{id}. Forms are never deleted; they become unassigned.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function destroy( WP_REST_Request $request ): WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );

		if ( ! smartpay_get_campaign( $id ) ) {
			return $this->not_found();
		}

		$form_ids = smartpay_get_campaign_form_ids( $id );
		wp_delete_term( $id, SMARTPAY_CAMPAIGN_TAXONOMY );
		smartpay_invalidate_campaign_cache( $id );

		foreach ( $form_ids as $form_id ) {
			smartpay_invalidate_goal_cache( $form_id );
		}

		return new WP_REST_Response( array( 'message' => __( 'Campaign deleted', 'smartpay' ) ) );
	}

	/**
	 * GET campaigns/{id}/overview?range=today|7d|30d|all.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function overview( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		$campaign = smartpay_get_campaign( absint( $request->get_param( 'id' ) ) );
		if ( ! $campaign ) {
			return $this->not_found();
		}

		$range  = in_array( $request->get_param( 'range' ), array( 'today', '7d', '30d', 'all' ), true ) ? $request->get_param( 'range' ) : 'all';
		$days   = array(
			'today' => 1,
			'7d'    => 7,
			'30d'   => 30,
		);
		$since  = isset( $days[ $range ] ) ? gmdate( 'Y-m-d 00:00:00', strtotime( '-' . ( $days[ $range ] - 1 ) . ' days' ) ) : '';
		$table  = $wpdb->prefix . 'smartpay_payments';
		$cust   = $wpdb->prefix . 'smartpay_customers';
		$where  = smartpay_gift_payments_where_sql( $campaign['form_ids'], 'p' );
		$in_rng = $since ? $wpdb->prepare( ' AND p.created_at >= %s', $since ) : '';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fragments are prepared above.
		$stats = $wpdb->get_row( "SELECT COUNT(*) AS donations, COALESCE(SUM(p.amount),0) AS raised, COUNT(DISTINCT p.customer_id) AS donors FROM {$table} p WHERE {$where}{$in_rng}", ARRAY_A );

		// Revenue trend: per day for a range, per month for all-time.
		$bucket = 'all' === $range ? '%Y-%m' : '%Y-%m-%d';
		$series = $wpdb->get_results(
			$wpdb->prepare( "SELECT DATE_FORMAT(p.created_at, %s) AS bucket, COALESCE(SUM(p.amount),0) AS amount FROM {$table} p WHERE {$where}{$in_rng} GROUP BY bucket ORDER BY bucket ASC", $bucket ),
			ARRAY_A
		);

		$recent = $wpdb->get_results(
			"SELECT p.id, p.amount, p.created_at, p.parent_id, p.data, p.extra, c.id AS customer_id, c.first_name, c.last_name, c.email
			FROM {$table} p LEFT JOIN {$cust} c ON c.id = p.customer_id WHERE {$where} ORDER BY p.created_at DESC, p.id DESC LIMIT 5",
			ARRAY_A
		);

		$top = $wpdb->get_results(
			"SELECT c.id, c.first_name, c.last_name, c.email, SUM(p.amount) AS total, COUNT(*) AS gifts
			FROM {$table} p JOIN {$cust} c ON c.id = p.customer_id WHERE {$where} GROUP BY c.id ORDER BY total DESC LIMIT 5",
			ARRAY_A
		);
		// phpcs:enable

		$donor_counts = smartpay_query_donors(
			array(
				'form_ids' => $campaign['form_ids'],
				'per_page' => 1,
			)
		)['counts'];

		return new WP_REST_Response(
			array(
				'campaign' => $this->present( $campaign ),
				'range'    => $range,
				'stats'    => array(
					'raised'    => (float) ( $stats['raised'] ?? 0 ),
					'donations' => (int) ( $stats['donations'] ?? 0 ),
					'donors'    => (int) ( $stats['donors'] ?? 0 ),
					'repeat'    => (int) $donor_counts['repeat'],
					'monthly'   => (int) $donor_counts['monthly'],
				),
				'series'   => array_map(
					static function ( $row ) {
						return array(
							'date'   => $row['bucket'],
							'amount' => (float) $row['amount'],
						);
					},
					(array) $series
				),
				'recent'   => array_map( array( $this, 'present_gift' ), (array) $recent ),
				'top'      => array_map(
					static function ( $row ) {
						return array(
							'id'    => (int) $row['id'],
							'name'  => trim( $row['first_name'] . ' ' . $row['last_name'] ),
							'email' => (string) $row['email'],
							'total' => (float) $row['total'],
							'gifts' => (int) $row['gifts'],
						);
					},
					(array) $top
				),
			)
		);
	}

	/**
	 * GET campaigns/{id}/forms — attached forms with their share, plus legacy
	 * forms that must be migrated before they can join.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function forms( WP_REST_Request $request ): WP_REST_Response {
		$campaign = smartpay_get_campaign( absint( $request->get_param( 'id' ) ) );
		if ( ! $campaign ) {
			return $this->not_found();
		}

		$forms = array();
		foreach ( $campaign['form_ids'] as $form_id ) {
			$post = get_post( $form_id );
			if ( ! $post ) {
				continue;
			}
			$stats   = smartpay_get_forms_gift_stats( array( $form_id ) );
			$forms[] = array(
				'id'          => $form_id,
				'title'       => '' !== $post->post_title ? $post->post_title : __( '(Untitled)', 'smartpay' ),
				'status'      => $post->post_status,
				'is_default'  => $form_id === $campaign['default_form_id'],
				'raised'      => $stats['raised'],
				'donations'   => $stats['donations'],
				'shortcode'   => '[sp_form id="' . $form_id . '"]',
				'edit_url'    => admin_url( 'post.php?post=' . $form_id . '&action=edit' ),
				'preview_url' => get_permalink( $form_id ),
			);
		}

		return new WP_REST_Response(
			array(
				'forms'        => $forms,
				'legacy_forms' => $this->unmigrated_legacy_forms(),
			)
		);
	}

	/**
	 * POST campaigns/{id}/forms { form_ids: [] } — attach (moves from any other campaign).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function attach_forms( WP_REST_Request $request ): WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );
		if ( ! smartpay_get_campaign( $id ) ) {
			return $this->not_found();
		}

		$attached = $this->set_forms_campaign( (array) $request->get_param( 'form_ids' ), $id );

		return new WP_REST_Response(
			array(
				'attached' => $attached,
				/* translators: %d: number of forms */
				'message'  => sprintf( _n( '%d form attached', '%d forms attached', count( $attached ), 'smartpay' ), count( $attached ) ),
			)
		);
	}

	/**
	 * DELETE campaigns/{id}/forms/{form_id} — detach; the form is kept.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function detach_form( WP_REST_Request $request ): WP_REST_Response {
		$id      = absint( $request->get_param( 'id' ) );
		$form_id = absint( $request->get_param( 'form_id' ) );

		if ( ! has_term( $id, SMARTPAY_CAMPAIGN_TAXONOMY, $form_id ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Form is not in this campaign.', 'smartpay' ) ), 404 );
		}

		wp_remove_object_terms( $form_id, $id, SMARTPAY_CAMPAIGN_TAXONOMY );

		return new WP_REST_Response( array( 'message' => __( 'Form detached', 'smartpay' ) ) );
	}

	/**
	 * POST campaigns/{id}/default-form { form_id }.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function set_default_form( WP_REST_Request $request ): WP_REST_Response {
		$id      = absint( $request->get_param( 'id' ) );
		$form_id = absint( $request->get_param( 'form_id' ) );

		if ( ! has_term( $id, SMARTPAY_CAMPAIGN_TAXONOMY, $form_id ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Form is not in this campaign.', 'smartpay' ) ), 422 );
		}

		update_term_meta( $id, 'smartpay_campaign_default_form', $form_id );

		return new WP_REST_Response( array( 'message' => __( 'Default form updated', 'smartpay' ) ) );
	}

	/**
	 * GET campaigns/{id}/donors — the private donor list for one campaign.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function donors( WP_REST_Request $request ): WP_REST_Response {
		$campaign = smartpay_get_campaign( absint( $request->get_param( 'id' ) ) );
		if ( ! $campaign ) {
			return $this->not_found();
		}

		$donors = smartpay_query_donors(
			array(
				'form_ids' => $campaign['form_ids'],
				'type'     => sanitize_key( (string) $request->get_param( 'type' ) ),
				'flag'     => sanitize_key( (string) $request->get_param( 'flag' ) ),
				'search'   => sanitize_text_field( (string) $request->get_param( 'search' ) ),
				'page'     => absint( $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1 ),
				'per_page' => absint( $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 20 ),
			)
		);

		/**
		 * Filter the campaign donors response (Pro adds wall visibility per row).
		 *
		 * @param array $donors   smartpay_query_donors() result.
		 * @param array $campaign Normalised campaign.
		 */
		return new WP_REST_Response( array( 'donors' => apply_filters( 'smartpay_campaign_donors_response', $donors, $campaign ) ) );
	}

	/**
	 * POST native-forms/campaign { ids: [], campaign_id } — bulk assign (0 removes).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function assign_forms( WP_REST_Request $request ): WP_REST_Response {
		$campaign_id = absint( $request->get_param( 'campaign_id' ) );

		if ( $campaign_id && ! smartpay_get_campaign( $campaign_id ) ) {
			return $this->not_found();
		}

		$done = $this->set_forms_campaign( (array) $request->get_param( 'ids' ), $campaign_id );

		return new WP_REST_Response(
			array(
				'updated' => $done,
				'message' => $campaign_id
					/* translators: %d: number of forms */
					? sprintf( _n( '%d form assigned', '%d forms assigned', count( $done ), 'smartpay' ), count( $done ) )
					/* translators: %d: number of forms */
					: sprintf( _n( '%d form removed from its campaign', '%d forms removed from their campaigns', count( $done ), 'smartpay' ), count( $done ) ),
			)
		);
	}

	/**
	 * Attach forms to a campaign (0 = remove from any campaign).
	 *
	 * @param array $form_ids    Raw form IDs.
	 * @param int   $campaign_id Campaign term ID or 0.
	 * @return int[] IDs actually changed.
	 */
	private function set_forms_campaign( array $form_ids, int $campaign_id ): array {
		$done = array();

		foreach ( array_unique( array_map( 'absint', $form_ids ) ) as $form_id ) {
			$post = $form_id ? get_post( $form_id ) : null;
			// Only new-builder forms can join; legacy forms must be migrated first.
			if ( ! $post || 'smartpay_form' !== $post->post_type ) {
				continue;
			}

			wp_set_object_terms( $form_id, $campaign_id ? array( $campaign_id ) : array(), SMARTPAY_CAMPAIGN_TAXONOMY );
			$done[] = $form_id;
		}

		if ( $campaign_id && $done && ! get_term_meta( $campaign_id, 'smartpay_campaign_default_form', true ) ) {
			update_term_meta( $campaign_id, 'smartpay_campaign_default_form', $done[0] );
		}

		return $done;
	}

	/**
	 * Save campaign meta from a create/update request (only fields present).
	 *
	 * @param int             $id      Campaign term ID.
	 * @param WP_REST_Request $request Request.
	 */
	private function save_meta( int $id, WP_REST_Request $request ): void {
		if ( null !== $request->get_param( 'story' ) ) {
			update_term_meta( $id, 'smartpay_campaign_story', wp_kses_post( (string) $request->get_param( 'story' ) ) );
		}

		if ( null !== $request->get_param( 'cover_id' ) ) {
			update_term_meta( $id, 'smartpay_campaign_cover_id', absint( $request->get_param( 'cover_id' ) ) );
		}

		if ( null !== $request->get_param( 'goal_type' ) ) {
			$type = sanitize_key( (string) $request->get_param( 'goal_type' ) );
			update_term_meta( $id, 'smartpay_campaign_goal_type', array_key_exists( $type, smartpay_campaign_goal_types() ) ? $type : 'amount' );
		}

		if ( null !== $request->get_param( 'goal_target' ) ) {
			update_term_meta( $id, 'smartpay_campaign_goal_target', max( 0, (float) $request->get_param( 'goal_target' ) ) );
		}

		if ( null !== $request->get_param( 'end_date' ) ) {
			$date = sanitize_text_field( (string) $request->get_param( 'end_date' ) );
			update_term_meta( $id, 'smartpay_campaign_end_date', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '' );
		}

		if ( null !== $request->get_param( 'goal_behavior' ) ) {
			update_term_meta( $id, 'smartpay_campaign_goal_behavior', 'stop' === $request->get_param( 'goal_behavior' ) ? 'stop' : 'keep' );
		}
	}

	/**
	 * Campaign as returned to the admin, with totals.
	 *
	 * @param array|null $campaign Normalised campaign.
	 * @return array|null
	 */
	private function present( ?array $campaign ): ?array {
		if ( ! $campaign ) {
			return null;
		}

		$campaign['forms_count']        = count( $campaign['form_ids'] );
		$campaign['default_form_title'] = $campaign['default_form_id'] ? get_the_title( $campaign['default_form_id'] ) : '';
		$campaign['progress']           = smartpay_calculate_campaign_progress( $campaign['id'] );

		/**
		 * Filter a campaign's admin REST representation (Pro adds donor list visibility).
		 *
		 * @param array $campaign Campaign data.
		 */
		return apply_filters( 'smartpay_campaign_rest_data', $campaign );
	}

	/**
	 * A gift row for the admin (recent donations lists).
	 *
	 * @param array $row Payment row joined with its customer.
	 * @return array
	 */
	public function present_gift( array $row ): array {
		$data     = json_decode( (string) $row['data'], true );
		$donation = smartpay_get_payment_donation( $row['extra'] );
		$monthly  = ! empty( $row['parent_id'] ) || ( is_array( $data ) && Payment::BILLING_TYPE_SUBSCRIPTION === ( $data['billing_type'] ?? '' ) );

		return array(
			'id'         => (int) $row['id'],
			'amount'     => (float) $row['amount'],
			'created_at' => (string) $row['created_at'],
			'monthly'    => $monthly,
			'anonymous'  => $donation['anonymous'],
			'comment'    => $donation['comment'],
			'customer'   => array(
				'id'    => (int) $row['customer_id'],
				'name'  => trim( $row['first_name'] . ' ' . $row['last_name'] ),
				'email' => (string) $row['email'],
			),
		);
	}

	/**
	 * Legacy (table) forms not yet migrated to the form builder.
	 *
	 * @return array[]
	 */
	private function unmigrated_legacy_forms(): array {
		if ( ! class_exists( '\\SmartPay\\Models\\Form' ) ) {
			return array();
		}

		$migrated     = array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => 'smartpay_form',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin-only, small set.
					'meta_key'       => '_smartpay_migrated_from_legacy_id',
				)
			)
		);
		$migrated_ids = array_map(
			static function ( $post_id ) {
				return (int) get_post_meta( $post_id, '_smartpay_migrated_from_legacy_id', true );
			},
			$migrated
		);

		try {
			$legacy = \SmartPay\Models\Form::all();
		} catch ( \Throwable $e ) {
			return array();
		}

		$out = array();
		foreach ( $legacy as $form ) {
			if ( in_array( (int) $form->id, $migrated_ids, true ) ) {
				continue;
			}
			$out[] = array(
				'id'    => (int) $form->id,
				'title' => (string) $form->title,
			);
		}

		return $out;
	}

	/**
	 * 404 response for an unknown campaign.
	 *
	 * @return WP_REST_Response
	 */
	private function not_found(): WP_REST_Response {
		return new WP_REST_Response( array( 'message' => __( 'Campaign not found', 'smartpay' ) ), 404 );
	}
}
