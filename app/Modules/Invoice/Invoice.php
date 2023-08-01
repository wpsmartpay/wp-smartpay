<?php

namespace SmartPay\Modules\Invoice;

use SmartPay\Http\Controllers\Rest\Admin\InvoiceController;
use WP_REST_Server;

class Invoice {
	protected $app;

	public function __construct($app)
	{
		$this->app = $app;

		$this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

		$this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
	}

	public function adminScripts(  ) {
		//
	}

	public function registerRestRoutes(  ) {

		$invoiceController = $this->app->make(InvoiceController::class);

		register_rest_route('smartpay/v1', 'invoices', [
			[
				'methods'   => WP_REST_Server::READABLE,
				'callback'  => [$invoiceController, 'index'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
			[
				'methods'   => WP_REST_Server::CREATABLE,
				'callback'  => [$invoiceController, 'store'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
		]);

		register_rest_route('smartpay/v1', 'invoices/(?P<id>[\d]+)', [
			[
				'methods'   => WP_REST_Server::READABLE,
				'callback'  => [$invoiceController, 'preview'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
			[
				'methods'   => WP_REST_Server::READABLE,
				'callback'  => [$invoiceController, 'generatePdf'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
			[
				'methods'   => 'PUT, PATCH',
				'callback'  => [$invoiceController, 'update'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
			[
				'methods'   => WP_REST_Server::DELETABLE,
				'callback'  => [$invoiceController, 'destroy'],
				'permission_callback' => [$invoiceController, 'middleware'],
			],
		]);

	}
}