import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import { Container, Row, Col, Card, ListGroup } from 'react-bootstrap'
import { Report } from '../components/report/report'

export const Dashboard = () => {
    const [report, setReport] = useState([])

    useEffect(() => {
        apiFetch({
            path: `${smartpay.restUrl}/v1/reports`,
            headers: {
                'X-WP-Nonce': smartpay.apiNonce,
            },
        }).then((response) => {
            setReport(response)
        })
    }, [])

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container fluid>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto"></div>
                    </div>
                </Container>
            </div>
            <Container fluid>
                <Row className="mt-2">
                    <Col md={12}>
                        <div className="p-3">
                            <h2 className="m-0 mb-3">
                                {__('Monthly Report', 'smartpay')}
                            </h2>
                            <Card className="m-0 p-3">
                                <Report
                                    height="350"
                                    series={[
                                        {
                                            name: __(
                                                'Product Purchase',
                                                'smartpay'
                                            ),
                                            data: report.map(
                                                (data) => data.product_purchase
                                            ),
                                        },
                                        {
                                            name: __(
                                                'Form Payment',
                                                'smartpay'
                                            ),
                                            data: report.map(
                                                (data) => data.form_payment
                                            ),
                                        },
                                    ]}
                                    options={{
                                        chart: {
                                            type: 'bar',
                                            height: 350,
                                            stacked: true,
                                            toolbar: {
                                                show: true,
                                            },
                                            // zoom: {
                                            //     enabled: true,
                                            // },
                                        },
                                        plotOptions: {
                                            bar: {
                                                horizontal: false,
                                                columnWidth: '60%',
                                            },
                                        },
                                        dataLabels: {
                                            enabled: false,
                                        },
                                        // stroke: {
                                        //     show: true,
                                        //     width: 2,
                                        //     colors: ['transparent']
                                        // },
                                        xaxis: {
                                            categories: report.map(
                                                (data) => data.date
                                            ),
                                        },
                                        yaxis: {
                                            title: {
                                                text: 'Revenue',
                                            },
                                        },
                                        // responsive: [
                                        //     {
                                        //         breakpoint: 480,
                                        //         options: {
                                        //             legend: {
                                        //                 position: 'bottom',
                                        //                 offsetX: -10,
                                        //                 offsetY: 0,
                                        //             },
                                        //         },
                                        //     },
                                        // ],
                                        legend: {
                                            position: 'bottom',
                                            offsetY: 20,
                                        },
                                        fill: {
                                            opacity: 1,
                                        },
                                    }}
                                />
                            </Card>
                        </div>
                    </Col>
                    {/* <Col md={4}>
                        <div className="py-3">
                            <h2 className="m-0 mb-3">{__('Recent Payments')}</h2>
                            <ListGroup>
                                <ListGroup.Item>
                                    Link 1
                                </ListGroup.Item>
                                <ListGroup.Item>
                                    Link 2
                                </ListGroup.Item>
                            </ListGroup>
                        </div>
                    </Col> */}
                </Row>
            </Container>
        </>
    )
}
