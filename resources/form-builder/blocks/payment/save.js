import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <div className="row justify-content-between">
                <div className="col form-amounts">
                    {attributes.amounts.map((amount, index) => {
                        return (
                            <div
                                class="custom-control custom-radio form--fixed-amount"
                                key={index}
                            >
                                <input
                                    type="radio"
                                    id={`smartpay-amount-${index}`}
                                    name="_form_amount"
                                    class="custom-control-input"
                                    value={amount.value}
                                    checked={
                                        amount.value ===
                                        attributes.defaultAmount
                                    }
                                />
                                <label
                                    class="custom-control-label ml-4"
                                    for={`smartpay-amount-${index}`}
                                >
                                    {`${amount.label} - $${amount.value}`}
                                </label>
                            </div>
                        )
                    })}
                    <input
                        type="hidden"
                        class="form--custom-amount"
                        name="smartpay_amount"
                        value={attributes.defaultAmount}
                    />
                </div>

                {/* {attributes.showOptions && (
                    <div className="col">
                        {attributes.options.map((option, index) => {
                            return (
                                <div
                                    class="custom-control custom-checkbox"
                                    key={index}
                                >
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        class="smartpay_option"
                                        id={`smartpay-option-${index}`}
                                    />
                                    <label
                                        class="custom-control-label ml-4"
                                        for={`smartpay-option-${index}`}
                                    >
                                        {`${option.label} - $${option.value}`}
                                    </label>
                                </div>
                            )
                        })}
                    </div>
                )} */}
            </div>
            <div className="mt-3">
                <button type="submit" class="btn btn-success open-payment-form">
                    {__('Pay Now', 'smartpay')}
                </button>
            </div>
        </div>
    )
}
