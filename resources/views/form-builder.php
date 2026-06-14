<?php defined( 'ABSPATH' ) || exit; ?>
<div class="smartpay" style="display: block !important;">
	<div class="wrap" style="display:none">
		<h2></h2>
	</div>
	<?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Partial escapes its own output.
	echo smartpay_view( 'admin.partials.deprecated-banner', array( 'feature' => __( 'Legacy Forms', 'smartpay' ) ) );
	?>
	<div id="smartpay-form"></div>
</div>
