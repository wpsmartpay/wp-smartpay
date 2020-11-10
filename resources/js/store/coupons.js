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
    updateCoupon(coupon) {
        return {
            type: 'UPDATE_COUPON',
            coupon,
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
            case 'UPDATE_COUPON':
                let updateCoupon
                state.coupons.map(function (couponItem) {
                    if (couponItem.id == action.coupon.id) {
                        updateCoupon = { ...action.coupon }
                    }
                })
                return {
                    ...state,
                    coupons: [...state.coupons, updateCoupon],
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
            return JSON.parse(
                JSON.stringify(state.coupons.find((c) => c.id === id))
            )
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
