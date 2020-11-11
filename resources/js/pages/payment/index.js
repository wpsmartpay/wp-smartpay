import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

const { useEffect } = wp.element
const { useSelect, dispatch } = wp.data

export const PaymentList = () => {
    useEffect(() => {
        dispatch('smartpay/payments').getPayments()
    }, [])

    const payments = useSelect((select) =>
        select('smartpay/payments').getPayments()
    )

    const deletePayment = () => {
        // FIXME
        console.log('Delete payment')
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
                                        <td>{payment.amount}</td>
                                        <td>{payment.type}</td>
                                        <td>{payment.status}</td>
                                        <td>{payment.completed_at}</td>
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
