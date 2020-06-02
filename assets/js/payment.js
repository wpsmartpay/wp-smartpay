jQuery(document).ready(function ($) {

    $(document.body).on('click', '.smartpay button#pay_now', (e) => {

        e.preventDefault()

        // TODO: Change to class name
        let buttonText = $('button#pay_now').text()

        $('#pay_now').text('Processing...').attr('disabled', 'disabled')
        $('#smartpay_payment_checkout_popup .overlay').css('display', 'block');
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
                $('#smartpay_payment_checkout_popup .overlay').css('display', 'none');
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
    $('#single-payment-card .product-variations .list-group-item').on('click', function(e){
        $(this).parent().find('li.selected').removeClass('selected');
        $(this).addClass('selected');
    })
    /**
     * open payment checkout form
     */
    $('button#checkout_button').on('click', function (e) {
        e.preventDefault();
        getFormJSONData($('.smartpay #payment_form'));
        if(! $('body').hasClass('smartpay')){
            $('body').addClass('smartpay');
        };
        $('#smartpay_payment_checkout_popup').modal('show');
    })
    /**
     * open form checkout form
     */
    $('button#form_checkout_button').on('click', function (e) {
        e.preventDefault();
        var checkoutData = getFormJSONData($('.smartpay #checkout_form'));
        var selectedPriceAmount = checkoutData.smartpay_amount;
        $('#smartpay_form_checkout_popup #payment_form input[name="smartpay_amount"]').val(selectedPriceAmount);
        if(! $('body').hasClass('smartpay')){
            $('body').addClass('smartpay');
        };
        $('#smartpay_form_checkout_popup').modal('show');
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
