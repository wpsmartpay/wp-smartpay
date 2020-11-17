import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button, Alert } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

import { DeletePayment } from '../../http/payment'

export const PaymentList = () => {
    useEffect(() => {
        dispatch('smartpay/payments').getPayments()
    }, [])

    const payments = useSelect((select) =>
        select('smartpay/payments').getPayments()
    )

    const [response, setResponse] = useState({})

    const deletePayment = (paymentId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                DeletePayment(paymentId).then((response) => {
                    dispatch('smartpay/payments').deletePayment(paymentId)
                    setResponse({
                        type: 'success',
                        message: __(response.message, 'smartpay'),
                    })
                })
            }
        })
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Payments', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                {response.message && (
                    <Alert className="mt-3" variant={response.type}>
                        {response.message}
                    </Alert>
                )}
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                            <tr className="text-white bg-dark">
                                <th className="w-5 text-left">
                                    <strong>{__('ID', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>
                                        {__('Customer', 'smartpay')}
                                    </strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Amount', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Type', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Status', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Date', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Actions', 'smartpay')}</strong>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {payments.map((payment, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{payment.id}</td>
                                        <td>
                                            {/* FIXME */}
                                            {payment.customer_id}
                                        </td>
                                        <td>{`${payment.currency}${payment.amount} `}</td>
                                        <td>{payment.type}</td>
                                        <td>{payment.status}</td>
                                        <td>{payment.completed_at}</td>
                                        <td>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deletePayment(payment.id)
                                                }
                                                variant="link"
                                            >
                                                {__('Delete', 'smartpay')}
                                            </Button>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </Table>
                </div>
            </Container>
        </>
    )
}
