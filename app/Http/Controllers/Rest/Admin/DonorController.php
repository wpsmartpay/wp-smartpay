<?php
/**
 * Donor REST controller.
 *
 * @package SmartPay
 */

namespace SmartPay\Http\Controllers\Rest\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Customer;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Admin REST API for donors (smartpay/v1/donors…). A donor is a customer with
 * a completed gift — see app/Helpers/donor.php. No table of its own.
 */
class DonorController extends RestController {

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
	 * GET donors — counters, list, filters (campaign, type, search, orderby).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$args = array(
			'type'     => sanitize_key( (string) $request->get_param( 'type' ) ),
			'search'   => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'orderby'  => sanitize_key( (string) $request->get_param( 'orderby' ) ),
			'page'     => max( 1, absint( $request->get_param( 'page' ) ) ),
			'per_page' => absint( $request->get_param( 'per_page' ) ) ? absint( $request->get_param( 'per_page' ) ) : 20,
		);

		$campaign_id = absint( $request->get_param( 'campaign' ) );
		if ( $campaign_id ) {
			$campaign         = smartpay_get_campaign( $campaign_id );
			$args['form_ids'] = $campaign ? $campaign['form_ids'] : array();
		}

		/**
		 * Filter the donors list response.
		 *
		 * @param array           $donors  smartpay_query_donors() result.
		 * @param WP_REST_Request $request Request.
		 */
		return new WP_REST_Response( array( 'donors' => apply_filters( 'smartpay_donors_response', smartpay_query_donors( $args ), $request ) ) );
	}

	/**
	 * GET donors/{id} — the customer record with its giving layer.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function show( WP_REST_Request $request ): WP_REST_Response {
		$id       = absint( $request->get_param( 'id' ) );
		$customer = Customer::find( $id );
		$donor    = smartpay_query_donors(
			array(
				'customer_ids' => array( $id ),
				'per_page'     => 1,
			)
		)['data'][0] ?? null;

		if ( ! $customer || ! $donor ) {
			return new WP_REST_Response( array( 'message' => __( 'Donor not found', 'smartpay' ) ), 404 );
		}

		$giving = smartpay_get_donor_giving( $id );

		$extra = is_array( $customer->extra ) ? $customer->extra : json_decode( (string) $customer->extra, true );

		/**
		 * Filter a donor's detail response (Pro adds private notes).
		 *
		 * @param array $response Response data.
		 * @param int   $id       Customer ID.
		 */
		$response = apply_filters(
			'smartpay_donor_response',
			array(
				'donor'     => $donor,
				'customer'  => array(
					'id'         => (int) $customer->id,
					'first_name' => (string) $customer->first_name,
					'last_name'  => (string) $customer->last_name,
					'email'      => (string) $customer->email,
					'user_id'    => (int) $customer->user_id,
					'created_at' => (string) $customer->created_at,
				),
				'stats'     => $giving['stats'],
				'history'   => $giving['history'],
				'campaigns' => $giving['campaigns'],
				'wall'      => array(
					'name'      => ! empty( $extra['donor']['hide_name'] )
						? __( 'Anonymous', 'smartpay' )
						: smartpay_donor_public_name( (string) $customer->first_name, (string) $customer->last_name ),
					'hide_name' => ! empty( $extra['donor']['hide_name'] ),
				),
			),
			$id
		);

		return new WP_REST_Response( $response );
	}
}
