<?php

namespace SmartPay\Http\Controllers\Admin;

use SmartPay\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        echo view('admin.customer.index');
    }

    public function create()
    {
        echo view('admin.customer.create');
    }
}