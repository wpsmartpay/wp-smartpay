<?php

namespace SmartPay\Http\Controllers\Admin;

use SmartPay\Http\Controllers\Controller;

class FormController extends Controller
{
    public function index()
    {
        echo view('admin.form.index');
    }

    public function create()
    {
        echo view('admin.form.create');
    }

    public function edit()
    {
        echo view('admin.form.create');
    }
}