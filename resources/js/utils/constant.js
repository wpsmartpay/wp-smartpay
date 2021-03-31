const ONE_TIME = 'One Time'
const SUBSCRIPTION = 'Subscription'

const productDefaultData = {
    title: '',
    covers: [],
    description: '',
    variations: [],
    base_price: '',
    sale_price: '',
    files: [],
    extra: {},
}

const variationDefaultData = {
    title: '',
    description: '',
    base_price: '',
    sale_price: '',
    files: [],
    key: '',
    extra: { billing_type: ONE_TIME },
}

export { ONE_TIME, SUBSCRIPTION, productDefaultData, variationDefaultData }
