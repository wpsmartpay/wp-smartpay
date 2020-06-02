jQuery(function ($) {

    /** ============= Amounts ============= **/

    /** Add new amount **/
    $(document.body).on('click', '#add-more-amount', (e) => {
        $formAmounts = $('#form-amounts')

        amountIndex = ($('#form-amounts .amount-section').length + 1)

        let newAmount = `<div class="col-sm-2 amount-section mb-3">
            <div class="input-group">
                <input type="text" class="form-control amount" id="amounts[${amountIndex}]" name="amounts[${amountIndex}]" placeholder="${5.5 + amountIndex}">
                <div class="input-group-append">
                    <button class="btn btn-light border remove-amount" type="button"><i data-feather="x" width="17" height="17"></i></button>
                </div>
            </div>
        </div>`

        $formAmounts.append(newAmount)
        feather.replace()
    });

    /** Remove variation option **/
    $(document.body).on('click', '.remove-amount', (e) => {
        e.preventDefault()

        $(e.target).parents('.amount-section').remove()
    });
});