<?php
get_header();

while (have_posts()) : the_post();

    $product = smartpay_get_product($post->ID);

    if (isset($product)) {
        if (!$product->can_purchase()) {
            echo 'You can\'t buy this product';
        }

        $output = '<div class="smartpay">';
        $output .= '<div class="container py-3">';
        $output .= smartpay_view_render('shortcodes/product', ['product' => $product]);
        $output .= '</div>';
        $output .= '</div>';

        echo $output;
    }
endwhile;

get_footer();