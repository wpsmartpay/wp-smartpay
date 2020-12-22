jQuery(($) => {
    // SmartPayFormValidation
    window.SmartPayFormValidator = function (data, rules) {
        /** Instance to self. **/
        const self = this
        this.data = data
        this.rules = rules

        self.validate = () => {
            return Object.entries(this.rules).reduce(
                (errors, [property, requirements]) => {
                    let itemErrors = []

                    // Check required validation
                    if (requirements.required) {
                        const errorMessage = this.validateRequiredMessage(
                            this.data[property]
                        )
                        if (errorMessage) itemErrors.push(errorMessage)
                    }

                    // Check email validation
                    if (requirements.email) {
                        const errorMessage = this.validateEmailMessage(
                            this.data[property]
                        )
                        if (errorMessage) itemErrors.push(errorMessage)
                    }

                    // Check length validation
                    if (requirements.length) {
                        const errorMessage = this.validateLengthMessage(
                            this.data[property],
                            requirements.length
                        )
                        if (errorMessage) itemErrors.push(errorMessage)
                    }

                    // Check value validation
                    if (requirements.value) {
                        const errorMessage = this.validateValueMessage(
                            this.data[property],
                            requirements.value
                        )
                        if (errorMessage) itemErrors.push(errorMessage)
                    }

                    if (itemErrors.length) {
                        errors[property] = itemErrors
                    }
                    return errors
                },
                {}
            )
        }

        self.validateLengthMessage = (value, length) => {
            if (value == null) return

            if (Array.isArray(length)) {
                if (value.length >= length[0] && value.length <= length[1])
                    return

                return `must be between ${length[0]} to ${length[1]} character`
            }

            if (value.length >= length) return

            return `must be ${length} or more characters`
        }

        self.validateRequiredMessage = (value) => {
            if (value) return

            return 'is required'
        }

        self.validateEmailMessage = (value) => {
            const emailFormat = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/

            if (emailFormat.test(value)) return

            return 'is not a valid email'
        }

        self.validateValueMessage = (value, compareValue) => {
            if (value === compareValue) return

            return `must be same as ${compareValue}`
        }
    }

    window.JSUcfirst = function (string) {
        return string.charAt(0).toUpperCase() + string.slice(1)
    }
})

import './frontend/payment/product'
import './frontend/payment/form'
import './frontend/shortcode.js'
