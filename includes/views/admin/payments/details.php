<?php
use SmartPay\Payments\SmartPay_Payment;
echo 'payment details page';
$post_type = $_GET['post_type'] ?: null;
$post_type = $_GET['page'] ?: null;
$payment_ID = $_GET['id'] ?: null;
// $payment_details = get_post_meta($payment_ID);
$payment_details = new SmartPay_Payment($payment_ID);
echo '<pre>';
var_dump($payment_details);
// var_dump(unserialize($payment_details['_payment_purchase_data'][0]));
echo '</pre>';
?>
<div id="post-body">
<div class="postbox-container">
    <div class="row header">Header</div>
    <div class="row">Body</div>
</div>
</div>