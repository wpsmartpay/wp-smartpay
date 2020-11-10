import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    coupons: [],
}

const actions = {
    getCoupons() {
        return {
            type: 'GET_COUPONS',
            path: `${smartpay.restUrl}/v1/coupons`,
        }
    },
    setCoupons(coupons) {
        return {
            type: 'SET_COUPONS',
            coupons,
        }
    },
    addProduct(product) {
        return {
            type: 'ADD_PRODUCT',
            product,
        }
    },
}

registerStore('smartpay/coupons', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_COUPONS':
                return {
                    ...state,
                    coupons: action.coupons,
                }
            case 'ADD_PRODUCT':
                return {
                    ...state,
                    coupons: [...state.coupons, action.product],
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
        getCoupons(state) {
            return state.coupons
        },
    },

    controls: {
        GET_COUPONS(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getCoupons() {
            const coupons = yield actions.getCoupons()
            return actions.setCoupons(coupons)
        },
    },
})
