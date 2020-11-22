import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    customers: [],
}

const actions = {
    getCustomers() {
        return {
            type: 'GET_CUSTOMERS',
            path: `${smartpay.restUrl}/v1/customers`,
        }
    },
    setCustomers(customers) {
        return {
            type: 'SET_CUSTOMERS',
            customers,
        }
    },
    getCustomer(id) {
        return {
            type: 'GET_CUSTOMER',
            path: `${smartpay.restUrl}/v1/customers/${id}`,
            id,
        }
    },
    setCustomer(customer) {
        return {
            type: 'SET_CUSTOMER',
            customer,
        }
    },
    deleteCustomer(customerId) {
        return {
            type: 'DELETE_CUSTOMER',
            customerId,
        }
    },
}

registerStore('smartpay/customers', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_CUSTOMERS':
                return {
                    ...state,
                    customers: action.customers,
                }
            case 'SET_CUSTOMER':
                return {
                    ...state,
                    customers: [
                        action.customer,
                        ...state.customers.filter(
                            (customer) => customer.id !== action.customer.id
                        ),
                    ],
                }
            case 'DELETE_CUSTOMER':
                return {
                    ...state,
                    customers: [
                        ...state.customers.filter(
                            (customer) => customer.id !== action.customerId
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
        getCustomers(state) {
            return state.customers
        },
        getCustomer(state, id) {
            if (!state.customers) {
                return actions.getCustomer(1)
            }
            return state.customers.find((customer) => customer.id == id)
        },
    },

    controls: {
        GET_CUSTOMERS(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
        GET_CUSTOMER(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getCustomers() {
            const response = yield actions.getCustomers()
            return actions.setCustomers(response?.customers)
        },
        *getCustomer(id) {
            const response = yield actions.getCustomer(id)
            return actions.setCustomer(response?.customer)
        },
    },
})
