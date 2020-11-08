
import apiFetch from '@wordpress/api-fetch';
const { registerStore } = wp.data;

const DEFAULT_STATE = {
    isLoading: true,
    products: [],
};

const actions = {
    getProducts(path) {
        return {
            type: 'FETCH_FROM_API',
            path,
        };
    },
    addProduct(product) {
        return {
            type: 'ADD_PRODUCT',
            product,
        };
    },
};

export const PRODUCTS = registerStore('smartpay/products', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'FETCH_FROM_API':
                state.isLoading = true;

                apiFetch({
                    path: `${smartpay.restUrl}/v1/products`,
                    headers: {
                        'X-WP-Nonce': smartpay.apiNonce,
                    }
                }).then(response => {
                    state.isLoading = false;
                    return {
                        ...state,
                        products: response,
                    };
                });

            case 'ADD_PRODUCT':
                return {
                    ...state,
                    products: [
                        ...state.products,
                        action.product,
                    ]
                }
            default:
                return state;
        }
    },

    actions,

    selectors: {
        isLoading(state) {
            return state.isLoading
        },
        getProducts(state) {
            return state.products;
        },
    },

    controls: {
        FETCH_FROM_API(action) {
            return apiFetch({ path: action.path });
        },
    },

    resolvers: {
        * getProducts() {
            //
        },
    },
});
