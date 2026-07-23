<?php
defined('ABSPATH') || exit;?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <?php wp_head(); ?>
	<style>
		.smartpay-layout {
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
		}
	</style>
</head>
<body class="smartpay-layout">

    <?php echo do_shortcode( "[{$shortcode}]" ); ?>

    <?php wp_footer(); ?>
</body>
</html>
