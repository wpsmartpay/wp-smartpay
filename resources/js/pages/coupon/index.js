import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

export const CouponList = () => {
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
        </>
    )
}
