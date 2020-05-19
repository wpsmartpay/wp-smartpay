jQuery(document).ready(function ($) {

    $(document.body).on('click', '.smartpay button#pay_now', (e) => {

        e.preventDefault()

        let buttonText = $('button#pay_now').text()

        $('#pay_now').text('Processing...').attr('disabled', 'disabled')
        $('#smartpay_payment_gateway_popup').modal('show')

        // let data = { action: 'smartpay_payment_process_action', data: getFormJSONData($('.smartpay #payment_form')) }

        let data = {
            action: 'smartpay_payment_process_action',
            data: getFormJSONData($('.smartpay #payment_form'))
        };

        jQuery.post(smartpay.ajax_url, data, response => {
            if (response) {
                setTimeout(() => {
                    $('#smartpay_payment_gateway_popup .modal-body').html(response)
                }, 500)

            } else {
                $('#smartpay_payment_gateway_popup .modal-body').html('Something wrong!')
                console.log('Something wrong!')
            }


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
