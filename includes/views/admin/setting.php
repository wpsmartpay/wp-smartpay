<?php
/*
switch ($_GET['tab'] ?? null) {
    default:
    case 'general':
        $active_tab = 'general';
        break;

    case 'gateways':
        $active_tab = 'gateways';
        break;
}
?>

<div class="wrap">
    <h1>SmartPay Setting</h1>
    <h2 class="nav-tab-wrapper">
        <a href="?page=smartpay-setting&tab=general"
            class="nav-tab <?php if ($active_tab == 'general') echo 'nav-tab-active'; ?>">
            <?php _e('General', 'wp-smartpay'); ?>
        </a>
        <a href="?page=smartpay-setting&tab=gateways"
            class="nav-tab <?php if ($active_tab == 'gateways') echo 'nav-tab-active'; ?>">
            <?php _e('Payment gateway', 'wp-smartpay'); ?>
        </a>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields("header_section"); ?>

        <?php view('admin/setting/' . $active_tab) ?>

        <?php submit_button(); ?>
    </form>
</div>
<?php */