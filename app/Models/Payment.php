<?php

namespace SmartPay\Models;

use SmartPay\Models\Customer;
use SmartPay\Framework\Database\Eloquent\Model;
use SmartPay\Framework\Database\Eloquent\Relation\HasMany;
use SmartPay\Framework\Database\Eloquent\Relation\HasOne;

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
        'parent_id',
        'mode',
        'status',
        'extra',
        'completed_at',
    ];

    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';
    const ABANDONED = 'abandoned';
    const REVOKED = 'revoked';
    const PROCESSING = 'processing';

    const PRODUCT_PURCHASE = 'product_purchase';
    const FORM_PAYMENT = 'form_payment';


    const BILLING_TYPE_ONE_TIME = 'One Time';
    const BILLING_TYPE_SUBSCRIPTION = 'Subscription';

    const BILLING_PERIOD_DAILY = 'Daily';
    const BILLING_PERIOD_WEEKLY = 'Weekly';
    const BILLING_PERIOD_MONTHLY = 'Monthly';
    const BILLING_PERIOD_QUARTERLY = 'Every 3 Months';
    const BILLING_PERIOD_SEMIANNUAL = 'Every 6 Months';
    const BILLING_PERIOD_YEARLY = 'Yearly';

    public static function boot()
    {
        static::creating(function ($product) {
            $product->status = $product->status ?: self::PENDING;
        });

        static::saving(function ($payment) {
            if ($payment->isDirty('status')) {
                do_action(
                    'smartpay_update_payment_status',
                    $payment,
                    $payment->attributes['status'],
                    $payment->original['status'] ?? self::PENDING
                );
            }
        });
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    /**
     * Get parent payment
     *
     * @return \SmartPay\Framework\Database\Eloquent\Relation\HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'parent_id');
    }

    /**
     * Get related payments
     *
     * @return \SmartPay\Framework\Database\Eloquent\Relation\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_id', 'id');
    }

    public function getTypeAttribute($type)
    {
        switch ($type) {
            case self::PRODUCT_PURCHASE:
            default:
                return 'Product Purchase';
                break;

            case self::FORM_PAYMENT:
                return 'Form Payment';
                break;
        }
    }

    public function getStatusAttribute($status)
    {
        switch ($status) {
            case self::PENDING:
            default:
                return 'Pending';
                break;

            case self::COMPLETED:
                return 'Completed';
                break;

            case self::REFUNDED:
                return 'Refunded';
                break;

            case self::FAILED:
                return 'Failed';
                break;

            case self::ABANDONED:
                return 'Abandoned';
                break;

            case self::REVOKED:
                return 'Revoked';
                break;

            case self::PROCESSING:
                return 'Processing';
                break;
        }
    }

    public function setDataAttribute($data)
    {
        $this->attributes['data'] = \json_encode($data);
    }

    public function getDataAttribute($data)
    {
        return json_decode($data, true);
    }

    public function setExtraAttribute($extra)
    {
        $this->attributes['extra'] = \json_encode($extra);
    }

    public function getExtraAttribute($extra)
    {
        return json_decode($extra, true);
    }

    //FIXME
    public function getType()
    {
        return $this->attributes['type'];
    }

    /**
     * Update payment status
     *
     * @param string $status
     * @return boolean
     */
    public function updateStatus(string $status)
    {
        $this->status = $status;
        return $this->save();
    }

    /**
     * Set transaction id
     *
     * @param string $transactionId
     * @return boolean
     */
    public function setTransactionId(string $transactionId)
    {
        $this->transaction_id = $transactionId;
        return $this->save();
    }
}