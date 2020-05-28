<?php

use SmartPay\Admin\Customers\Customer_Table;

$customers_table = new Customer_Table();
$customers_table->prepare_items();

?>


<div class="wrap">
    <h1><?php _e('Customers', 'smartpay'); ?></h1>
    <?php do_action('smartpay_customers_table_top'); ?>
    <form id="smartpay-customers-filter" method="get"
        action="<?php echo admin_url('edit.php?post_type=download&page=smartpay-customers'); ?>">
        <?php
        $customers_table->search_box(__('Search Customers', 'smartpay'), 'smartpay-customers');
        $customers_table->display();
        ?>
        <input type="hidden" name="post_type" value="download" />
        <input type="hidden" name="page" value="smartpay-customers" />
        <input type="hidden" name="view" value="customers" />
    </form>
    <?php do_action('smartpay_customers_table_bottom'); ?>
</div>