import { __ } from '@wordpress/i18n'
import { Container, Link } from 'react-bootstrap'

export const Dashboard = () => {
    return (
        <div className="text-black bg-white border-bottom d-fixed">
            <Container>
                <div className="d-flex align-items-center justify-content-between">
                    <h2 className="text-black">
                        {__('Dashboard', 'smartpay')}
                    </h2>
                    <div className="ml-auto"></div>
                </div>
            </Container>
        </div>
    )
}
