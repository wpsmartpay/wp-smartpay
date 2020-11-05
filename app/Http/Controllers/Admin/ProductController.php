<?php

namespace SmartPay\Http\Controllers\Admin;

use SmartPay\Framework\Http\Request;
use SmartPay\Http\Controllers\Controller;

use SmartPay\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        echo view('admin.product.index');
    }

    public function create()
    {

        // echo json_encode([
        //     [
        //         'filename' => 'test file',
        //     ]
        // ]);

        // dd(Product::find(1)->variations);

        // foreach (Product::find(1)->variations as $var) {
        //     echo $var->title;
        //     echo '<br>';
        // }

        // $product = Product::create(
        //     [
        //         'title' => 'Test Product - Variation 1',
        //         'description' => 'Test Product',
        //         'files' => ['file - 1'],
        //         'base_price' => 90,
        //         'sale_price' => 80,
        //         'parent' => 1,
        //         'status' => 'aaa',
        //     ]
        // );
        echo view('admin.product.create', ['product' => Product::findOrFail(1)]);
    }

    public function store(Request $request)
    {
        echo '<pre>';
        dd($request);

        $product = Product::create(
            [
                'title' => 'Test Product - Variation 1',
                'description' => 'Test Product',
                'files' => ['file - 1'],
                'base_price' => 90,
                'sale_price' => 80,
                'parent' => 1,
                'status' => 'aaa',
            ]
        );

        wp_redirect(admin_url("admin.php?page=smartpay-products&action=edit&id={$product->id}"));
    }

    public function edit(Product $product)
    {
        echo view('admin.product.create', ['product' => $product]);
    }
}