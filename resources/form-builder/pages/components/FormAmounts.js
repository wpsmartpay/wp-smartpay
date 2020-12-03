import { __ } from '@wordpress/i18n'
import { Card, Form, Button } from 'react-bootstrap'
import { X as CloseIcon, Plus as PlusIcon } from 'react-feather'
import { useState } from '@wordpress/element'

const defaultAmount = {
    key: '',
    label: '',
    amount: '',
}

const geneateKey = () => Math.random().toString(36).substr(2, 9)

export const FormAmounts = () => {
    const [amounts, setAmounts] = useState([
        { ...defaultAmount, key: geneateKey() },
    ])

    const addNewAmountRow = () => {
        setAmounts([...amounts, { ...defaultAmount, key: geneateKey() }])
    }

    const removeAmountRow = (key) => {
        if (amounts.length <= 1) {
            return
        }

        setAmounts([...amounts.filter((amount) => key !== amount.key)])
    }

    const setAmount = (amount) => {
        setAmounts([
            ...amounts.map((a) => {
                return amount.key === a.key ? amount : a
            }),
        ])
    }

    return (
        <Card>
            <Card.Body>
                <h2 className="m-0">{__('Form Amounts', 'smartpay')}</h2>
                <div className="col-md-6 mx-auto py-4">
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
                            placeholder="Label"
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
                            placeholder="Amount"
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
