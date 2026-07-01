import { __ } from '@wordpress/i18n'
import { Button, Card, CardBody, CardHeader, TextControl, CheckboxControl } from '@wordpress/components'
import { Plus as PlusIcon, X as CloseIcon } from 'react-feather'
import { Alert } from '../../components/Alert'
import { defaultAmount, geneateKey } from '../../utils/constant'

export const FormAmounts = ({ form, setFormData }) => {
    let { amounts } = form

    const addNewAmountRow = () => {
        setAmountsData([...amounts, { ...defaultAmount, key: geneateKey() }])
    }

    const removeAmountRow = (key) => {
        if (amounts.length <= 1) {
            Alert('Form must contain at least one amount', 'error')
            return
        }

        setAmountsData([...amounts.filter((amount) => key !== amount.key)])
    }

    const setAmount = (amount) => {
        setAmountsData([
            ...amounts.map((a) => {
                return amount.key === a.key ? amount : a
            }),
        ])
    }

    const setAmountsData = (amounts) => {
        setFormData({
            ...form,
            amounts,
        })
    }

    return (
        <Card className="smartpay-card">
            <CardHeader>
                <h2 className="smartpay-card__title">{__('Form Amounts', 'smartpay')}</h2>
            </CardHeader>
            <CardBody>
                <div className="smartpay-amounts">
                    {/* Form amounts */}
                    {window.SMARTPAY_FORM_HOOKS.applyFilters(
                        'smartpay.form.amount.section',
                        <>
                            {amounts.map((amount, index) => {
                                return (
                                    <div key={index} className="smartpay-amounts__row">
                                        <AmountRow
                                            amount={amount}
                                            setAmount={setAmount}
                                            removeAmountRow={removeAmountRow}
                                        />
                                    </div>
                                )
                            })}
                        </>,
                        form,
                        setFormData
                    )}
                    <div className="smartpay-amounts__add">
                        <Button
                            variant="secondary"
                            onClick={addNewAmountRow}
                            icon={<PlusIcon size={16} />}
                        >
                            {__('Add New Amount', 'smartpay')}
                        </Button>
                    </div>
                </div>

                <div className="smartpay-amounts__custom">
                    <CustomAmount form={form} setFormData={setFormData} />
                </div>
            </CardBody>
        </Card>
    )
}

const AmountRow = ({ rowIndex, amount, setAmount, removeAmountRow }) => {
    return (
        <Card className="smartpay-amount-row" key={rowIndex}>
            <CardBody className="smartpay-amount-row__body">
                <div className="smartpay-amount-row__fields">
                    <div className="smartpay-amount-row__label">
                        <TextControl
                            value={amount.label}
                            onChange={(value) => {
                                setAmount({ ...amount, label: value })
                            }}
                            placeholder={__('Label', 'smartpay')}
                            __nextHasNoMarginBottom
                        />
                    </div>
                    <div className="smartpay-amount-row__amount">
                        <TextControl
                            value={amount.amount}
                            onChange={(value) => {
                                setAmount({ ...amount, amount: value })
                            }}
                            placeholder={__('Amount', 'smartpay')}
                            __nextHasNoMarginBottom
                        />
                    </div>
                    <Button
                        variant="tertiary"
                        isDestructive
                        onClick={() => {
                            removeAmountRow(amount.key)
                        }}
                        icon={<CloseIcon size={16} />}
                        label={__('Remove', 'smartpay')}
                    />
                </div>
            </CardBody>
        </Card>
    )
}

const CustomAmount = ({ form, setFormData }) => {
    const setSettingsData = (settings) => {
        setFormData({
            ...form,
            settings,
        })
    }

    return (
        <Card className="smartpay-custom-amount">
            <CardBody>
                <CheckboxControl
                    label={__('Allow custom amount', 'smartpay')}
                    checked={form.settings.allowCustomAmount}
                    onChange={(checked) => {
                        setSettingsData({
                            ...form.settings,
                            allowCustomAmount: checked,
                        })
                    }}
                    __nextHasNoMarginBottom
                />
                {form.settings.allowCustomAmount && (
                    <div className="smartpay-custom-amount__label-field">
                        <TextControl
                            label={__('Custom amount label', 'smartpay')}
                            value={form.settings.customAmountLabel}
                            onChange={(value) => {
                                setSettingsData({
                                    ...form.settings,
                                    customAmountLabel: value,
                                })
                            }}
                            placeholder={__('Custom amount label', 'smartpay')}
                            __nextHasNoMarginBottom
                        />
                    </div>
                )}
            </CardBody>
        </Card>
    )
}
