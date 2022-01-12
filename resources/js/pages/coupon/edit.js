import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { useEffect, useReducer } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Container, Form, Tabs, Tab, Row, Col, Button } from 'react-bootstrap'
import { Update } from '../../http/coupon'
const { useSelect, dispatch } = wp.data

const defaultCouponData = {
    title: '',
    body: '',
}

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const EditCoupon = () => {
    const { couponId } = useParams()

    const [coupon, setCouponData] = useReducer(reducer, defaultCouponData)

    const couponData = useSelect(
        (select) => select('smartpay/coupons').getCoupon(couponId),
        [couponId]
    )

    useEffect(() => {
        setCouponData(couponData)
    }, [couponId, couponData])

    const _setCouponData = (event) => {
        setCouponData({ [event.target.name]: event.target.value })
    }

    const Save = () => {
        Update(couponId, JSON.stringify(coupon)).then((response) => {
            dispatch('smartpay/coupons').updateCoupon(response.coupon)
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

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Edit Coupon', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                onClick={Save}
                            >
                                {__('Save', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                <Form>
                    <Form.Group controlId="couponForm.title">
                        <Form.Control
                            name="title"
                            value={coupon.title || ''}
                            onChange={_setCouponData}
                            type="text"
                            placeholder="Enter coupon code here"
                        />
                    </Form.Group>
                    <Form.Group controlId="couponForm.description">
                        <Form.Control
                            name="description"
                            value={coupon.description || ''}
                            onChange={_setCouponData}
                            as="textarea"
                            rows={3}
                            placeholder="Coupon description"
                        />
                    </Form.Group>
                    <div className="py-2">
                        <Tabs className="mb-3" fill defaultActiveKey="general">
                            <Tab
                                tabClassName="text-decoration-none"
                                eventKey="general"
                                title="General"
                            >
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
                                                    coupon.discount_type || ''
                                                }
                                                onChange={_setCouponData}
                                            >
                                                <option value="fixed">
                                                    {__(
                                                        'Fixed Amount',
                                                        'smartpay'
                                                    )}
                                                </option>
                                                <option value="percent">
                                                    {__(
                                                        'Percentage Amount',
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
                                                    coupon.discount_amount || ''
                                                }
                                                onChange={_setCouponData}
                                                type="text"
                                                placeholder="0"
                                            />
                                        </Form.Group>
                                    </Col>
                                </Row>
                                <Form.Group controlId="couponForm.expiryDate">
                                    <Form.Label className="mb-2 d-inline-block">
                                        {__('Coupon expiry date', 'smartpay')}
                                    </Form.Label>
                                    <Form.Control
                                        name="expiry_date"
                                        type="date"
                                        value={coupon.expiry_date || ''}
                                        onChange={_setCouponData}
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
                                            'Coming soon',
                                            'smartpay'
                                        )}
                                    </h3>
                                </div>
                            </Tab>
                        </Tabs>
                    </div>
                </Form>
            </Container>
        </>
    )
}
