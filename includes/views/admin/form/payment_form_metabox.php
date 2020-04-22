<?php
$_form_amount = get_post_meta($post->ID, '_form_amount', true);
?>
<form action="" method="POST">

    <?php wp_nonce_field('smartpay_form_metabox_nonce', 'smartpay_form_metabox_nonce'); ?>

    <table>
        <tbody class="simpay-panel-section">
            <tr class="">
                <th>
                    <label for="_form_amount">
                        <p>One-Time Amount</p>
                    </label>
                </th>
                <td>
                    <input type="tel" name="_form_amount" id="_form_amount" value="<?php echo esc_attr($_form_amount); ?>" placeholder="1.00">
                </td>
            </tr>
        </tbody>
    </table>
</form>