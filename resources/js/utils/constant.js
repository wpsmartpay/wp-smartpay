const ONE_TIME = 'One Time'
const SUBSCRIPTION = 'Subscription'

const PAYMENT_STATUS_PENDING = 'Pending'
const PAYMENT_STATUS_COMPLETED = 'Completed'
const PAYMENT_STATUS_REFUNDED = 'Refunded'
const PAYMENT_STATUS_FAILED = 'Failed'
const PAYMENT_STATUS_ABANDONED = 'Abandoned'
const PAYMENT_STATUS_REVOKED = 'Revoked'
const PAYMENT_STATUS_PROCESSING = 'Processing'

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

export {
    PAYMENT_STATUS_PENDING,
    PAYMENT_STATUS_COMPLETED,
    PAYMENT_STATUS_REFUNDED,
    PAYMENT_STATUS_FAILED,
    PAYMENT_STATUS_ABANDONED,
    PAYMENT_STATUS_REVOKED,
    PAYMENT_STATUS_PROCESSING,
    ONE_TIME,
    SUBSCRIPTION,
    productDefaultData,
    variationDefaultData,
}
