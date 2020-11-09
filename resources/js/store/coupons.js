const { apiFetch } = wp
const { registerStore } = wp.data

const DEFAULT_STATE = {
    coupons: [],
}

const actions = {
    setCoupon(coupon) {
        return {
            type: 'SET_COUPON',
            coupon,
        }
    },
}

registerStore('smartpay/coupons', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_COUPON':
                return {
                    ...state,
                    coupons: [...state.coupons, action.coupon],
                }
        }

        return state
    },

    actions,

    selectors: {
        getPrice(state, item) {
            const { prices, discountPercent } = state
            const price = prices[item]

            return price * (1 - 0.01 * discountPercent)
        },
    },

    controls: {
        FETCH_FROM_API(action) {
            return apiFetch({ path: action.path })
        },
    },

    resolvers: {
        *getPrice(item) {
            const path = '/wp/v2/prices/' + item
            const price = yield actions.fetchFromAPI(path)
            return actions.setPrice(item, price)
        },
    },
})
