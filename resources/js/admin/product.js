import './product_metabox'

jQuery(function($) {
    $(document.body).on('click', '#save_product', async e => {
        e.preventDefault()

        // const formData = JSON.stringify(
        //     $('#create-product-form').serializeArray()
        // )

        let fields = []

        $('#create-product-form')
            .serializeArray()
            .map(item => {
                if (fields[item.name]) {
                    if (!fields[item.name].push) {
                        fields[item.name] = [fields[item.name]]
                    }
                    fields[item.name].push(item.value || '')
                } else {
                    fields[item.name] = item.value || ''
                }
            })

        console.log(fields)

        // console.log(formData)

        // const data = {
        //     title:
        // }

        // fetch(`${smartpay.restUrl}/v1/products`, {
        //     headers: {
        //         'X-WP-Nonce': smartpay.apiNonce,
        //     },
        // })
        //     .then(res => res.json())
        //     .then(data => {
        //         // window.location = `${smartpay.adminUrl}?page=smartpay-products&action=edit&id=1`
        //     })
        //     .catch(err => console.log(err))
    })
})
