<?php
// var_dump($product);
$product_price = $product->sale_price > -1 ? $product->sale_price : $product->base_price;
$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$chosen_gateway = isset($_REQUEST['gateway']) && smartpay_is_gateway_active($_REQUEST['gateway']) ? $_REQUEST['gateway'] : smartpay_get_default_gateway();
$has_payment_error = false;
$count = 0;

?>

<div class="smartpay smartpay-payment-shortcode-output">
    <div id="single-payment-card" class="card single-payment-card">
        <div class="bg-light border-bottom">
            <img src="<?php echo $product->image; ?>" class="card-img-top">
        </div>
        <div class="card-body p-5">
            <div class="row">
                <div class="col-7">
                    <h2 class="card-title mt-0 mb-2"><?php echo $product->title; ?></h2>
                    <div class="card-text"><?php echo $product->description; ?></div>
                </div>
                <div class="col product-price">
                <?php if ($product->has_variations()) : ?>
                    <div class="product-variations mb-2">
                        <ul class="list-group m-0">
                            <?php foreach ($product->variations as $variation) : $count++; ?>
                            <li class="list-group-item m-0 my-2 py-4 <?php echo (1==$count) ? 'selected': null ?>">
                                <?php //echo '<label for="smartpay-product-variation-' . esc_attr($variation['id']) . '">
                                    // <input type="radio" name="smartpay_product_variation_id" id="smartpay-product-variation-' . esc_attr($variation['id']) . '" value="' . esc_attr($variation['id']) . '" checked>';
                                        //echo esc_html($variation['name']) . ' - ' . smartpay_amount_format(($product_price + $variation['additional_amount']));
                                        //echo '</label>';
                                        ?>
                                <label for="smartpay-product-variation-<?php echo esc_attr($variation['id']); ?>" class="d-block m-0">
                                <input 
                                    class="d-none"
                                    type="radio" 
                                    name="smartpay_product_variation_id" 
                                    id="smartpay-product-variation-<?php echo esc_attr($variation['id']); ?>" 
                                    value="<?php echo esc_attr($variation['id']); ?>" checked >
                                <span class="price"><?php echo smartpay_amount_format(($product_price + $variation['additional_amount'])); ?></span>
                                <h5 class="m-0 mt-3"><?php echo esc_html(ucfirst($variation['name'])); ?></h5>
                                <p class="variation-short-description m-0"><?php echo esc_attr($variation['description']); ?></p>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else : ?>
                    <p class="price"><?php echo smartpay_amount_format($product_price); ?>
                    </p>
                    <?php endif; ?>

                    <button id="checkout_button" type="button" class="btn btn-success btn-block btn-lg">
                        <?php echo esc_html('Pay Now', 'wp-smartpay'); ?>
                    </button>
                </div> <!-- col -->
            </div> <!-- row -->
        </div> <!-- card-body -->
    </div> <!-- card -->
    <!-- Modal -->
    <!-- <div class="checkout-form-wrapper"> -->
    <div class="modal fade" id="smartpay_payment_checkout_popup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title m-0"><b>Checkout</b></p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h5 class="m-0 mb-3"><?php echo $product->title; ?></h5>
                        <p class="m-0"><?php echo esc_html('Enter your info to complete your puchase', 'wpsmartpay');?></p>
                    </div>
                    <div class="text-center">
                        <form id="payment_form" action="<?php echo $form_action; ?>" method="POST">

                            <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>
                            <input type="hidden" name="smartpay_action" value="smartpay_process_payment">
                            <input type="hidden" name="smartpay_purchase_type" value="product_purchase">
                            <input type="hidden" name="smartpay_product_id" value="<?php echo $product->get_ID() ?>">
                            
                            <ul class="list-unstyled list-group list-group-horizontal-sm m-0 my-3 justify-content-center">
                                <?php if (count($gateways)) : ?>

                                    <?php foreach ($gateways as $gateway_id => $gateway) : ?>
                                        <li class="list-group-item p-0 m-0 px-3 py-1">
                                            <?php echo '<label for="smartpay-gateway-' . esc_attr($gateway_id) . '">
                                                    <input type="radio" class="mr-2" name="smartpay_gateway" id="smartpay-gateway-' . esc_attr($gateway_id) . '" value="' . esc_attr($gateway_id) . '"' . checked($gateway_id, $chosen_gateway, false) . '>';
                                                    echo esc_html($gateway['checkout_label']);
                                                    echo '</label>';
                                                    ?>
                                        </li>
                                    <?php endforeach; ?>

                                <?php else : ?>
                                    <?php
                                        $has_payment_error = true;
                                        echo 'You must enable a payment gateway to proceed a payment.';
                                    ?>
                                <?php endif; ?>
                            </ul>
                            <div class="user-info">
                                <input type="text" placeholder="First name" class="mb-3 form-control" name="smartpay_first_name" value="Al-Amin" required>
                                <input type="text" placeholder="Last name" class="mb-3 form-control" name="smartpay_last_name" value="Firdows" >
                                <input type="email" placeholder="Email address" class="mb-3 form-control" name="smartpay_email" value="alaminfirdows@gmail.com" required>
                                <button id="pay_now" type="submit" class="btn btn-success btn-block btn-lg"
                                    <?php if ($has_payment_error) echo 'disabled'; ?>>
                                    <?php echo isset($payment_button_text) ?: 'Pay Now'; ?>
                                </button>
                            </div>

                        </form>
                        
                    </div>
                </div>
                <div class="overlay"></div>
            </div>
        </div>
    </div>
    <!-- </div> -->
    <div class="modal fade" id="smartpay_payment_gateway_popup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title m-0">Process payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-5">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- .smartpay -->