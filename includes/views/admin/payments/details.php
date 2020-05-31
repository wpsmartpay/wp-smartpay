<?php
use SmartPay\Payments\SmartPay_Payment;
$post_type = $_GET['post_type'] ?: null;
$post_type = $_GET['page'] ?: null;
$payment_ID = $_GET['id'] ?: null;
// $payment_details = get_post_meta($payment_ID);
$payment_details = new SmartPay_Payment($payment_ID);
$customer_details = $payment_details->customer;
?>

<div class="wrap payment-details">
    <h1 class="wp-heading-inline">Payment #<?php echo $payment_ID;?></h1>
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
                    <!-- <div id="smartpay-form-metabox-data" class="postbox ">
                        <h2 class="hndle"><span>Payment Form Options</span></h2>
                        <div class="inside">
                        <?php 
                            // echo '<pre>';
                            //     var_dump($payment_details);
                            // echo '</pre>';
                        ?>
                        <div class="column-container">

                        </div>
                        </div>
                    </div> -->
                    <div id="payment-details" class="postbox">
                        <h2 class="hndle">Details</h2>
                        <div class="inside">
                            <div class="smartpay">
                                <div class="column-container row d-flex align-items-center border-bottom py-3">
                                    <div class="column col-6 d-flex">
                                        <h3 class="m-0 h3 mr-3"><b class="payment-amount"><?php echo $payment_details->amount . ' ' . $payment_details->currency;?></b></h3>
                                        <span class="btn btn-info px-2 py-0 pb-1"><?php echo ucfirst($payment_details->status); ?></span>
                                    </div>
                                    <div class="column col-6 text-right">
                                        <p>Transaction Key: <?php echo $payment_details->key; ?></p>
                                    </div>
                                </div>
                                <div class="column-container row pt-3">
                                    <div class="column col-3">
                                        <b>Date</b>
                                        <p><?php echo $payment_details->date; ?></p>
                                    </div>
                                    <div class="column col-3">
                                        <b>Customer</b>
                                        <p><?php echo $payment_details->email; ?></p>
                                    </div>
                                    <div class="column col-3">
                                        <b>Payment Method</b>
                                        <p><?php echo $payment_details->mode; ?></p>
                                    </div>
                                    <div class="column col-3">
                                        <b>Transaction ID</b>
                                        <p><?php echo $payment_details->ID; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="checkout-details" class="postbox">
                        <h2 class="hndle">Checkout Details</h2>
                        <div class="inside">
                            <div class="smartpay">
                                <table class="table table-bordered col-12">
                                    <thead>
                                        <tr>
                                            <th scope="col">Item</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Unit Price</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Pro</td>
                                            <td>1</td>
                                            <td>10</td>
                                            <td>10</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3">Total</td>
                                            <td>20</td>
                                        </tr>
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="customer-details" class="postbox ">
                        <h2 class="hndle"><span>Customer Details</span></h2>
                        <div class="inside">
                            <div class="column-container">
                                <div class="column">
                                    <p><b>Name:</b> <?php echo $customer_details['first_name'] . ' ' . $customer_details['last_name']; ?></p>
                                    <p><b>Email:</b> <?php echo $customer_details['email']; ?></p>
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