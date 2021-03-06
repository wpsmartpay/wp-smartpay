import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'
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
                            <tr className="bg-light">
                                <th className="w-5 text-left">
                                    {__('ID', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Customer', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Type', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Amount', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Date', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Status', 'smartpay')}
                                </th>
                                <th className="w-30 text-left">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {!payments.length && (
                                <tr>
                                    <td className="text-center" colSpan="7">
                                        {__('No payment found.', 'smartpay')}
                                    </td>
                                </tr>
                            )}

                            {payments.map((payment, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{payment.id}</td>
                                        <td>{payment.email}</td>
                                        <td>{payment.type}</td>
                                        <td>
                                            <span
                                                dangerouslySetInnerHTML={{
                                                    __html: `${smartpay.options.currencySymbol} ${payment.amount} `,
                                                }}
                                            ></span>
                                        </td>
                                        <td>
                                            {payment.completed_at ||
                                                payment.created_at}
                                        </td>
                                        <td>{payment.status}</td>
                                        <td>
                                            <Link
                                                className="btn-sm p-0 mr-2"
                                                to={`/payments/${payment.id}/edit`}
                                            >
                                                {__('View', 'smartpay')}
                                            </Link>
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
