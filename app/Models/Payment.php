<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'smartpay_payments';

    protected $fillable = [
        'type',
        'data',
        'amount',
        'currency',
        'gateway',
        'transaction_id',
        'customer_id',
        'email',
        'key',
        'parent_payment',
        'mode',
        'status',
        'extra',
        'completed_at',
    ];

    const PENDING = 'pending';

    const PRODUCT_PURCHASE = 'product_purchase';

    /**
     * Update payment status
     *
     * @param string $status
     * @return void
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        return $this->save();
    }
}