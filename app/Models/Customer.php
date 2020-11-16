<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'smartpay_customers';

    protected $fillable = [];

    public static function boot()
    {
        static::creating(function ($form) {
            $time = date('Y-m-d h-i-s');

            $form->created_at = $time;
            $form->updated_at = $time;
        });
    }
}