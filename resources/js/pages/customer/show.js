import Header from '@/components/Header'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import { Link, useParams } from 'react-router-dom'
import { Loading } from '../../components/Loading'
import CustomerStats from './customer-stats'
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
			<Header
				title={__('Customer Details', 'smartpay')}
				subtitle={__('View and manage customer information', 'smartpay')}
			/>

            <Container className="mt-4">
                {isLoading ? (
                    <Loading />
                ) : (
					<>
						<CustomerStats customer={customer} />

						<div className="bg-white p-4 rounded-lg">
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
					</>
                )}
            </Container>
        </>
    )
}
