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

    const PUBLISH   = 'publish';
    const DRAFT     = 'draft';

    public function getExpiryDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }
}
