<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'smartpay_payments';

    protected $fillable = [];

    const PENDING = 'pending';

    const PRODUCT_PURCHASE = 'product_purchase';
}
