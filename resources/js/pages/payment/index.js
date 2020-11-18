import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button, Alert } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

import { DeletePayment } from '../../http/payment'

export const PaymentList = () => {
    const [payments, setPayments] = useState([])

    const paymentList = useSelect((select) =>
        select('smartpay/payments').getPayments()
    )

    useEffect(() => {
        setPayments(paymentList)
    }, [paymentList])

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
