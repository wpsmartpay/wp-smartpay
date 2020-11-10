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
    getProduct(id) {
        return {
            type: 'GET_PRODUCT',
            path: `${smartpay.restUrl}/v1/products/${id}`,
            id,
        }
    },
    setProduct(product) {
        return {
            type: 'SET_PRODUCT',
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
            case 'SET_PRODUCT':
                return {
                    ...state,
                    products: [
                        ...state.products.filter(
                            (product) => product.id !== action.id
                        ),
                        action.product,
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
        getProducts(state) {
            return state.products
        },
        getProduct(state, id) {
            if (!state.products) {
                return actions.getProduct(1)
            }
            return state.products.find((product) => product.id == id)
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
        GET_PRODUCT(action) {
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
        *getProduct(id) {
            const product = yield actions.getProduct(id)
            return actions.setProduct(product)
        },
    },
})
