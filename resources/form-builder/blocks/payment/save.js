import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <div className="d-flex justify-content-between">
                <div className="col">
                    {attributes.amounts.map((amount, index) => {
                        return (
                            <div
                                class="custom-control custom-radio"
                                key={index}
                            >
                                <input
                                    type="radio"
                                    id={`smartpay-amount-${index}`}
                                    name="smartpay_amount"
                                    class="custom-control-input"
                                    value={amount.value}
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
                </div>

                {attributes.showOptions && (
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
                )}
            </div>
            <div className="mt-3">
                <button type="submit" class="btn btn-success">
                    {__('Pay Now', 'smartpay')}
                </button>
            </div>
        </div>
    )
}
