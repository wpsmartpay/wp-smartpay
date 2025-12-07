import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import * as dayjs from 'dayjs'
import { Container } from 'react-bootstrap'
import { Link, useParams } from 'react-router-dom'
import { Loading } from '../../components/Loading'
import {
	PAYMENT_STATUS_COMPLETED,
	PAYMENT_STATUS_PENDING,
	PAYMENT_STATUS_REFUNDED,
} from '../../utils/constant'
const { useSelect, dispatch } = wp.data

export const ShowCustomer = () => {
    const { customerId } = useParams()
    const [customer, setCustomer] = useState({})
    const [isLoading, setIsLoading] = useState(true)

    const customerData = useSelect(
        (select) => select('smartpay/customers').getCustomer(customerId),
        [customerId]
    )

    useEffect(() => {
		console.log('Customer Data:', customerData);
		setCustomer(customerData)
		setIsLoading(false)
    }, [customerData])

    const filterPaymentsByStatus = (status = '') => {
        if (!customer?.payments?.data) {
            return []
        }

        return status
            ? customer?.payments?.data.filter((payment) => payment.status === status)
            : customer?.payments?.data
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Customer Details', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                {isLoading ? (
                    <Loading />
                ) : (
                    <div className="bg-white p-4 rounded-lg">
                        <div className="d-flex justify-content-between align-items-center">
                            <div className="d-flex align-items-center">
                                <img
                                    className="rounded-circle"
                                    style={{ height: '60px', width: '60px' }}
                                    src="http://2.gravatar.com/avatar/2537555324c913ede41a6366a12efd79?s=96"
                                    alt=""
                                />
                                <div className="ml-3">
                                    <h2 className="m-0 mb-1">
                                        {customer?.full_name}
                                    </h2>
                                    <p className="m-0 text-muted">
                                        {customer?.email}
                                    </p>
                                </div>
                            </div>
                            <div className="text-right">
                                <h3 className="my-0 mb-2">{`#${customer?.id}`}</h3>
                                <p className="m-0">
                                    {__('Customer since', 'smartpay')}{' '}
                                    <strong>
                                        {dayjs(customer?.created_at).format(
                                            'D MMM YYYY'
                                        )}
                                    </strong>
                                </p>
                            </div>
                        </div>

                        <div className="text-center my-4">
                            <div className="row">
                                <div className="col">
                                    <div className="p-3 bg-light rounded-lg">
                                        <h2 className="mt-0 mb-1">
                                            {customer?.payments?.length}
                                        </h2>
                                        <p className="text-muted m-0">
                                            {__('Total Payments', 'smartpay')}
                                        </p>
                                    </div>
                                </div>
                                <div className="col">
                                    <div className="p-3 bg-light rounded-lg">
                                        <h2 className="mt-0 mb-1">
                                            {
                                                filterPaymentsByStatus(
                                                    PAYMENT_STATUS_COMPLETED
                                                ).length
                                            }
                                        </h2>
                                        <p className="text-muted m-0">
                                            {__(
                                                'Completed Payments',
                                                'smartpay'
                                            )}
                                        </p>
                                    </div>
                                </div>
                                <div className="col">
                                    <div className="p-3 bg-light rounded-lg">
                                        <h2 className="mt-0 mb-1">
                                            {
                                                filterPaymentsByStatus(
                                                    PAYMENT_STATUS_PENDING
                                                ).length
                                            }
                                        </h2>
                                        <p className="text-muted m-0">
                                            {__('Pending Payments', 'smartpay')}
                                        </p>
                                    </div>
                                </div>
                                <div className="col">
                                    <div className="p-3 bg-light rounded-lg">
                                        <h2 className="mt-0 mb-1">
                                            {
                                                filterPaymentsByStatus(
                                                    PAYMENT_STATUS_REFUNDED
                                                ).length
                                            }
                                        </h2>
                                        <p className="text-muted m-0">
                                            {__(
                                                'Refunded Payments',
                                                'smartpay'
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-4">
                            <h3>{__('Recent Payments', 'smartpay')}</h3>
                            <div>
                                <table className="table text-center">
                                    <thead className="thead-light">
                                        <tr>
                                            <th scope="col">
                                                {__('ID', 'smartpay')}
                                            </th>
                                            <th scope="col">
                                                {__('Type', 'smartpay')}
                                            </th>
                                            <th scope="col">
                                                {__('Amount', 'smartpay')}
                                            </th>
                                            <th scope="col">
                                                {__('Status', 'smartpay')}
                                            </th>
                                            <th scope="col">
                                                {__('Date', 'smartpay')}
                                            </th>
                                            <th scope="col">
                                                {__('Action', 'smartpay')}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filterPaymentsByStatus().map(
                                            (payment) => {
                                                return (
                                                    <tr>
                                                        <td scope="row">
                                                            {payment.id}
                                                        </td>
                                                        <td scope="col">
                                                            {payment.type}
                                                        </td>
                                                        <td scope="col">
                                                            {`${payment.amount} ${payment.currency}`}
                                                        </td>
                                                        <td scope="col">
                                                            {payment.status}
                                                        </td>
                                                        <td scope="col">
                                                            {payment.completed_at ||
                                                                '-'}
                                                        </td>
                                                        <td scope="col">
                                                            <Link
                                                                className="text-primary text-sm text-decoration-none"
                                                                to={`/payments/${payment.id}/edit`}
                                                                disabled
                                                            >
                                                                {__(
                                                                    'Details',
                                                                    'smartpay'
                                                                )}
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                )
                                            }
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </Container>
        </>
    )
}
