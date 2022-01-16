<?php use SmartPay\Modules\Frontend\Utilities\Downloader;
if ($payment) : ?>

    <?php do_action('smartpay_before_payment_receipt', $payment); ?>

    <table>
        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

        <tr>
            <td><?php _e('Payment ID:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->id); ?></td>
        </tr>
        <tr>
            <td><?php _e('Name:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->customer->first_name . ' ' . $payment->customer->last_name); ?></td>
        </tr>
        <tr>
            <td><?php _e('Email:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->email); ?></td>
        </tr>
        <tr>
            <td><?php _e('Payment amount:', 'smartpay') ?></td>
            <td><?php echo smartpay_amount_format($payment->amount) ?></td>
        </tr>
        <tr>
            <td><?php _e('Payment gateway:', 'smartpay') ?></td>
            <td><?php echo smartpay_payment_gateways()[$payment->gateway]['checkout_label'] ?? ucfirst($payment->gateway) ?>
            </td>
        </tr>
        <tr>
            <td><?php _e('Payment status:', 'smartpay') ?></td>
            <td><?php echo esc_html(ucfirst($payment->status)); ?></td>
        </tr>

        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

    </table>

    <?php do_action('smartpay_after_payment_receipt', $payment); ?>

    <?php do_action('smartpay_payment_' . $payment->gateway . '_receipt', $payment); ?>

    <?php if (smartpay_get_option('product_download_files_settings_for_receipt') == true): ?>
        <!--    Do staff for download files-->
        <?php $productId = $payment->data['product_id'] ?? 0; ?>
        <?php $product = \SmartPay\Models\Product::with(['parent'])->find($productId); ?>
        <h3><?php echo __( 'Files', 'smartpay' ); ?></h3>
        <table>
            <thead>
            <th><?php _e('Name', 'smartpay') ?></th>
            <th><?php _e('Action', 'smartpay') ?></th>
            </thead>

            <tbody>
            <?php if ($payment->status != \SmartPay\Models\Payment::COMPLETED && $payment->status != 'Completed'): ?>
                <tr>
                    <td width="70%">
                        To get the files, you must complete your payment
                    </td>
                    <td></td>
                </tr>
            <?php else: ?>
                <?php if (count($product->files) > 0): ?>
                    <?php foreach ($product->files as $file) { ?>
                        <tr>
                            <td width="70%">
                                <?php echo $file['name']; ?>
                            </td>
                            <td>
                                <a href="<?php echo smartpay()->make(Downloader::class)->getDownloadUrl($file['id'], $payment->id, $product->id); ?>" class="btn btn-sm btn-primary btn--download"><?php _e('Download', 'smartpay'); ?></a>
                            </td>
                        </tr>

                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td width="70%">
                            No download files are available for this product
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php

endif;
