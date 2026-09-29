<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="smartpay-layout">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
	echo do_shortcode( "[{$shortcode}]" );
	?>
</div>
<?php
get_footer();
