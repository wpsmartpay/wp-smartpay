<?php

namespace SmartPay\Modules\Product;

use SmartPay\Framework\Application;
use SmartPay\Http\Controllers\Rest\Admin\ProductController;
use WP_REST_Server;

class Product
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);

        $this->app->addAction('smartpay_create_product_preview_page',[$this, 'createProductPreviewPage']);
        $this->app->addAction('smartpay_update_product_preview_page',[$this, 'updateProductPreviewPage']);
        $this->app->addAction('smartpay_delete_product_preview_page',[$this, 'deleteProductPreviewPage']);
    }

    public function adminScripts()
    {
        //
    }

    public function registerRestRoutes()
    {
        $productController = $this->app->make(ProductController::class);

        register_rest_route('smartpay/v1', 'products', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'index'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$productController, 'store'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1', 'products/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'show'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$productController, 'update'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$productController, 'destroy'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);
    }

    public function createProductPreviewPage($product) {
        if( ! $product->parent_id ) {
            $pageArr = [
                'post_title'    => $product->title ?? 'Untitled Product',
                'post_status'   => 'publish',
                'post_content'  => '<!-- wp:shortcode -->[smartpay_product id="'.$product->id.'" behavior="embedded" label=""]<!-- /wp:shortcode -->',
                'post_type'     => 'page'
            ];
    
            $pageId = wp_insert_post( $pageArr );
            if( is_wp_error( $pageId ) ) {
                return;
            }
            $product->extra = array_merge($product->extra,['product_preview_page_id' => $pageId,'product_preview_page_permalink' => get_permalink($pageId)]);
            $product->save();
        }
    }

    public function updateProductPreviewPage( $product ) {
        if(  ! $product->parent_id ) {
            $extraFields = $product->extra;
            if( is_array($extraFields) && array_key_exists('product_preview_page_id',$extraFields) ) {
                return;
            }

            $pageArr = [
                'post_title'    => $product->title ?? 'Untitled Product',
                'post_status'   => 'publish',
                'post_content'  => '<!-- wp:shortcode -->[smartpay_product id="'.$product->id.'" behavior="embedded" label=""]<!-- /wp:shortcode -->',
                'post_type'     => 'page'
            ];

            $pageId = wp_insert_post( $pageArr );
            if( is_wp_error( $pageId ) ) {
                return;
            }
            $product->extra = array_merge($product->extra,['product_preview_page_id' => $pageId,'product_preview_page_permalink' => get_permalink($pageId)]);
            $product->save();
        }
    }

    public function deleteProductPreviewPage( $product ) {
        $extraFields = $product->extra;
        if( is_array($extraFields) && array_key_exists('product_preview_page_id',$extraFields) ) {
            wp_delete_post( $extraFields['product_preview_page_id'] );
        }
    }
}
