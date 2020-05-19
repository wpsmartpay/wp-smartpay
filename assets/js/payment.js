jQuery(document).ready(function ($) {

    $(document.body).on('click', '.smartpay button#pay_now', (e) => {

        e.preventDefault()

        let buttonText = $('button#pay_now').text()

        $('#pay_now').text('Processing...').attr('disabled', 'disabled')

        // let data = { action: 'smartpay_payment_process_action', data: getFormJSONData($('.smartpay #payment_form')) }

        let data = {
            action: 'smartpay_payment_process_action',
            data: getFormJSONData($('.smartpay #payment_form'))
        };

        jQuery.post(smartpay.ajax_url, data, response => {
            if (response) {
                $('#smartpay_payment_gateway_popup .modal-body').html(response)

            } else {
                console.log('Something wrong!')
            }

            $('#smartpay_payment_gateway_popup').modal('show')

            $('button#pay_now')
                .text(buttonText)
                .removeAttr('disabled');
        });

    });

    function getFormJSONData($form) {
        var serialize_array = $form.serializeArray();
        var indexed_array = {};

        $.map(serialize_array, function (n, i) {
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    }
});
