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
        $page         = (int) $request->get_param('page') ?: 1;
        $per_page     = (int) $request->get_param('per_page') ?: 10;
        $search       = sanitize_text_field($request->get_param('search') ?? '');
        $sort_by      = sanitize_text_field($request->get_param('sort_by') ?? 'id:desc');
        $billing_type = sanitize_text_field($request->get_param('billing_type') ?? '');

        $query = Product::where(function ($q) {
            $q->where('parent_id', 0)->orWhereNull('parent_id');
        })->with(['variations']);

        // Search
        if (!empty($search)) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        // Billing type filter — matches on parent extra OR any variation extra
        if (!empty($billing_type)) {
            $like = '%"billing_type":"' . $billing_type . '"%';
            $query->where(function ($q) use ($like) {
                $q->where('extra', 'LIKE', $like)
                  ->orWhereHas('variations', function ($vq) use ($like) {
                      $vq->where('extra', 'LIKE', $like);
                  });
            });
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
            $data = json_decode($request->get_body(), true);

            $product = new Product();
            $product->title       = sanitize_text_field($data['title'] ?? 'Untitled product');
            $product->description = sanitize_textarea_field($data['description'] ?? '');
            $product->base_price  = max(0, (float) ($data['base_price'] ?? 0));
            $product->sale_price  = max(0, (float) ($data['sale_price'] ?? 0));
            $product->files       = $this->sanitize_files((array) ($data['files'] ?? []));
            $product->covers      = $this->sanitize_covers((array) ($data['covers'] ?? []));
            $product->status      = Product::PUBLISH;
            $product->extra       = $this->sanitize_recursive((array) ($data['extra'] ?? []));
            $product->settings    = $this->sanitize_recursive((array) ($data['settings'] ?? []));
            $result = $product->save();

            if ($result && $product->id) {
                array_walk($data['variations'] ?? [], function ($variationData) use ($product) {
                    $this->createVariation($variationData, $product->id);
                });

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query('COMMIT');

                $product = Product::find($product->id);

                return new WP_REST_Response(['product' => $product, 'message' => __('Product created', 'smartpay')]);
            } else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query('ROLLBACK');
		        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('SmartPay: Failed to create product.');
                return new WP_REST_Response(['message' => __('Failed to create product.', 'smartpay')], 500);
            }
        } catch (\Exception $e) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('ROLLBACK');
	        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('SmartPay: Product create error — ' . $e->getMessage());
            return new WP_REST_Response(['message' => esc_html__('An error occurred. Please try again.', 'smartpay')], 500);
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

            $data = json_decode($request->get_body(), true);

            $product->title       = sanitize_text_field($data['title'] ?? 'Untitled product');
            $product->description = sanitize_textarea_field($data['description'] ?? '');
            $product->base_price  = max(0, (float) ($data['base_price'] ?? 0));
            $product->sale_price  = max(0, (float) ($data['sale_price'] ?? 0));
            $product->files       = $this->sanitize_files((array) ($data['files'] ?? []));
            $product->covers      = $this->sanitize_covers((array) ($data['covers'] ?? []));
            $product->extra       = $this->sanitize_recursive((array) ($data['extra'] ?? []));
            $product->settings    = $this->sanitize_recursive((array) ($data['settings'] ?? []));
            $product->status      = Product::PUBLISH;
            $product->save();

            array_walk($data['variations'] ?? [], function ($variationData) use ($product) {
                $variationData = (array) $variationData;
                if (empty($variationData['id'])) {
                    return $this->createVariation($variationData, $product->id);
                }

                $variation = Product::find($variationData['id']);
                if (!$variation) {
                    return;
                }
                $variation->title       = sanitize_text_field($variationData['title'] ?? '');
                $variation->description = sanitize_textarea_field($variationData['description'] ?? '');
                $variation->base_price  = max(0, (float) ($variationData['base_price'] ?? 0));
                $variation->sale_price  = max(0, (float) ($variationData['sale_price'] ?? 0));
                $variation->files       = $this->sanitize_files((array) ($variationData['files'] ?? []));
                $variation->status      = Product::PUBLISH;
                $variation->extra       = $this->sanitize_recursive((array) ($variationData['extra'] ?? []));
                $variation->save();
            });

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('COMMIT');
            $product->load('variations');
            return new WP_REST_Response(['product' => $product, 'message' => __('Product updated', 'smartpay')]);
        } catch (\Exception $e) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query('ROLLBACK');
	        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('SmartPay: Product update error — ' . $e->getMessage());
            return new WP_REST_Response(['message' => esc_html__('An error occurred. Please try again.', 'smartpay')], 500);
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
            error_log('SmartPay: Product delete error — ' . $e->getMessage());
            return new WP_REST_Response(['message' => esc_html__('An error occurred. Please try again.', 'smartpay')], 500);
        }
    }

    /**
     * Create variation
     *
     * @param array $data
     * @param int   $parentId
     * @return Product
     */
    private function createVariation(array $data, int $parentId): Product
    {
        return Product::create([
            'title'       => sanitize_text_field($data['title'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'base_price'  => max(0, (float) ($data['base_price'] ?? 0)),
            'sale_price'  => max(0, (float) ($data['sale_price'] ?? 0)),
            'covers'      => [],
            'files'       => $this->sanitize_files((array) ($data['files'] ?? [])),
            'parent_id'   => $parentId,
            'status'      => Product::PUBLISH,
            'extra'       => $this->sanitize_recursive((array) ($data['extra'] ?? [])),
        ]);
    }

    /**
     * Sanitize a files array: name (sanitize_text_field), url/icon/src (esc_url_raw).
     *
     * @param array $files
     * @return array
     */
    private function sanitize_files(array $files): array
    {
        return array_map(function ($file) {
            if (!is_array($file)) {
                return [];
            }
            $clean = [];
            foreach ($file as $key => $value) {
                if (in_array($key, ['url', 'icon', 'src'], true)) {
                    $clean[sanitize_text_field($key)] = esc_url_raw((string) $value);
                } elseif (is_array($value)) {
                    $clean[sanitize_text_field($key)] = $this->sanitize_recursive($value);
                } else {
                    $clean[sanitize_text_field($key)] = sanitize_text_field((string) $value);
                }
            }
            return $clean;
        }, array_values($files));
    }

    /**
     * Sanitize a covers array: url/icon/src (esc_url_raw), other keys (sanitize_text_field).
     *
     * @param array $covers
     * @return array
     */
    private function sanitize_covers(array $covers): array
    {
        return $this->sanitize_files($covers);
    }

    /**
     * Recursively sanitize an array.
     *
     * @param array $data
     * @return array
     */
    private function sanitize_recursive(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean_key = sanitize_text_field((string) $key);
            if (is_array($value)) {
                $clean[$clean_key] = $this->sanitize_recursive($value);
            } elseif (is_bool($value)) {
                $clean[$clean_key] = $value;
            } elseif (is_int($value)) {
                $clean[$clean_key] = (int) $value;
            } elseif (is_float($value)) {
                $clean[$clean_key] = (float) $value;
            } else {
                $clean[$clean_key] = sanitize_text_field((string) $value);
            }
        }
        return $clean;
    }
}
