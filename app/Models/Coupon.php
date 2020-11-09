<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'smartpay_coupons';

    protected $fillable = [
        'title',
        'description',
        'discount_type',
        'discount_amount',
        'status',
        'expiry_date',
        'extra',
    ];
}