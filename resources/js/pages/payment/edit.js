import { Container, Button, Card, InputGroup, Form } from 'react-bootstrap'
import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Update } from '../../http/payment'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { useEffect, useState } from '@wordpress/element'
const { useSelect, dispatch } = wp.data

export const EditPayment = () => {
    const { paymentId } = useParams()

    const [payment, setPaymentData] = useState({})
    const [paymentStatus, setPaymentStatus] = useState('pending')

    const paymentData = useSelect(
        (select) => select('smartpay/payments').getPayment(paymentId),
        [paymentId]
    )

    useEffect(() => {
        setPaymentData(paymentData)
        setPaymentStatus(paymentData?.status)
    }, [paymentId, paymentData])

    const _setPaymentStatus = (status) => {
        setPaymentStatus(status)
    }

    const Save = () => {
        Update(
            paymentId,
            JSON.stringify({ ...payment, status: paymentStatus })
        ).then((response) => {
            Swal.fire({
                toast: true,
                icon: 'success',
                title: __(response.message, 'smartpay'),
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                showClass: {
                    popup: 'swal2-noanimation',
                },
                hideClass: {
                    popup: '',
                },
            })

            dispatch('smartpay/payments').updatePayment(response.payment)
        })
    }

    return (
        <>
            {payment && (
                <>
                    <div className="text-black bg-white border-bottom d-fixed">
                        <Container>
                            <div className="d-flex align-items-center justify-content-between">
                                <h2 className="text-black">
                                    {__('Payment Details', 'smartpay')}
                                </h2>
                                <div className="ml-auto">
                                    <InputGroup size="sm">
                                        <Form.Control
                                            name="status"
                                            as="select"
                                            value={paymentStatus.toLowerCase()}
                                            onChange={(e) =>
                                                _setPaymentStatus(
                                                    e.target.value
                                                )
                                            }
                                        >
                                            <option disabled>
                                                {__(
                                                    'Select Status',
                                                    'smartpay'
                                                )}
                                            </option>
                                            <option value="pending">
                                                {__('Pending', 'smartpay')}
                                            </option>
                                            <option value="completed">
                                                {__('Completed', 'smartpay')}
                                            </option>
                                            <option value="refunded">
                                                {__('Refunded', 'smartpay')}
                                            </option>
                                            <option value="failed">
                                                {__('Failed', 'smartpay')}
                                            </option>
                                            <option value="abandoned">
                                                {__('Abandoned', 'smartpay')}
                                            </option>
                                            <option value="revoked">
                                                {__('Revoked', 'smartpay')}
                                            </option>
                                            <option value="processing">
                                                {__('Processing', 'smartpay')}
                                            </option>
                                        </Form.Control>
                                        <InputGroup.Append>
                                            <Button
                                                className="btn btn-primary btn-sm text-decoration-none text-white"
                                                type="button"
                                                onClick={Save}
                                            >
                                                {__('Update', 'smartpay')}
                                            </Button>
                                        </InputGroup.Append>
                                    </InputGroup>
                                </div>
                            </div>
                        </Container>
                    </div>
                    <Container>
                        <Card className="mt-3">
                            <Card.Body>
                                <div className="d-flex pb-3 border-bottom justify-content-between align-items-center">
                                    <div className="d-flex align-items-center">
                                        <h3 className="my-1 mr-3">
                                            ${payment?.amount || 0}
                                        </h3>
                                        <span
                                            className={
                                                'px-2 py-1 text-white rounded ' +
                                                (payment.status == 'Completed'
                                                    ? 'bg-success'
                                                    : 'bg-danger')
                                            }
                                        >
                                            {__(payment.status, 'smartpay')}
                                        </span>
                                    </div>
                                    <h3 className="text-primary m-0 text-right">
                                        {payment.type}
                                    </h3>
                                </div>
                                <p>
                                    <strong>{__('Date', 'smartpay')}: </strong>
                                    <span>{payment.created_at}</span>
                                </p>
                                <p>
                                    <strong>
                                        {__('Customer', 'smartpay')}:{' '}
                                    </strong>
                                    <span>{payment.email}</span>
                                </p>
                                <p>
                                    <strong>
                                        {__('Payment Method', 'smartpay')}:{' '}
                                    </strong>
                                    <span>{payment.gateway}</span>
                                </p>
                                {payment?.transaction_id && (
                                    <p>
                                        <strong>
                                            {__('Transaction ID', 'smartpay')}:{' '}
                                        </strong>
                                        <span>
                                            {payment?.transaction_id || '-'}
                                        </span>
                                    </p>
                                )}

                                {payment.type == 'Product Purchase' && (
                                    <>
                                        <h3 className="text-black mt-4">
                                            {__('Product Details', 'smartpay')}
                                        </h3>
                                        <p>
                                            <strong>
                                                {__('Product', 'smartpay')}:{' '}
                                            </strong>
                                            <span>
                                                {`#${payment?.data?.product_id}` ||
                                                    '-'}
                                            </span>
                                        </p>
                                        <p>
                                            <strong>
                                                {__(
                                                    'Product Price',
                                                    'smartpay'
                                                )}
                                                :{' '}
                                            </strong>
                                            <span>
                                                {`${payment?.currency} ${payment?.data?.product_price}` ||
                                                    '-'}
                                            </span>
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Total Amount', 'smartpay')}
                                                :{' '}
                                            </strong>
                                            <span>
                                                {`${payment?.currency} ${payment?.data?.total_amount}` ||
                                                    '-'}
                                            </span>
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Price Type', 'smartpay')}
                                                :{' '}
                                            </strong>
                                            <span>
                                                {
                                                    payment.data && payment.data.price_type == 'onetime' ? 
                                                    __('One Time', 'smartpay') 
                                                    : __('Subscription', 'smartpay')
                                                }
                                            </span>
                                        </p>
                                    </>
                                )}

                                {payment.type == 'Form Payment' && (
                                    <>
                                        <h3 className="text-black mt-4">
                                            {__(
                                                'Form Payment Details',
                                                'smartpay'
                                            )}
                                        </h3>
                                        <p>
                                            <strong>
                                                {__('Form', 'smartpay')}:{' '}
                                            </strong>
                                            <span>
                                                #{payment.data?.form_id}
                                            </span>
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Total Amount', 'smartpay')}
                                                :{' '}
                                            </strong>
                                            <span>
                                                {payment.data?.total_amount}
                                            </span>
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Price Type', 'smartpay')}
                                                :{' '}
                                            </strong>
                                            <span>
                                                {
                                                    payment.data && payment.data.price_type == 'onetime' ? 
                                                    __('One Time', 'smartpay') 
                                                    : __('Subscription', 'smartpay')
                                                }
                                            </span>
                                        </p>
                                    </>
                                )}

                                {payment?.customer && (
                                    <>
                                        <h3 className="text-black mt-4">
                                            {__('Customer Details', 'smartpay')}
                                        </h3>
                                        <p>
                                            <strong>
                                                {__('First Name', 'smartpay')}:{' '}
                                            </strong>
                                            {payment?.customer?.first_name}
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Last Name', 'smartpay')}:{' '}
                                            </strong>
                                            {payment?.customer?.last_name}
                                        </p>
                                        <p>
                                            <strong>
                                                {__('Email', 'smartpay')}:{' '}
                                            </strong>
                                            {payment?.customer?.email}
                                        </p>
                                    </>
                                )}
                            </Card.Body>
                        </Card>
                        {payment.extra?.form_data && (
                            <div className="mt-3">
                                <DisplayFormData
                                    formData={payment.extra?.form_data}
                                    formFields={payment.extra?.form_fields}
                                />
                            </div>
                        )}
                    </Container>
                </>
            )}
        </>
    )
}

const DisplayFormData = ({ formData, formFields }) => {
    const build = (fields) => {
        if (!Array.isArray(fields)) {
            return
        }

        let tempFields = {}

        fields.forEach((item) => {
            const data = item[Object.keys(item)[0]]
            if (data.hasOwnProperty('attributes')) {
                item = data
            }

            const key = item['attributes']['name']
            if (item.hasOwnProperty('fields')) {
                tempFields[key] = build(item['fields'])
            } else {
                tempFields[key] = item['settings']['label']
            }
        })

        return tempFields
    }

    const renderFields = (labels, formData) => {
        return (
            <div key={Math.random().toString(36).substr(2, 11)}>
                {Object.keys(labels).map(function (key) {
                    if ('object' === typeof labels[key]) {
                        return renderFields(labels[key], formData[key])
                    } else {
                        return (
                            formData[key] && (
                                <p key={key}>
                                    <strong>{labels[key]}: </strong>
                                    {formData[key]}
                                </p>
                            )
                        )
                    }
                })}
            </div>
        )
    }

    return (
        <Card>
            <Card.Body>
                <h3 className="text-black">{__('Form Data', 'smartpay')}</h3>
                {renderFields(build(formFields), formData)}
            </Card.Body>
        </Card>
    )
}
