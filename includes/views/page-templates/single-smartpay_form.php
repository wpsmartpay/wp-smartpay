<?php
get_header();

while (have_posts()) : the_post();

    $form = smartpay_get_form($post->ID);

    if (isset($form)) {
        if (!$form->can_pay()) {
            echo 'You can\'t pay on this form.';
        }

        $output = '<div class="smartpay">';
        $output .= '<div class="container py-3">';
        $output .= smartpay_view_render('shortcodes/form', ['form' => $form]);
        $output .= '</div>';
        $output .= '</div>';

        echo $output;
    }
endwhile;

get_footer();