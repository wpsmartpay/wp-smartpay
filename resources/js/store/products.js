import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    products: [],
}

const actions = {
    getProducts() {
        return {
            type: 'GET_PRODUCTS',
            path: `${smartpay.restUrl}/v1/products`,
        }
    },
    setProducts(products) {
        return {
            type: 'SET_PRODUCTS',
            products,
        }
    },
    addProduct(product) {
        return {
            type: 'ADD_PRODUCT',
            product,
        }
    },
}

registerStore('smartpay/products', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_PRODUCTS':
                return {
                    ...state,
                    products: action.products,
                }
            case 'ADD_PRODUCT':
                return {
                    ...state,
                    products: [...state.products, action.product],
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
        getProducts(state) {
            return state.products
        },
    },

    controls: {
        GET_PRODUCTS(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getProducts() {
            const products = yield actions.getProducts()
            return actions.setProducts(products)
        },
    },
})
