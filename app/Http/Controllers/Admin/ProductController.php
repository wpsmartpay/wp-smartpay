<?php

namespace SmartPay\Http\Controllers\Admin;

use SmartPay\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        echo view('admin.product.index');
    }

    public function create()
    {
        echo view('admin.product.create');
    }
}