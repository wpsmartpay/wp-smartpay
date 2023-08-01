<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use WP_REST_Request;
use WP_REST_Response;

class InvoiceController extends RestController {

	public function middleware( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), [
				'status' => is_user_logged_in() ? 403 : 401,
			] );
		}

		return true;
	}


	public function index(): \WP_REST_Response {
		return new \WP_REST_Response( [
			'invoices' => [
			]
		] );
	}

	public function store( WP_REST_Request $request ): WP_REST_Response {
		$request = json_decode( $request->get_body() );

		return new WP_REST_Response( [ 'invoice' => [], 'message' => __( 'Invoice created', 'smartpay' ) ] );
	}

	public function preview( WP_REST_Request $request ) {
		// preview the pdf or html
	}

	public function generatePdf( WP_REST_Request $request ) {
		// generate pdf
	}

	public function destroy( WP_REST_Request $request ) {
		// delete invoice with invoice items
	}

}