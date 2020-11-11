import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

const { useEffect } = wp.element
const { useSelect, dispatch } = wp.data

export const CustomerList = () => {
    useEffect(() => {
        dispatch('smartpay/customers').getCustomers()
    }, [])

    const customers = useSelect((select) =>
        select('smartpay/customers').getCustomers()
    )

    const deleteCustomer = () => {
        // FIXME
        console.log('Delete customer')
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Customers', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                            <tr className="text-white bg-dark">
                                <th className="w-50 text-left">
                                    <strong>{__('Name', 'smartpay')}</strong>
                                </th>
                                <th className="w-30 text-left">
                                    <strong>{__('Email', 'smartpay')}</strong>
                                </th>
                                <th className="w-20 text-right">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {customers.map((customer, index) => {
                                return (
                                    <tr key={index}>
                                        <td>
                                            {`${customer.first_name} ${customer.last_name}` ||
                                                ''}
                                        </td>
                                        <td>{customer.email || ''}</td>
                                        <td className="text-right">
                                            {/* <Link
                                                className="btn-sm p-0 mr-2"
                                                to={`/customers/${customer.id}`}
                                                disabled
                                            >
                                                {__('Show', 'smartpay')}
                                            </Link> */}
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteCustomer(customer)
                                                }
                                                variant="link"
                                                disabled
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
