<?php

namespace SmartPay\Models;
defined('ABSPATH') || exit;

use SmartPay\Framework\Database\Eloquent\Model;

defined( 'ABSPATH' ) || exit;

class PaymentLog extends Model {

	protected $table = 'smartpay_payment_logs';

	public $timestamps = false;

	protected $fillable = array(
		'payment_id',
		'user_id',
		'action',
		'note',
	);

	protected $casts = array(
		'payment_id' => 'integer',
	);

	public function payment() {
		return $this->belongsTo( Payment::class, 'payment_id', 'id' );
	}

	public function getCreatedAtAttribute( $value ) {
		return $value;
	}
}
