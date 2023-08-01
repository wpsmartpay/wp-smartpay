import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    invoices: [],
}

const actions = {
    getInvoices() {
        return {
            type: 'GET_INVOICES',
            path: `${smartpay.restUrl}/v1/invoices`,
        }
    },
    setInvoices(invoices) {
        return {
            type: 'SET_INVOICES',
            invoices,
        }
    },
    getInvoice(id) {
        return {
            type: 'GET_INVOICE',
            path: `${smartpay.restUrl}/v1/invoices/${id}`,
            id,
        }
    },
    setInvoice(invoice) {
        return {
            type: 'SET_INVOICE',
            invoice,
        }
    },
    deleteInvoice(invoiceId) {
        return {
            type: 'DELETE_INVOICE',
            invoiceId,
        }
    },
}

registerStore('smartpay/invoices', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'GET_INVOICES':
                return {
                    ...state,
                    invoices: action.invoices,
                }
            case 'SET_INVOICES':
                return {
                    ...state,
                    invoices: [
                        action.invoice,
                        ...state.invoices.filter(
                            (invoice) => invoice.id !== action.invoice.id
                        ),
                    ],
                }
            case 'DELETE_INVOICES':
                return {
                    ...state,
                    invoices: [
                        ...state.invoices.filter(
                            (invoice) => invoice.id !== action.invoiceId
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
        getInvoices(state) {
            return state.invoices
        },
        getInvoice(state, id) {
            if (!state.invoices) {
                return actions.getInvoice(1)
            }
            return state.invoices.find((invoice) => invoice.id === id)
        },
    },

    controls: {
        GET_INVOICES(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
        GET_INVOICE(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getInvoices() {
            const response = yield actions.getInvoices()
            return actions.setInvoices(response?.invoices)
        },
        *getInvoice(id) {
            const response = yield actions.getInvoice(id)
            return actions.setInvoice(response?.invoice)
        },
    },
})
