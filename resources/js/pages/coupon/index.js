import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteCoupon } from '../../http/coupon'
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

export const CouponList = () => {
    const [coupons, setCoupons] = useState([])

    const couponList = useSelect((select) =>
        select('smartpay/coupons').getCoupons()
    )

    useEffect(() => {
        setCoupons(couponList)
    }, [couponList])

    const deleteCoupon = (couponId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                DeleteCoupon(couponId).then((response) => {
                    dispatch('smartpay/coupons').deleteCoupon(couponId)
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
                            {__('Coupons', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                to="/coupons/create"
                            >
                                {__('Create', 'smartpay')}
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>
            <Container className="mt-3">
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                        <tr className="bg-light">
                            <th className="w-25 text-center">
                                <strong>{__('Title', 'smartpay')}</strong>
                            </th>
                            <th className="text-center">
                                <strong>{__('Type', 'smartpay')}</strong>
                            </th>
                            <th className="text-center">
                                <strong>{__('Amount', 'smartpay')}</strong>
                            </th>
                            <th className="text-center">
                                <strong>{__('Expire At', 'smartpay')}</strong>
                            </th>
                            <th className="w-25 text-center">
                                {__('Actions', 'smartpay')}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        {!coupons.length && (
                            <tr>
                                <td className="text-center" colSpan="5">
                                    {__('No coupon found.', 'smartpay')}
                                </td>
                            </tr>
                        )}

                        {coupons.map((coupons) => {
                            return (
                                <tr key={coupons.id}>
                                    <td className='text-center'>{coupons.title || ''}</td>
                                    <td className='text-center'>{coupons.discount_type.toUpperCase() || ''}</td>
                                    <td className='text-center'>{coupons.discount_type === 'percent' ? `${coupons.discount_amount}%` : coupons.discount_amount}</td>
                                    <td className='text-center'>{coupons.expiry_date && coupons.expiry_date.startsWith('-') ? 'Never' : coupons.expiry_date}</td>
                                    <td className="text-center justify-content-center" style={{display: 'flex'}}>
                                        <Link
                                            className="btn-sm p-0 mr-2 text-decoration-none"
                                            to={`/coupons/${coupons.id}/edit`}
                                        >
                                            {__('Edit', 'smartpay')}
                                        </Link>
                                        <Button
                                            className="btn-sm p-0"
                                            onClick={() =>
                                                deleteCoupon(coupons.id)
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
