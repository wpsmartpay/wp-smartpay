import { __ } from '@wordpress/i18n'
import { Card, Form, Button } from 'react-bootstrap'
import { X as CloseIcon, Plus as PlusIcon } from 'react-feather'
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
        <Card>
            <Card.Body>
                <h2 className="m-0">{__('Form Amounts', 'smartpay')}</h2>
                <div className="col-md-8 mx-auto">
                    {/* Form amounts */}
                    {window.SMARTPAY_FORM_HOOKS.applyFilters(
                        'smartpay.form.amount.section',
                        <>
                            {amounts.map((amount, index) => {
                                return (
                                    <div key={index}>
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
                    <div className="mt-4">
                        <Button onClick={addNewAmountRow} size="sm">
                            <PlusIcon
                                size={18}
                                style={{ marginBottom: '-4px' }}
                                className="mr-2"
                            />
                            <span>{__('Add New Amount', 'smartpay')}</span>
                        </Button>
                    </div>
                </div>

                <div className="col-md-8 mx-auto">
                    <CustomAmount form={form} setFormData={setFormData} />
                </div>
            </Card.Body>
        </Card>
    )
}

const AmountRow = ({ rowIndex, amount, setAmount, removeAmountRow }) => {
    return (
        <Card className="mb-2 bg-light" key={rowIndex}>
            <div className="p-2">
                <div className="d-flex">
                    <div className="w-75 mr-2">
                        <Form.Control
                            size="sm"
                            type="text"
                            value={amount.label}
                            onChange={(e) => {
                                setAmount({ ...amount, label: e.target.value })
                            }}
                            placeholder={__('Label', 'smartpay')}
                        />
                    </div>
                    <div className="w-25 mr-2">
                        <Form.Control
                            size="sm"
                            type="text"
                            value={amount.amount}
                            onChange={(e) => {
                                setAmount({ ...amount, amount: e.target.value })
                            }}
                            placeholder={__('Amount', 'smartpay')}
                        />
                    </div>
                    <Button
                        size="sm"
                        variant="light"
                        onClick={() => {
                            removeAmountRow(amount.key)
                        }}
                    >
                        <CloseIcon size={18} style={{ marginBottom: '-4px' }} />
                    </Button>
                </div>
            </div>
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
        <Card className="my-3 bg-light">
            <div className="p-3">
                <div className="custom-control custom-checkbox py-1">
                    <input
                        type="checkbox"
                        className="custom-control-input"
                        id="allowCustomAmount"
                        value="true"
                        checked={form.settings.allowCustomAmount}
                        onChange={(e) => {
                            setSettingsData({
                                ...form.settings,
                                allowCustomAmount: e.target.checked,
                            })
                        }}
                    />
                    <label
                        className="custom-control-label pt-1"
                        htmlFor="allowCustomAmount"
                    >
                        {__('Allow custom amount', 'smartpay')}
                    </label>
                </div>
                {form.settings.allowCustomAmount && (
                    <div className="mt-3">
                        <div className="form-group mb-0">
                            <label>
                                {__('Custom amount label', 'smartpay')}
                            </label>
                            <Form.Control
                                className="mt-1"
                                size="sm"
                                type="text"
                                value={form.settings.customAmountLabel}
                                onChange={(e) => {
                                    setSettingsData({
                                        ...form.settings,
                                        customAmountLabel: e.target.value,
                                    })
                                }}
                                placeholder={__(
                                    'Custom amount label',
                                    'smartpay'
                                )}
                            />
                        </div>
                    </div>
                )}
            </div>
        </Card>
    )
}
