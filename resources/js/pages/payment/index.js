import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DataTable } from '../../components/data-table'
import { createColumns } from './columns'

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

    // Create columns with deletePayment function
    const columns = createColumns(deletePayment)


    return (
        <>
            <div className="text-black bg-white border-bottom">
                <Container>
                    <div className="d-flex align-items-center justify-content-between py-4">
                        <h2 className="text-black m-0">
                            {__('Payments', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container className="mt-4">
                <div className="bg-white p-4 rounded-lg shadow-sm">
                    <DataTable columns={columns} data={payments} />
                </div>
            </Container>
        </>
    )
}
