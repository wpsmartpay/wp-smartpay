;(function ($) {
    $('.smartpay-clear-debug-log').on('click', function (event) {
        event.preventDefault()

        $.ajax({
            url: debugLog.ajax_url,
            method: 'POST',
            data: { action: 'smartpay_debug_log_clear' },
        }).done(function (msg) {
            location.reload()
        })
    })
})(jQuery)
