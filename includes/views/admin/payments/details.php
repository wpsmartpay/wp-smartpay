<?php

use SmartPay\Payments\SmartPay_Payment;

$payment_id = intval($_GET['id'] ?? null);

$payment = new SmartPay_Payment($payment_id);

?>

<div class="wrap payment-details">
    <h1 class="wp-heading-inline">Payment #<?php echo $payment_id; ?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <?php if (!$payment->ID) : ?>

        <p>Payment not found.</p>

        <?php else : ?>

        <div id="post-body" class="metabox-holder columns-1">
            <!-- <div id="postbox-container-1" class="postbox-container">
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
            </div> -->
            <div id="postbox-container-2" class="postbox-container">
                <div id="payment-details" class="postbox">
                    <h2 class="hndle">Details</h2>
                    <div class="smartpay">
                        <div class="d-flex align-items-center border-bottom py-3">
                            <div class="col d-flex">
                                <h3 class="my-1 h3 mr-3">
                                    <strong class="payment-amount"><?php echo smartpay_amount_format($payment->amount, $payment->currency); ?></strong>
                                </h3>
                                <span class="btn px-2 py-0 pb-1 <?php echo 'publish' == $payment->status ? 'btn-success' : 'btn-danger'; ?>"><?php echo $payment->status_nicename ?? '-'; ?></span>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <h3 class="h3 text-primary px-2 my-0 pb-1">
                                    <?php echo ucwords(str_replace('_', ' ', $payment->payment_type)); ?></h3>
                            </div>
                        </div>
                        <div class="d-flex flex-lg-row flex-column py-2">
                            <div class="col">
                                <p><strong>Date</strong></p>
                                <p><?php echo $payment->date; ?></p>
                            </div>
                            <div class="col">
                                <p><strong>Customer</strong></p>
                                <p><?php echo $payment->email; ?></p>
                            </div>
                            <div class="col">
                                <p><strong>Payment Method</strong></p>
                                <p><?php echo smartpay_payment_gateways()[$payment->gateway]['admin_label'] ?? ucfirst($payment->email); ?>
                                </p>
                            </div>
                            <div class="col">
                                <p><strong>Transaction ID</strong></p>
                                <p><?php echo $payment->transaction_id ?? __('N/A', 'smartpay'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="checkout-details" class="postbox">
                    <h2 class="hndle">Checkout Details</h2>
                    <div class="smartpay">
                        <div class="d-flex flex-lg-row flex-column py-2">

                            <?php if ('product_purchase' == $payment->payment_type) : ?>

                            <?php $payment_data = $payment->payment_data; ?>

                            <?php $product = smartpay_get_product($payment_data['product_id']); ?>

                            <?php if (!is_object($product) || !$product->ID) : ?>
                            <div class="col">
                                <p class="text-center text-danger"><strong>Product not found!</strong></p>
                            </div>
                            <?php else : ?>
                            <div class="col">
                                <p><strong>Product</strong></p>
                                <p>
                                    <strong><?php echo '#' . $payment_data['product_id'] ?? ''; ?></strong> &nbsp;
                                    <span><?php echo $product->title ?? ''; ?></span>
                                </p>
                            </div>

                            <div class="col">
                                <p><strong>Product Price</strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['product_price'] ?? '', $payment->currency); ?>
                                </p>
                            </div>

                            <!-- If payment has variation -->
                            <?php if (isset($payment_data['variation_id'])) : ?>

                            <?php $variation = smartpay_get_product_variation($payment_data['variation_id']); ?>

                            <?php if ($variation->parent != $product->ID) : ?>

                            <div class="col">
                                <p class="text-center text-danger">
                                    <strong>Product variation not associated with this
                                        product!</strong>
                                </p>
                            </div>

                            <?php else : ?>

                            <div class="col">
                                <p><strong>Product Variation</strong></p>
                                <p><?php echo $variation->name ?? ''; ?></p>
                            </div>

                            <div class="col">
                                <p><strong>Additional Amount</strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['additional_amount'] ?? '', $payment->currency); ?>
                                </p>
                            </div>

                            <?php endif; ?>

                            <?php endif; ?>

                            <div class="col">
                                <p><strong>Total Amount</strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['total_amount'] ?? '', $payment->currency); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div id="customer-details" class="postbox ">
                    <?php $customer = $payment->customer; ?>
                    <h2 class="hndle"><span>Customer Details</span></h2>
                    <div class="smartpay">
                        <div class="d-flex flex-lg-row flex-column py-2">
                            <div class="col">
                                <p><strong>First Name</strong></p>
                                <p><?php echo $customer['first_name'] ?: '-'; ?></p>
                            </div>
                            <div class="col">
                                <p><strong>Last Name</strong></p>
                                <p><?php echo $customer['last_name'] ?: '-'; ?></p>
                            </div>
                            <div class="col">
                                <p><strong>Email</strong></p>
                                <p><?php echo $customer['email']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /post-body -->

        <?php endif; ?>

        <br class="clear">
    </div><!-- /poststuff -->
</div>