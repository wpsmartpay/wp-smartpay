import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    payments: [],
}

const actions = {
    getPayments() {
        return {
            type: 'GET_PAYMENTS',
            path: `/smartpay/v1/payments`,
        }
    },
    setPayments(payments) {
        return {
            type: 'SET_PAYMENTS',
            payments,
        }
    },
    getPayment(id) {
        return {
            type: 'GET_PAYMENT',
            path: `/smartpay/v1/payments/${id}`,
            id,
        }
    },
    setPayment(payment) {
        return {
            type: 'SET_PAYMENT',
            payment,
        }
    },
}

registerStore('smartpay/payments', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_PAYMENTS':
                return {
                    ...state,
                    payments: action.payments,
                }
            case 'SET_PAYMENT':
                return {
                    ...state,
                    payments: [
                        ...state.payments.filter(
                            (payment) => payment.id !== action.payment.id
                        ),
                        action.payment,
                    ],
                }
            default:
                return state
        }
    },

    actions,

    selectors: {
        isLoading(state) {
            return state.isLoading
        },
        getPayments(state) {
            return state.payments
        },
        getPayment(state, id) {
            if (!state.payments) {
                return actions.getPayment(1)
            }
            return state.payments.find((payment) => payment.id == id)
        },
    },

    controls: {
        GET_PAYMENTS(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
        GET_PAYMENT(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getPayments() {
            const payments = yield actions.getPayments()
            return actions.setPayments(payments)
        },
        *getPayment(id) {
            const payment = yield actions.getPayment(id)
            return actions.setPayment(payment)
        },
    },
})
