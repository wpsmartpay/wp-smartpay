jQuery(function ($) {
    /** ============= Buy Product ============= **/

    /** Open order form **/
    $(document.body).on('click', '.smartpay button.smartpay-product-buy', (e) => {
        e.preventDefault();

        console.log('object');
        $('#myModal').modal('show')

    });

});