<?php

use SmartPay\Modules\Frontend\Utilities\Downloader;

$additional_charge = $payment->data['additional_info']['additional_charge'] ?? 0;
$total_count = $payment->data['additional_info']['total_billing_cycle'] ?? 0;

if ($payment) : ?>

	<?php do_action('smartpay_before_payment_receipt', $payment); ?>

    <table>
		<?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

        <tr>
            <td><?php _e('Payment ID:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->id); ?></td>
        </tr>

        <tr>
            <td><?php _e( $payment->type == 'Product Purchase' ? 'Product Name' : 'Form Name:', 'smartpay') ?></td>
            <td>
                <a href="<?php echo smartpay_get_payment_product_or_form_name($payment->id)['preview'];?>" target="_blank">
					<?php echo esc_html(smartpay_get_payment_product_or_form_name($payment->id)['name']); ?>
                </a>
            </td>
            <!--            <td>--><?php //echo esc_html(smartpay_get_payment_product_or_form_name($payment->id)); ?><!--</td>-->
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
            <td>
				<?php
				echo smartpay_amount_format($payment->amount);
				?>
            </td>
        </tr>

		<?php if ($payment->data['additional_info'] &&  ($additional_charge > 0 || $total_count > 0)): ?>
            <tr>
                <td><?php _e('Subscription Info:', 'smartpay') ?></td>
                <td>
					<?php
					if ($additional_charge > 0) {
						echo ('Additional charge '. smartpay_amount_format($additional_charge). ', ');
					}
					if ($total_count > 0) {
						echo ('  Will be billed '.$total_count .' times.');
					}
					?>
                </td>
            </tr>
		<?php endif; ?>

        <tr>
            <td><?php _e('Payment gateway:', 'smartpay') ?></td>
            <td>
				<?php
				if (smartpay_payment_gateways()[$payment->gateway]['checkout_label'] == 'Free'){
					echo 'Free Purchase';
				}else{
					echo smartpay_payment_gateways()[$payment->gateway]['checkout_label'] ?? ucfirst($payment->gateway);
				}
				?>
            </td>
        </tr>
        <tr>
            <td><?php _e('Payment status:', 'smartpay') ?></td>
            <td><?php echo esc_html(ucfirst($payment->status)); ?></td>
        </tr>

		<?php if (strtolower($payment->status) == \SmartPay\Models\Payment::COMPLETED): ?>
			<?php if ($payment->type == 'Product Purchase'): ?>
				<?php $product = \SmartPay\Models\Product::find(intval($payment['data']['product_id'])) ?? null;
				$external_link = $product['settings']['externalLink'];
				?>
				<?php if ($product && $external_link && $external_link['allowExternalLink']): ?>
                    <tr>
                        <td><?php _e('Resource', 'smartpay') ?></td>
                        <td>
                            <a href="<?php echo $product['settings']['externalLink']['link']; ?>" target="_blank">
								<?php echo $product['settings']['externalLink']['label'] ?>
                            </a>
                        </td>
                    </tr>
				<?php endif; ?>

			<?php else: ?>
				<?php $form = \SmartPay\Models\Form::find(intval($payment['data']['form_id'])) ?? null;
				$external_link = $form['settings']['externalLink'];
				?>
				<?php if ($form && $external_link && $external_link['allowExternalLink']): ?>
                    <tr>
                        <td><?php _e('Resource', 'smartpay') ?></td>
                        <td>
                            <a href="<?php echo $form['settings']['externalLink']['link']; ?>" target="_blank">
								<?php echo $form['settings']['externalLink']['label'] ?>
                            </a>
                        </td>
                    </tr>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>


		<?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

    </table>

	<?php do_action('smartpay_after_payment_receipt', $payment); ?>

	<?php do_action('smartpay_payment_' . $payment->gateway . '_receipt', $payment); ?>

	<?php $productId = $payment->data['product_id'] ?? 0; ?>
	<?php $product = \SmartPay\Models\Product::with(['parent'])->find($productId); ?>
	<?php if (strtolower($payment->status) == \SmartPay\Models\Payment::COMPLETED): ?>
		<?php if ($product && count($product->files) > 0): ?>
            <!--    Do staff for download files-->
            <h3><?php echo __( 'Files', 'smartpay' ); ?></h3>
            <table>
                <thead>
                <th><?php _e('Name', 'smartpay') ?></th>
                <th><?php _e('Action', 'smartpay') ?></th>
                </thead>
                <tbody>
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
                </tbody>
            </table>
		<?php endif; ?>
	<?php endif; ?>
<?php

endif;
