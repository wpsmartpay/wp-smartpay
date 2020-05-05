// Admin: smartpay form metabox
jQuery(function ($) {
	$('#smartpay_form_metabox input[name="_form_amount_type"]').on(
		'change',
		toggle_amount_type
	);
	function toggle_amount_type() {
		let form_amount_type = $(
			'input[name="_form_amount_type"]:checked'
		).val();
		if ('multiple' == form_amount_type) {
			$('#_form_amount_container').hide();
			$('#_form_multiple_amount_container').show();
		} else {
			$('#_form_multiple_amount_container').hide();
			$('#_form_amount_container').show();
		}
	}
	toggle_amount_type();
});
