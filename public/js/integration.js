jQuery(function ($) {
    $(document.body).on(
        'change',
        '.smartpay-integrations .custom-control-input',
        (e) => {
            e.preventDefault()

            let action = 'deactivate'
            let namespace = $(e.target).data('namespace')
            let nonce = $('#smartpay_integrations_toggle_activation').val()

            if (e.target.checked) {
                action = 'activate'
            }

            let data = {
                action: 'toggle_integration_activation',
                payload: { action, namespace, nonce },
            }

            jQuery.post(smartpay.ajaxUrl, data, (response) => {
                if (response) {
                    $(e.target)
                        .parents('.actions')
                        .find('.integration-status')
                        .html(response)
                    window.location.reload();
                } else {
                    console.error('Something wrong!')
                }
            });
        }
    )
})
