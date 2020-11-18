import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'
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
