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
    updateProduct(product) {
        return {
            type: 'UPDATE_PRODUCT',
            product,
        }
    },
    deleteProduct(productId) {
        return {
            type: 'DELETE_PRODUCT',
            productId,
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
                        action.product,
                        ...state.products.filter(
                            (product) => product.id !== action.product.id
                        ),
                    ],
                }
            case 'UPDATE_PRODUCT':
                return {
                    ...state,
                    products: state.products.map((product) =>
                        product.id === action.product.id
                            ? action.product
                            : product
                    ),
                }

            case 'DELETE_PRODUCT':
                return {
                    ...state,
                    products: [
                        ...state.products.filter(
                            (product) => product.id !== action.productId
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
        getProducts(state) {
            return state.products
        },
        getProduct(state, id) {
            if (!state.products) {
                return actions.getProduct(id)
            }
            return state.products.find((product) => product.id === id)
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
            const response = yield actions.getProducts()
            return actions.setProducts(response?.products)
        },
        *getProduct(id) {
            const response = yield actions.getProduct(id)
            return actions.setProduct(response?.product)
        },
    },
})
