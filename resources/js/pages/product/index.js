import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Nav, Form, Button } from 'react-bootstrap'

export const ProductList = () => {
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay | Product', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                to="/product/create"
                            >
                                Create
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>
        </>
    )
}
