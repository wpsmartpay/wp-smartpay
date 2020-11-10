import { __ } from '@wordpress/i18n'
import {
    Container,
    Form,
    Tabs,
    Tab,
    Row,
    Col,
    Button,
    Alert,
} from 'react-bootstrap'
import { useReducer, useEffect, useState } from '@wordpress/element'
import { useParams } from 'react-router-dom'
const { useSelect, select, dispatch } = wp.data

export const EditCoupon = () => {
    const { couponId } = useParams()

    const [coupon, setCoupon] = useState(null)

    useEffect(() => {
        const coupon = select('smartpay/coupons').getCoupon(couponId)
        setCoupon(coupon)
    }, [couponId, setCoupon])

    const changeHandler = (event) => {
        setCoupon({ ...coupon, ...{ [event.target.name]: event.target.value } })
    }

    const couponUpdateHandler = (event) => {
        event.preventDefault()
        dispatch('smartpay/coupons').updateCoupon(coupon)
    }

    if (!coupon) {
        return <p>Coupon {couponId} not found</p>
    } else {
        console.log(coupon)
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                onClick={couponUpdateHandler}
                            >
                                {__('Update', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
            <Container>
                <RR className="justify-content-center">
                    <Col xs={9}>
                        <Alert
                            id="coupon-alert"
                            className="mt-5 d-none"
                            variant="success"
                        >
                            Coupon Added Successfully
                        </Alert>
                    </Col>
                </RR>
            </Container>

            <div className="py-5">
                <Container>
                    <Row className="justify-content-center">
                        <Col xs={9}>
                            <Form>
                                <Form.Group controlId="couponForm.title">
                                    <Form.Control
                                        name="title"
                                        value={coupon.title}
                                        onChange={changeHandler}
                                        type="text"
                                        placeholder="Enter coupon code here"
                                    />
                                </Form.Group>
                                <Form.Group controlId="couponForm.description">
                                    <Form.Control
                                        name="description"
                                        value={coupon.description}
                                        onChange={changeHandler}
                                        as="textarea"
                                        rows={3}
                                        placeholder="Coupon description"
                                    />
                                </Form.Group>
                                <div className="py-2">
                                    <Tabs
                                        className="mb-3"
                                        fill
                                        defaultActiveKey="home"
                                    >
                                        <Tab
                                            tabClassName="text-decoration-none"
                                            eventKey="home"
                                            title="Home"
                                        >
                                            <Row>
                                                <Col>
                                                    <Form.Group controlId="couponForm.discountType">
                                                        <Form.Label className="mb-2 d-inline-block">
                                                            Discount type
                                                        </Form.Label>
                                                        <Form.Control
                                                            name="discount_type"
                                                            as="select"
                                                            value={
                                                                coupon.discount_type
                                                            }
                                                            onChange={
                                                                changeHandler
                                                            }
                                                        >
                                                            <option value="fixed">
                                                                Fixed Amount
                                                            </option>
                                                            <option value="percent">
                                                                Percent
                                                            </option>
                                                        </Form.Control>
                                                    </Form.Group>
                                                </Col>
                                                <Col>
                                                    <Form.Group controlId="couponForm.amount">
                                                        <Form.Label className="mb-2 d-inline-block">
                                                            Coupon amount
                                                        </Form.Label>
                                                        <Form.Control
                                                            name="discount_amount"
                                                            value={
                                                                coupon.discount_amount
                                                            }
                                                            onChange={
                                                                changeHandler
                                                            }
                                                            type="text"
                                                            placeholder="0"
                                                        />
                                                    </Form.Group>
                                                </Col>
                                            </Row>
                                            <Form.Group controlId="couponForm.expiryDate">
                                                <Form.Label className="mb-2 d-inline-block">
                                                    Coupon expiry date
                                                </Form.Label>
                                                <Form.Control
                                                    name="expiry_date"
                                                    type="date"
                                                    value={coupon.expiry_date}
                                                    onChange={changeHandler}
                                                />
                                            </Form.Group>
                                        </Tab>
                                        <Tab
                                            tabClassName="text-decoration-none"
                                            eventKey="usage-restriction"
                                            title="Usage Restriction"
                                        >
                                            <p>Upgrade to pro</p>
                                        </Tab>
                                    </Tabs>
                                </div>
                            </Form>
                        </Col>
                    </Row>
                </Container>
            </div>
        </>
    )
}
