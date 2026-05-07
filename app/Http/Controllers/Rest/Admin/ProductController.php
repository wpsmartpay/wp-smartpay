<?php

namespace SmartPay\Http\Controllers\Rest\Admin;
defined('ABSPATH') || exit;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Product;
use WP_REST_Request;
use WP_REST_Response;

class ProductController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
     * @return \WP_Error|bool
     */
    public function middleware(WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.', 'smartpay'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    /**
     * Get all parent products with pagination
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $page     = (int) $request->get_param('page') ?: 1;
        $per_page = (int) $request->get_param('per_page') ?: 10;
        $search   = sanitize_text_field($request->get_param('search') ?? '');
        $sort_by  = sanitize_text_field($request->get_param('sort_by') ?? 'id:desc');

        $query = Product::where(function ($q) {
            $q->where('parent_id', 0)->orWhereNull('parent_id');
        })->with(['variations']);

        // Search
        if (!empty($search)) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        // Sorting
        $sort_parts = explode(':', $sort_by);
        $sort_column = in_array($sort_parts[0], ['id', 'title', 'created_at']) ? $sort_parts[0] : 'id';
        $sort_order  = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'asc' ? 'ASC' : 'DESC';
        $query->orderBy($sort_column, $sort_order);

        // Get total count
        $total = $query->count();
        $last_page = max(1, (int) ceil($total / $per_page));

        // Paginate
        $offset = ($page - 1) * $per_page;
        $products = $query->skip($offset)->take($per_page)->get();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $per_page, $total);

        return new WP_REST_Response([
            'products' => [
                'data'         => $products,
                'current_page' => $page,
                'per_page'     => $per_page,
                'last_page'    => $last_page,
                'total'        => $total,
                'from'         => $from,
                'to'           => $to,
            ],
        ]);
    }

    /**
     * Create new product
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query('START TRANSACTION');

        try {
            $request = json_decode($request->get_body());

            $product = new Product();
            $product->title = $request->title ?? 'Untitled product';
            $product->description = $request->description;
            $product->base_price = $request->base_price;
            $product->sale_price = $request->sale_price;
            $product->files = $request->files ?? [];
            $product->covers = $request->covers ?? [];
            $product->status = Product::PUBLISH;
            $product->extra = $request->extra ?? [];
            $product->settings = $request->settings ?? [];
            $result = $product->save(); // response true
            if($result && $product->id){

                array_walk($request->variations, function ($variationData) use ($product) {
                    $this->createVariation($variationData, $product->id);
                });

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query('COMMIT');

                // get the currently stored product
                $product = Product::find($product->id); // response product object

                return new WP_REST_Response(['product' => $product, 'message' => __('Product created', 'smartpay')]);
            }else{
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query('ROLLBACK');
	            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('Failed to create product.');
                return new WP_REST_Response(['message' => __('Failed to create product.', 'smartpay')], 500);
            }
        } catch (\Exception $e) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('ROLLBACK');
	        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($e->getMessage());
            return new WP_REST_Response(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single product with variations
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $product = Product::with(['variations'])->find($request->get_param('id'));

        if (!$product) {
            return new WP_REST_Response(['message' => __('Product not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['product' => $product]);
    }

    public function update(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query('START TRANSACTION');

        try {
            $product = Product::with(['variations'])->find($request->get_param('id'));

            if (!$product) {
                return new WP_REST_Response(['message' => __('Product not found', 'smartpay')], 404);
            }

            $request = json_decode($request->get_body());

            // Update product
            $product->title = $request->title;
            $product->description = $request->description;
            $product->base_price = $request->base_price;
            $product->sale_price = $request->sale_price;
            $product->files = $request->files ?? [];
            $product->covers = $request->covers ?? [];
            $product->extra = $request->extra;
            $product->settings = $request->settings;
            $product->status = Product::PUBLISH;
            $product->save();

            array_walk($request->variations, function ($variationData) use ($product) {
                if (!$variationData->id) {
                    return $this->createVariation($variationData, $product->id);
                }

                // Update variation
                $variation = Product::find($variationData->id);
                $variation->title = $variationData->title;
                $variation->description = $variationData->description;
                $variation->base_price = $variationData->base_price;
                $variation->sale_price = $variationData->sale_price;
                $variation->files = $variationData->files ?? [];
                $variation->status = Product::PUBLISH;
                $variation->extra = $variationData->extra ?? [];
                $variation->save();
            });

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('COMMIT');
            //            $product->refresh();
            $product->load('variations');
            return new WP_REST_Response(['product' => $product, 'message' => __('Product updated', 'smartpay')]);
        } catch (\Exception $e) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('ROLLBACK');
	        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }

    /**
     * Delete product
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query('START TRANSACTION');

        try {
            $product = Product::with(['variations'])->find($request->get_param('id'));

            if (!$product) {
                return new WP_REST_Response(['message' => __('Product not found', 'smartpay')], 404);
            }

            foreach ($product->variations as $variation) {
                $variation->delete();
            }

            $product->delete();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('COMMIT');
            return new WP_REST_Response(['message' => __('Product deleted', 'smartpay')], 200);
        } catch (\Exception $e) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('ROLLBACK');
	        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }

    /**
     * Create variation
     *
     * @param object $data
     * @param int $parentId
     * @return Product
     */
    private function createVariation($data, $parentId): Product
    {
        return Product::create([
            'title' => $data->title,
            'description' => $data->description,
            'base_price' => $data->base_price,
            'sale_price' => $data->sale_price,
            'covers' => [],
            'files' => $data->files ?? [],
            'parent_id' => $parentId,
            'status' => Product::PUBLISH,
            'extra' => $data->extra ?? [],
        ]);
    }
}
