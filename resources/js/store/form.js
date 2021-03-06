import apiFetch from '@wordpress/api-fetch'
const { registerStore } = wp.data

const DEFAULT_STATE = {
    isLoading: true,
    forms: [],
}

// DO NOT USE IT

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
    getForm(id) {
        return {
            type: 'GET_FORM',
            path: `${smartpay.restUrl}/v1/forms/${id}`,
            id,
        }
    },
    setForm(form) {
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
                        action.form,
                        ...state.forms.filter(
                            (form) => form.id !== action.form.id
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
        getForms(state) {
            return state.forms
        },
        getForm(state, id) {
            if (!state.forms) {
                return actions.getForm(1)
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
        *getForm(id) {
            const form = yield actions.getForm(id)
            return actions.setForm(form)
        },
    },
})
