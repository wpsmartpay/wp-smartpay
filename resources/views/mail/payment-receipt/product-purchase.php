<?php

use SmartPay\Models\Product;

if (!property_exists($payment, 'customer')) {
    $payment->load('customer');
}

// $download = Process_Download::instance();

$productId     = absint($payment->data['product_id'] ?? 0);
$product       = Product::with('parent')->find($productId);
?>

<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <style>
      td,th,div,p,a,h1,h2,h3,h4,h5,h6 {font-family: "Segoe UI", sans-serif; mso-line-height-rule: exactly;}
    </style>
  <![endif]-->
    <title><?php _e('Thank you for your payment', 'smartpay'); ?></title>
    <style>
        .hover-no-underline:hover {
            text-decoration: none !important;
        }

        @media (max-width: 600px) {
            .sm-block {
                display: block !important;
            }

            .sm-text-xl {
                font-size: 20px !important;
            }

            .sm-text-32px {
                font-size: 32px !important;
            }

            .sm-text-40px {
                font-size: 40px !important;
            }

            .sm-leading-16 {
                line-height: 16px !important;
            }

            .sm-leading-24 {
                line-height: 24px !important;
            }

            .sm-leading-28 {
                line-height: 28px !important;
            }

            .sm-leading-32 {
                line-height: 32px !important;
            }

            .sm-leading-36 {
                line-height: 36px !important;
            }

            .sm-leading-40 {
                line-height: 40px !important;
            }

            .sm-leading-44 {
                line-height: 44px !important;
            }

            .sm-leading-64 {
                line-height: 64px !important;
            }

            .sm-p-0 {
                padding: 0 !important;
            }

            .sm-p-24 {
                padding: 24px !important;
            }

            .sm-pb-16 {
                padding-bottom: 16px !important;
            }

            .sm-pb-32 {
                padding-bottom: 32px !important;
            }

            .sm-text-left {
                text-align: left !important;
            }

            .sm-w-full {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #f2f2f7">
    <div role="article" aria-roledescription="email" aria-label="<?php _e('Thank you for your payment', 'smartpay'); ?>" lang="en">
        <table style="font-family: Arial, sans-serif; width: 100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center" style="background-color: #f2f2f7" bgcolor="#f2f2f7">
                    <table class="sm-w-full" style="width: 600px" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="sm-p-24" style="padding: 48px 20px">
                                <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td>
                                            <div style="text-align: center">
                                                <a href="<?php echo site_url(); ?>" style="text-decoration: none">
                                                    <?php echo get_bloginfo('name'); ?>
                                                </a>
                                            </div>
                                            <div class="sm-leading-64" style="line-height: 70px">&zwnj;</div>
                                            <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <td class="sm-p-0" style="padding-left: 64px; padding-right: 64px">
                                                        <h1 class="sm-text-40px sm-leading-44" style="font-size: 28px; line-height: 56px; margin: 0; text-align: center; color: #5744cb">
                                                            <?php _e('Thank you for your payment', 'smartpay'); ?>
                                                        </h1>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div class="sm-leading-16" style="line-height: 24px">&zwnj;</div>
                                            <p style="font-size: 16px; line-height: 24px; margin: 0; text-align: center; color: #a0a6b0">
                                                <?php echo __('Payment ', 'smartpay') . ' #' . $payment->id; ?>
                                            </p>
                                            <div class="sm-leading-40" style="line-height: 48px">&zwnj;</div>
                                            <div style="background-color: #d4d5d6; height: 1px; line-height: 1px">&nbsp;</div>
                                            <div class="sm-leading-16" style="line-height: 24px">&zwnj;</div>
                                            <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <th class="sm-w-full sm-block sm-pb-16" style="font-weight: 400; text-align: left; width: 50%" align="left">
                                                        <p class="sm-text-32px sm-leading-36" style="font-weight: 700; font-size: 28px; line-height: 44px; margin: 0px; color: #4f5a68">
                                                            <?php echo smartpay_amount_format($payment->amount); ?>
                                                        </p>
                                                    </th>
                                                    <th class="sm-w-full sm-block" style="font-weight: 400; vertical-align: center; width: 50%" valign="center">
                                                        <div class="sm-text-left" style="text-align: right">
                                                            <!-- FIXME: Set dynamic URL -->
                                                            <a href="<?php echo site_url() . '/smartpay-customer-dashboard/'; ?>" class="hover-no-underline" style="font-size: 16px; color: #986dff; text-decoration: underline"><?php _e('My Account', 'smartpay'); ?></a>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </table>
                                            <div class="sm-leading-40" style="line-height: 24px">&zwnj;</div>
                                            <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <td style="background-color: #ffffff; border-radius: 4px; padding: 32px 24px" bgcolor="#ffffff">
                                                        <h3 style="font-weight: 400; font-size: 16px; line-height: 24px; margin: 0; color: #4f5a68"><?php _e('Order details', 'smartpay'); ?></h3>
                                                        <div style="line-height: 24px">&zwnj;</div>
                                                        <table style="color: #4f5a68; width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                            <tr>
                                                                <td style="font-size: 16px; line-height: 24px; color: #a0a6b0; vertical-align: top; width: 50%" valign="top"><?php echo $product->formatted_title; ?></td>
                                                                <td style="font-weight: 700; font-size: 16px; line-height: 24px; text-align: right; vertical-align: top; width: 50%" align="right" valign="top"><?php echo smartpay_amount_format($payment->data['product_price']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" style="padding-top: 16px; padding-bottom: 16px">
                                                                    <div style="background-color: #d4d5d6; height: 1px; line-height: 1px">&nbsp;</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-weight: 700; font-size: 16px; line-height: 24px; vertical-align: top; width: 50%" valign="top"><?php _e('Total', 'smartpay'); ?></td>
                                                                <td style="font-weight: 700; font-size: 16px; line-height: 24px; text-align: right; vertical-align: top; width: 50%" align="right" valign="top"><?php echo smartpay_amount_format($payment->data['total_amount']); ?></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <?php if (count($product->files)) : ?>
                                                <div class="sm-leading-40" style="line-height: 24px">&zwnj;</div>
                                                <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                    <tr>
                                                        <td style="background-color: #ffffff; border-radius: 4px; padding: 32px 24px" bgcolor="#ffffff">
                                                            <h3 style="font-weight: 400; font-size: 16px; line-height: 24px; margin: 0; color: #4f5a68"><?php _e('Downloads', 'smartpay'); ?></h3>
                                                            <div style="line-height: 24px">&zwnj;</div>
                                                            <table style="color: #4f5a68; width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                                <?php foreach ($product->files as $file_index => $file) : ?>
                                                                    <tr>
                                                                        <td style="font-size: 16px; line-height: 24px; color: #a0a6b0; vertical-align: top; width: 50%" valign="top"><?php echo $file['name'] ?? 'Download Item'; ?></td>
                                                                        <!-- FIXME -->
                                                                        <td style="font-weight: 700; font-size: 16px; line-height: 24px; text-align: right; vertical-align: top; width: 50%" align="right" valign="top"><a href="<?php //echo $download->get_file_download_url($file_index, $payment->id, $productId); 
                                                                                                                                                                                                                                    ?>" class="btn btn-sm btn-primary mr-1"><?php _e('Download', 'smartpay'); ?></a></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" style="padding-top: 16px; padding-bottom: 16px">
                                                                            <div style="background-color: #d4d5d6; height: 1px; line-height: 1px">&nbsp;</div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>

                                            <div class="sm-leading-40" style="line-height: 64px">&zwnj;</div>
                                            <div style="background-color: #d4d5d6; height: 1px; line-height: 1px">&nbsp;</div>
                                            <div class="sm-leading-40" style="line-height: 48px">&zwnj;</div>
                                            <table style="width: 100%" cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <td class="sm-w-full sm-block sm-pb-32" style="font-weight: 400; text-align: left; vertical-align: top; width: 50%" align="left" valign="top">
                                                        <h4 style="font-size: 16px; line-height: 24px; margin: 0 0 8px; color: #4f5a68">Customer details</h4>
                                                        <p style="font-size: 16px; line-height: 24px; margin: 0; color: #4f5a68">
                                                            <?php echo __('Name:', 'smartpay') . ' ' . $payment->customer->full_name; ?>
                                                            <br>
                                                            <?php echo __('Email:', 'smartpay') . ' ' . $payment->customer->email; ?>
                                                            <br>
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div style="line-height: 64px">&zwnj;</div>
                                            <div style="background-color: #d4d5d6; height: 1px; line-height: 1px">&nbsp;</div>
                                            <div class="sm-leading-16" style="line-height: 32px">&zwnj;</div>
                                            <p style="font-size: 14px; line-height: 20px; margin: 0; color: #a0a6b0"><?php echo __('You get this email because you sign up or purchase someting at ', 'smartpay') . get_bloginfo('name'); ?></p>
                                            <div class="sm-leading-16" style="line-height: 32px">&zwnj;</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>