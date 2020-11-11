import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    forms: [],
}

const actions = {
    getForms() {
        return {
            type: 'GET_FORMS',
            path: `${smartpay.restUrl}/v1/forms`,
        }
    },
    setForms(forms) {
        return {
            type: 'SET_FORMS',
            forms,
        }
    },
    getProduct(id) {
        return {
            type: 'GET_FORM',
            path: `${smartpay.restUrl}/v1/forms/${id}`,
            id,
        }
    },
    setProduct(form) {
        return {
            type: 'SET_FORM',
            form,
        }
    },
}

registerStore('smartpay/forms', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_FORMS':
                return {
                    ...state,
                    forms: action.forms,
                }
            case 'SET_FORM':
                return {
                    ...state,
                    forms: [
                        ...state.forms.filter(
                            (form) => form.id !== action.form.id
                        ),
                        action.form,
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
        getForms(state) {
            return state.forms
        },
        getProduct(state, id) {
            if (!state.forms) {
                return actions.getProduct(1)
            }
            return state.forms.find((form) => form.id == id)
        },
    },

    controls: {
        GET_FORMS(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
        GET_FORM(action) {
            return apiFetch({
                path: action.path,
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
        },
    },

    resolvers: {
        *getForms() {
            const forms = yield actions.getForms()
            return actions.setForms(forms)
        },
        *getProduct(id) {
            const form = yield actions.getProduct(id)
            return actions.setProduct(form)
        },
    },
})
