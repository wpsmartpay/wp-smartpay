<?php

namespace SmartPay\Models;
defined('ABSPATH') || exit;

use SmartPay\Models\Customer;
use SmartPay\Models\PaymentLog;
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
        'uuid',
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
        static::creating(function ($payment) {
            // Work on the raw attribute, not $payment->status — the status
            // accessor capitalises the value ('completed' → 'Completed'), and
            // assigning that back would persist the capitalised form, which then
            // fails to match the lowercase status constants on read (every case
            // falls through to the default 'Pending'). Only default when unset.
            if ( empty( $payment->attributes['status'] ) ) {
                $payment->attributes['status'] = self::PENDING;
            }
        });

        static::saving(function ($payment) {
            if ($payment->isDirty('status')) {
                $old_status = $payment->original['status'] ?? self::PENDING;
                $new_status = $payment->attributes['status'];

                do_action(
                    'smartpay_update_payment_status',
                    $payment,
                    $new_status,
                    $old_status
                );

                if ( $payment->id && function_exists( 'smartpay_record_payment_log' ) ) {
                    smartpay_record_payment_log(
                        (int) $payment->id,
                        'status_changed',
                        sprintf( '%s → %s', $old_status, $new_status )
                    );
                }
            }
        });
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class, 'payment_id', 'id');
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
        return json_decode((string) $data, true);
    }

    public function setExtraAttribute($extra)
    {
        $this->attributes['extra'] = \json_encode($extra);
    }

    public function updatePaymentExtra(string $mainKey, string $childKey, string $value)
    {
        $extra = $this->extra ?? [];
        $keyExtra = $extra[$mainKey] ?? [];
        $keyExtra[$childKey] = $value;
        $extra[$mainKey] = $keyExtra;
        $this->extra = $extra;
        $this->save();
    }

    public function getExtraAttribute($extra)
    {
        return json_decode((string) $extra, true);
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

    /**
     * Get formatted payment number
     *
     * @return string
     */
    public function get_payment_number(): string
    {
        if (!$this->id) {
            return '';
        }

        $id = (int) $this->id;
        
        $starting_number = smartpay_get_option('payment_number_starting', '');
        if (!empty($starting_number) && is_numeric($starting_number)) {
            $id += (int) $starting_number;
        }

        $id = (string) $id;

        $padding = smartpay_get_option('payment_number_padding', '');
        
        if (!empty($padding) && is_numeric($padding)) {
            $id = str_pad($id, (int)$padding, '0', STR_PAD_LEFT);
        }

        return $id;
    }
}
