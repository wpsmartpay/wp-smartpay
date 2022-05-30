;(function ($) {
    $.fn.serializeJSON = function () {
        // don't do anything if we didn't get any elements
        if (this.length < 1) {
            return false
        }

        var data = {}
        var lookup = data //current reference of data
        var selector = ':input[type!="checkbox"][type!="radio"], input:checked'
        var parse = function () {
            // Ignore disabled elements
            if (this.disabled) {
                return
            }

            // data[a][b] becomes [ data, a, b ]
            var named = this.name.replace(/\[([^\]]+)?\]/g, ',$1').split(',')
            var cap = named.length - 1
            var $el = $(this)

            // Ensure that only elements with valid `name` properties will be serialized
            if (named[0]) {
                for (var i = 0; i < cap; i++) {
                    // move down the tree - create objects or array if necessary
                    lookup = lookup[named[i]] =
                        lookup[named[i]] ||
                        (named[i + 1] === '' || named[i + 1] === '0' ? [] : {})
                }

                // at the end, push or assign the value
                if (lookup.length !== undefined) {
                    lookup.push($el.val())
                } else {
                    lookup[named[cap]] = $el.val()
                }

                // assign the reference back to root
                lookup = data
            }
        }

        // first, check for elements passed into this function
        this.filter(selector).each(parse)

        // then parse possible child elements
        this.find(selector).each(parse)

        // return data
        return data
    }
})(jQuery)

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
                    // Check required when validation
                    if (requirements.requiredWhen) {
                        const errorMessage = this.validateRequiredWhenMessage(
                            this.data[property], requirements.requiredWhen
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
        self.validateRequiredWhenMessage = (value, [key, checkValue]) => {
            if(key && self.data[key] === checkValue){
                return self.validateRequiredMessage(value)
            }
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
