import Header from '@/components/Header'
import { __ } from '@wordpress/i18n'
import { Button, Container, Table } from 'react-bootstrap'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteCustomer } from '../../http/customer'
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

export const CustomerList = () => {
    const [customers, setCustomers] = useState([])

    const customerList = useSelect((select) =>
        select('smartpay/customers').getCustomers()
    )

    useEffect(() => {
        setCustomers(customerList)
    }, [customerList])

    const deleteCustomer = (customerId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                DeleteCustomer(customerId).then((response) => {
                    dispatch('smartpay/customers').deleteCustomer(customerId)
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
			<Header
				title={__('Customers', 'smartpay')}
				subtitle={__('Manage your customers here', 'smartpay')}
			/>

            <Container className="mt-3">
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                            <tr className="bg-light">
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
                            {!customers.length && (
                                <tr>
                                    <td className="text-center" colSpan="3">
                                        {__('No customer found.', 'smartpay')}
                                    </td>
                                </tr>
                            )}

                            {customers.map((customer, index) => {
                                return (
                                    <tr key={index}>
                                        <td>
                                            {`${customer.first_name} ${customer.last_name}` ||
                                                ''}
                                        </td>
                                        <td>{customer.email || ''}</td>
                                        <td className="text-right">
                                            <Link
                                                className="btn-sm p-0 mr-3 text-primary text-decoration-none"
                                                to={`/customers/${customer.id}`}
                                                disabled
                                            >
                                                {__('View Details', 'smartpay')}
                                            </Link>
                                            <Button
                                                className="btn-sm p-0 text-danger"
                                                onClick={() =>
                                                    deleteCustomer(customer.id)
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
