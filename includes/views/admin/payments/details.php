<?php
use SmartPay\Payments\SmartPay_Payment;
$post_type = $_GET['post_type'] ?: null;
$post_type = $_GET['page'] ?: null;
$payment_ID = $_GET['id'] ?: null;
// $payment_details = get_post_meta($payment_ID);
$payment_details = new SmartPay_Payment($payment_ID);
$customer_details = $payment_details->customer;
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Payment Details #<?php echo $payment_ID;?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container">
                <div id="submitdiv" class="postbox">
                    <h2 class="hndle"><span>Publish</span></h2>
                    <div class="inside">
                        <div class="submitbox" id="submitpost">
                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <span class="spinner"></span>
                                    <input name="original_publish" type="hidden" id="original_publish" value="Publish">
                                    <input type="submit" name="publish" id="publish"
                                        class="button button-primary button-large" value="Publish"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <div id="normal-sortables">
                    <div id="smartpay-form-metabox-data" class="postbox ">
                        <h2 class="hndle"><span>Payment Form Options</span></h2>
                        <div class="inside">
                        <?php 
                            echo '<pre>';
                                var_dump($payment_details);
                            echo '</pre>';
                        ?>
                        <div class="column-container">

                        </div>
                        </div>
                    </div>
                    <div id="payment-details" class="postbox">
                        <h2 class="hndle">Payment Details</h2>
                        <div class="inside">
                            <div class="column-container">
                                <div class="column">
                                    <b class="payment-amount"><?php echo $payment_details->amount . ' ' . $payment_details->currency;?></b>
                                    <span class="status"><?php echo $payment_details->status; ?></span>
                                </div>
                                <div class="column">
                                    <p>Transaction Key: <?php echo $payment_details->key; ?></p>
                                </div>
                            </div>
                            <div class="column-container">
                                <div class="column">
                                    <p>Date</p>
                                    <p><?php echo $payment_details->date; ?></p>
                                </div>
                                <div class="column">
                                    <p>Customer</p>
                                    <p><?php echo $payment_details->email; ?></p>
                                </div>
                                <div class="column">
                                    <p>Payment Method</p>
                                    <p><?php echo $payment_details->mode; ?></p>
                                </div>
                                <div class="column">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="customer-details" class="postbox ">
                        <h2 class="hndle"><span>Customer Details</span></h2>
                        <div class="inside">
                            <div class="column-container">
                                <div class="column">
                                    <p>Name: <?php echo $customer_details['first_name'] . ' ' . $customer_details['last_name']; ?></p>
                                    <p>Email: <?php echo $customer_details['email']; ?></p>
                                </div>
                            </div>
                            <p></p>
                        </div>
                    </div>
                </div>
                <div id="advanced-sortables"></div>
            </div>
        </div><!-- /post-body -->
        <br class="clear">
    </div><!-- /poststuff -->

</div>