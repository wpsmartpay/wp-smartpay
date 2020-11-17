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
import { useReducer, useState } from '@wordpress/element'
const { dispatch } = wp.data
import { Save } from '../../http/coupon'

const initialState = {
    title: '',
    description: '',
    discount_type: 'fixed',
    discount_amount: '',
    expiry_date: '',
}

const reducer = (coupon, action) => {
    if (action.type == 'reset') {
        return initialState
    }

    const result = { ...coupon }
    result[action.type] = action.value
    return result
}

export const CreateCoupon = () => {
    const [coupon, setCoupon] = useReducer(reducer, initialState)
    const [response, setResponse] = useState({})

    const createCoupon = (event) => {
        event.preventDefault()
        Save(JSON.stringify(coupon)).then((response) => {
            dispatch('smartpay/coupons').setCoupon(response.coupon)
            setResponse({
                type: 'success',
                message: __(response.message, 'smartpay'),
            })
        })

        setCoupon({ type: 'reset' })
    }

    const setCouponData = (event) => {
        setCoupon({ type: event.target.name, value: event.target.value })
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
                                onClick={createCoupon}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                <Row className="justify-content-center">
                    <Col xs={9}>
                        {response.message && (
                            <Alert className="mb-3" variant={response.type}>
                                {response.message}
                            </Alert>
                        )}
                        <Form>
                            <Form.Group controlId="couponForm.title">
                                <Form.Control
                                    name="title"
                                    value={coupon.title}
                                    onChange={setCouponData}
                                    type="text"
                                    placeholder="Enter coupon code here"
                                />
                            </Form.Group>
                            <Form.Group controlId="couponForm.description">
                                <Form.Control
                                    name="description"
                                    value={coupon.description}
                                    onChange={setCouponData}
                                    as="textarea"
                                    rows={3}
                                    placeholder="Coupon description"
                                />
                            </Form.Group>
                            <div className="py-2">
                                <Tabs
                                    className="mb-3"
                                    fill
                                    defaultActiveKey="general"
                                >
                                    <Tab eventKey="general" title="General">
                                        <Row>
                                            <Col>
                                                <Form.Group controlId="couponForm.discountType">
                                                    <Form.Label className="mb-2 d-inline-block">
                                                        {__(
                                                            'Discount type',
                                                            'smartpay'
                                                        )}
                                                    </Form.Label>
                                                    <Form.Control
                                                        name="discount_type"
                                                        as="select"
                                                        value={
                                                            coupon.discount_type
                                                        }
                                                        onChange={setCouponData}
                                                    >
                                                        <option value="fixed">
                                                            {__(
                                                                'Fixed Amount',
                                                                'smartpay'
                                                            )}
                                                        </option>
                                                        <option value="percent">
                                                            {__(
                                                                'Percent',
                                                                'smartpay'
                                                            )}
                                                        </option>
                                                    </Form.Control>
                                                </Form.Group>
                                            </Col>
                                            <Col>
                                                <Form.Group controlId="couponForm.amount">
                                                    <Form.Label className="mb-2 d-inline-block">
                                                        {__(
                                                            'Coupon amount',
                                                            'smartpay'
                                                        )}
                                                    </Form.Label>
                                                    <Form.Control
                                                        name="discount_amount"
                                                        value={
                                                            coupon.discount_amount
                                                        }
                                                        onChange={setCouponData}
                                                        type="text"
                                                        placeholder="0"
                                                    />
                                                </Form.Group>
                                            </Col>
                                        </Row>
                                        <Form.Group controlId="couponForm.expiryDate">
                                            <Form.Label className="mb-2 d-inline-block">
                                                {__(
                                                    'Coupon expiry date',
                                                    'smartpay'
                                                )}
                                            </Form.Label>
                                            <Form.Control
                                                name="expiry_date"
                                                type="date"
                                                value={coupon.expiry_date}
                                                onChange={setCouponData}
                                            />
                                        </Form.Group>
                                    </Tab>
                                    <Tab
                                        eventKey="usageRestriction"
                                        title="Usage Restriction"
                                    >
                                        <div className="border rounded bg-light text-center p-5 d-flex flex-column align-items-center">
                                            <h3 className="mt-1">
                                                {__(
                                                    'Upgrade to pro',
                                                    'smartpay'
                                                )}
                                            </h3>
                                        </div>
                                    </Tab>
                                </Tabs>
                            </div>
                        </Form>
                    </Col>
                </Row>
            </Container>
        </>
    )
}
