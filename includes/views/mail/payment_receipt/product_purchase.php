<?php $customer_name = $payment->customer['first_name'] . ' ' . $payment->customer['last_name']; ?>

<p><?php echo __('Dear', 'smartpay') . ' ' . $customer_name . ','; ?></p>
<p><?php _e('Thank you for your payment. Please click on the link(s) below to download your files.', 'smartpay'); ?></p>

<ol>
    <?php foreach([['name'=>'test']] as $download): ?>
    <li>
        <p><?php echo $download['name']; ?></p>
        <a href="#">Download</a>
    </li>
    <?php endforeach; ?>
</ol>