<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Models\Product;
use WP_REST_Request;
use WP_REST_Response;

class ProductController extends \WP_REST_Controller
{
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request.
     * @return \WP_Error|bool
     */
    public function middleware(WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    /**
     * Get all parent products
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $products = Product::where('parent', 0)->get();

        return new WP_REST_Response($products);
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
        $wpdb->query('START TRANSACTION');

        try {
            $request = json_decode($request->get_body());

            $parent = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'sale_price' => $request->sale_price,
                'files' => $request->files,
                'covers' => $request->covers,
                'status' => Product::PUBLISH,
            ]);

            array_walk($request->variations, function ($variation) use ($parent) {
                Product::create([
                    'title' => $variation->title,
                    'description' => $variation->description,
                    'base_price' => $variation->base_price,
                    'sale_price' => $variation->sale_price,
                    'files' => $variation->files,
                    'parent' => $parent->id,
                    'status' => Product::PUBLISH,
                ]);
            });
            $wpdb->query('COMMIT');

            return new WP_REST_Response($parent, 200);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }

    /**
     * Get a single product with variations
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function view(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $product = Product::with(['variations'])->find($request->get_param('id'));
            if (!$product) {
                return new WP_REST_Response(['message' => 'Product not found'], 404);
            }

            return new WP_REST_Response($product);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }

    public function update(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');

        try {
            $product = Product::with(['variations'])->find($request->get_param('id'));

            if (!$product) {
                return new WP_REST_Response(['message' => 'Product not found'], 404);
            }

            $request = json_decode($request->get_body());

            // Update product
            $product->title = $request->title;
            $product->description = $request->description;
            $product->base_price = $request->base_price;
            $product->sale_price = $request->sale_price;
            $product->files = $request->files;
            $product->covers = $request->covers;
            $product->status = Product::PUBLISH;
            $product->save();

            // array_walk($request->variations, function ($variation) use ($product) {
            //     Product::create([
            //         'title' => $variation->title,
            //         'description' => $variation->description,
            //         'base_price' => $variation->base_price,
            //         'sale_price' => $variation->sale_price,
            //         'files' => $variation->files,
            //         'parent' => $product->id,
            //         'status' => Product::PUBLISH,
            //     ]);
            // });
            $wpdb->query('COMMIT');
            return new WP_REST_Response($product);
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }
}
