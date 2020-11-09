import { __ } from '@wordpress/i18n'
import { Container, Row, Col, Card, Navbar, Nav } from 'react-bootstrap'

export const Dashboard = () => {
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto"></div>
                    </div>
                </Container>

                {/* <Container className="">
                    <Navbar className="p-0 border-top">
                        <Nav className="p-0">
                            <Nav.Link href="#home">Home</Nav.Link>
                            <Nav.Link href="#link">Link</Nav.Link>
                        </Nav>
                    </Navbar>
                </Container> */}
            </div>

            {/* <Container>
                <Row>
                    <Col md={3}>
                        <Card className="p-3 bg-light text-center">
                            <h3 className="mb-2 m-0">2115</h3>
                            <p className="text-muted m-0">
                                {__('Total Products', 'smartpay')}
                            </p>
                        </Card>
                    </Col>
                </Row>
            </Container> */}
        </>
    )
}
