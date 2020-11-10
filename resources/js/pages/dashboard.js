import { __ } from '@wordpress/i18n'
import { Container, Row, Col, Card, Navbar, Nav } from 'react-bootstrap'
import { Report } from '../components/report/report'

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
            <Container>
                <Row>
                    <Col md={8}>
                        <Card className="p-3 bg-light text-center">
                            <Report
                                series={[
                                    {
                                        name: 'PRODUCT A',
                                        data: [44, 55, 41, 67, 22, 43],
                                    },
                                    {
                                        name: 'PRODUCT B',
                                        data: [13, 23, 20, 8, 13, 27],
                                    },
                                ]}
                                options={{
                                    xaxis: {
                                        type: 'datetime',
                                        categories: [
                                            '01/01/2011 GMT',
                                            '01/02/2011 GMT',
                                            '01/03/2011 GMT',
                                            '01/04/2011 GMT',
                                            '01/05/2011 GMT',
                                            '01/06/2011 GMT',
                                        ],
                                    },
                                    chart: {
                                        type: 'bar',
                                        height: 350,
                                        stacked: true,
                                        toolbar: {
                                            show: true,
                                        },
                                        zoom: {
                                            enabled: true,
                                        },
                                    },
                                    responsive: [
                                        {
                                            breakpoint: 480,
                                            options: {
                                                legend: {
                                                    position: 'bottom',
                                                    offsetX: -10,
                                                    offsetY: 0,
                                                },
                                            },
                                        },
                                    ],
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                        },
                                    },
                                    legend: {
                                        position: 'right',
                                        offsetY: 40,
                                    },
                                    fill: {
                                        opacity: 1,
                                    },
                                }}
                            />
                        </Card>
                        {/* <Row>
                            <Col md={6}>
                                <Card className="p-3 bg-light text-center">
                                    <Report />
                                </Card>
                            </Col>
                            <Col md={6}>
                                <Card className="p-3 bg-light text-center">
                                    <Report />
                                </Card>
                            </Col>
                        </Row> */}
                    </Col>
                    <Col md={4}>
                        <Card className="p-3 bg-ligh">
                            <h2>History</h2>
                        </Card>
                    </Col>
                </Row>
            </Container>
        </>
    )
}
