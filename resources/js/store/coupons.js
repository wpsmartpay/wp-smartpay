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
    getCoupon(id) {
        return {
            type: 'GET_COUPON',
            path: `${smartpay.restUrl}/v1/coupons/${id}`,
            id,
        }
    },
    setCoupon(coupon) {
        return {
            type: 'SET_COUPON',
            coupon,
        }
    },
    updateCoupon(coupon) {
        return {
            type: 'UPDATE_COUPON',
            coupon,
        }
    },
    deleteCoupon(id) {
        return {
            type: 'DELETE_COUPON',
            id,
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
            case 'SET_COUPON':
                return {
                    ...state,
                    coupons: [action.coupon, ...state.coupons],
                }
            case 'UPDATE_COUPON':
                return {
                    ...state,
                    coupons: state.coupons.map((coupon) =>
                        coupon.id === action.coupon.id ? action.coupon : coupon
                    ),
                }

            case 'DELETE_COUPON':
                return {
                    ...state,
                    coupons: [
                        ...state.coupons.filter(
                            (coupon) => coupon.id !== action.id
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
        getCoupons(state) {
            return state.coupons
        },
        getCoupon(state, id) {
            if (!state.coupons) {
                return actions.getCoupon(id)
            }
            return state.coupons.find((coupon) => coupon.id === id)
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
        GET_COUPON(action) {
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
            const response = yield actions.getCoupons()
            return actions.setCoupons(response?.coupons)
        },
        *getCoupon(id) {
            const response = yield actions.getCoupon(id)
            return actions.setCoupon(response?.coupon)
        },
    },
})
