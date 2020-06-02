jQuery(document).ready(function ($) {

    $(document.body).on('click', '.smartpay button#pay_now', (e) => {

        e.preventDefault()

        // TODO: Change to class name
        let buttonText = $('button#pay_now').text()

        $('#pay_now').text('Processing...').attr('disabled', 'disabled')
        $('#smartpay_payment_gateway_popup .modal-body').html(`
            <div class="text-center">
                    <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>`
        )
        $('#smartpay_payment_gateway_popup').modal('show')

        // let data = { action: 'smartpay_payment_process_action', data: getFormJSONData($('.smartpay #payment_form')) }

        let data = {
            action: 'smartpay_payment_process_action',
            data: getFormJSONData($('.smartpay #payment_form'))
        };

        jQuery.post(smartpay.ajax_url, data, response => {
            if (response) {
                $('#smartpay_payment_checkout_popup').modal('hide');
                setTimeout(() => {
                    $('#smartpay_payment_gateway_popup .modal-body').html(response)
                }, 500)

            } else {
                $('#smartpay_payment_gateway_popup .modal-body').html('Something wrong!')
                console.log('Something wrong!')
            }
            // console.log(buttonText);
            $('button#pay_now')
                .text(buttonText)
                .removeAttr('disabled');
        });

    });
    /**
     * add active class for variation price
     */
    $('#payment_form .product-variations .list-group-item').on('click', function (e) {
        $(this).parent().find('li.active span').removeClass('btn-outline-light').addClass('btn-outline-dark');
        $(this).parent().find('li.active').removeClass('active');
        $(this).find('span').removeClass('btn-outline-dark').addClass('btn-outline-light');
        $(this).addClass('active');
    })
    /**
     * open checkout form
     */
    $('button#checkout_button').on('click', function (e) {
        e.preventDefault();
        getFormJSONData($('.smartpay #payment_form'));
        $('#smartpay_payment_checkout_popup').modal('show')
    })

    function getFormJSONData($form) {
        var serialize_array = $form.serializeArray();

        var indexed_array = {};

        $.map(serialize_array, function (n, i) {
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    }
});
