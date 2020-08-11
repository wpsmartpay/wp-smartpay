<?php

use SmartPay\Payments\SmartPay_Payment;

$payment_id = intval(sanitize_text_field($_GET['id']) ?? null);

$payment = new SmartPay_Payment($payment_id);
?>

<div class="wrap payment-details">
    <h1 class="wp-heading-inline"><?php echo __('Payment #', 'smartpay') . $payment_id; ?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <?php if (!$payment->ID) : ?>

        <p><?php _e('Payment id invalid not found.', 'smartpay'); ?></p>

        <?php else : ?>

        <?php $payment_data = $payment->payment_data; ?>

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
                    <h2 class="hndle"><?php _e('Details', 'smartpay'); ?></h2>
                    <div class="smartpay">
                        <div class="d-flex align-items-center border-bottom py-3">
                            <div class="col d-flex">
                                <h3 class="my-1 h3 mr-3">
                                    <strong class="payment-amount"><?php echo smartpay_amount_format($payment->amount, $payment->currency); ?></strong>
                                </h3>
                                <span class="btn px-2 py-0 pb-1 <?php echo 'publish' == $payment->status ? 'btn-success' : 'btn-danger'; ?>"><?php echo $payment->status_nicename ?? '-'; ?></span>
                            </div>
                            <div class="col d-flex justify-content-center">
                                <h3 class="h3 text-primary px-2 my-0 pb-1">
                                    <?php echo ucwords(str_replace('_', ' ', $payment->payment_type)); ?>
                                </h3>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <form action="#" method="POST">
                                    <?php wp_nonce_field('smartpay_update_payment', 'smartpay_update_payment'); ?>
                                    <input type="hidden" id="payment_id" name="payment_id" value="<?php echo $payment->ID ?>">
                                    <div class="input-group">
                                        <select class="custom-select" id="payment_status" name="payment_status">
                                            <option disabled><?php _e('Select status', 'smartpay') ?></option>
                                            <?php foreach (smartpay_get_payment_statuses() as $key => $status) : ?>
                                            <option value="<?php echo $key; ?>" <?php if ($payment->status == $key) echo 'selected'; ?>><?php echo $status; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="submit">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="d-flex flex-lg-row flex-column py-2">
                            <div class="col">
                                <p><strong><?php _e('Date', 'smartpay'); ?></strong></p>
                                <p><?php echo $payment->date; ?></p>
                            </div>
                            <div class="col">
                                <p><strong><?php _e('Customer', 'smartpay'); ?></strong></p>
                                <p><?php echo $payment->email; ?></p>
                            </div>
                            <div class="col">
                                <p><strong><?php _e('Payment Method', 'smartpay'); ?></strong></p>
                                <p><?php echo smartpay_payment_gateways()[$payment->gateway]['admin_label'] ?? ucfirst($payment->email); ?>
                                </p>
                            </div>
                            <div class="col">
                                <p><strong><?php _e('Transaction ID', 'smartpay'); ?></strong></p>
                                <p><?php echo $payment->transaction_id ?? __('N/A', 'smartpay'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="checkout-details" class="postbox">
                    <h2 class="hndle"><?php echo ucwords(str_replace('_', ' ', $payment->payment_type)); ?> Details</h2>
                    <div class="smartpay">
                        <div class="d-flex flex-lg-row flex-column py-2">
                            <?php if (!is_array($payment_data) || !count($payment_data)) : ?>

                            <div class="col">
                                <p class="text-center text-danger"><strong><?php _e('Payment data not found!', 'smartpay'); ?></strong></p>
                            </div>

                            <?php else : ?>

                            <!-- // Product purchase -->
                            <?php if ('product_purchase' == $payment->payment_type) : ?>

                            <?php $product = smartpay_get_product($payment_data['product_id']); ?>

                            <?php if (!is_object($product) || !$product->ID) : ?>
                            <div class="col">
                                <p class="text-center text-danger"><strong><?php _e('Product not found!', 'smartpay'); ?></strong></p>
                            </div>
                            <?php else : ?>
                            <div class="col">
                                <p><strong><?php _e('Product', 'smartpay'); ?></strong></p>
                                <p>
                                    <strong><?php echo '#' . $payment_data['product_id'] ?? ''; ?></strong> &nbsp;
                                    <span><?php echo $product->title ?? ''; ?></span>
                                </p>
                            </div>

                            <div class="col">
                                <p><strong><?php _e('Product Price', 'smartpay'); ?></strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['product_price'] ?? '', $payment->currency); ?>
                                </p>
                            </div>

                            <!-- If payment has variation -->
                            <?php if (isset($payment_data['variation_id'])) : ?>

                            <?php $variation = smartpay_get_product_variation($payment_data['variation_id']); ?>

                            <?php if ($variation->parent != $product->ID) : ?>

                            <div class="col">
                                <p class="text-center text-danger">
                                    <strong><?php _e('Product variation not associated with this product!', 'smartpay'); ?></strong>
                                </p>
                            </div>

                            <?php else : ?>

                            <div class="col">
                                <p><strong><?php _e('Product Variation', 'smartpay'); ?></strong></p>
                                <p><?php echo $variation->name ?? ''; ?></p>
                            </div>

                            <div class="col">
                                <p><strong><?php _e('Additional Amount', 'smartpay'); ?></strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['additional_amount'] ?? '', $payment->currency); ?>
                                </p>
                            </div>

                            <?php endif; ?>

                            <?php endif; ?>

                            <div class="col">
                                <p><strong><?php _e('Total Amount', 'smartpay'); ?></strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['total_amount'] ?? '', $payment->currency); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <!-- // Form payment -->
                            <?php elseif ('form_payment' == $payment->payment_type) : ?>

                            <?php $form = smartpay_get_form($payment_data['form_id']); ?>

                            <!-- // If form not found -->
                            <?php if (!is_object($form) || !$form->ID) : ?>
                            <div class="col">
                                <p class="text-center text-danger"><strong><?php _e('Form not found!', 'smartpay'); ?></strong></p>
                            </div>

                            <!-- // Show form data -->
                            <?php else : ?>
                            <div class="col">
                                <p><strong><?php _e('Form', 'smartpay'); ?></strong></p>
                                <p>
                                    <strong><?php echo '#' . $payment_data['form_id'] ?? ''; ?></strong> &nbsp;
                                    <span><?php echo $form->title ?? ''; ?></span>
                                </p>
                            </div>

                            <div class="col">
                                <p><strong><?php _e('Total Amount', 'smartpay'); ?></strong></p>
                                <p><?php echo smartpay_amount_format($payment_data['total_amount'] ?? '', $payment->currency); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <?php endif; ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="customer-details" class="postbox ">
                    <?php $customer = $payment->customer; ?>
                    <h2 class="hndle"><span><?php _e('Customer Details', 'smartpay'); ?></span></h2>
                    <div class="smartpay">
                        <div class="d-flex flex-lg-row flex-column py-2">
                            <?php if (!is_array($customer) || !count($customer)) : ?>

                            <div class="col">
                                <p class="text-center text-danger"><strong><?php _e('Payment data not found!', 'smartpay'); ?></strong></p>
                            </div>

                            <?php else : ?>

                            <div class="col">
                                <p><strong><?php _e('First Name', 'smartpay'); ?></strong></p>
                                <p><?php echo $customer['first_name'] ?: '-'; ?></p>
                            </div>
                            <div class="col">
                                <p><strong><?php _e('Last Name', 'smartpay'); ?></strong></p>
                                <p><?php echo $customer['last_name'] ?: '-'; ?></p>
                            </div>
                            <div class="col">
                                <p><strong><?php _e('Email', 'smartpay'); ?></strong></p>
                                <p><?php echo $customer['email']; ?></p>
                            </div>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /post-body -->

        <?php endif; ?>

        <br class="clear">
    </div><!-- /poststuff -->
</div>