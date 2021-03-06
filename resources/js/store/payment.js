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
            path: `${smartpay.restUrl}/v1/payments`,
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
            path: `${smartpay.restUrl}/v1/payments/${id}`,
            id,
        }
    },
    setPayment(payment) {
        return {
            type: 'SET_PAYMENT',
            payment,
        }
    },
    updatePayment(payment) {
        return {
            type: 'UPDATE_PAYMENT',
            payment,
        }
    },
    deletePayment(paymentId) {
        return {
            type: 'DELETE_PAYMENT',
            paymentId,
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
                        action.payment,
                        ...state.payments.filter(
                            (payment) => payment.id !== action.payment.id
                        ),
                    ],
                }
            case 'UPDATE_PAYMENT':
                return {
                    ...state,
                    payments: state.payments.map((payment) =>
                        payment.id === action.payment.id
                            ? action.payment
                            : payment
                    ),
                }
            case 'DELETE_PAYMENT':
                return {
                    ...state,
                    payments: [
                        ...state.payments.filter(
                            (payment) => payment.id !== action.paymentId
                        ),
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
            const response = yield actions.getPayments()
            return actions.setPayments(response?.payments)
        },
        *getPayment(id) {
            const response = yield actions.getPayment(id)
            return actions.setPayment(response?.payment)
        },
    },
})
