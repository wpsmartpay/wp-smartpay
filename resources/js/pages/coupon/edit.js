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
import { UpdateCoupon } from '../../http/coupon'
import { createHooks } from '@wordpress/hooks'
import { useParams } from 'react-router-dom'
const { useSelect, select, dispatch } = wp.data

const restrictionElement = createHooks()

export const EditCoupon = () => {
    const { couponId } = useParams()

    const [coupon, setCoupon] = useState(null)

    const [response, setResponse] = useState({})

    useEffect(() => {
        const coupon = select('smartpay/coupons').getCoupon(couponId)
        setCoupon(coupon)
    }, [couponId, setCoupon])

    useEffect(() => {
        restrictionElement.addFilter(
            'restrictionElementContent',
            'smartpay',
            function ($content) {
                let contentElement = <p>{__('Upgrade to pro', 'smartpay')}</p>
                $content.push(contentElement)
                return $content
            }
        )
    }, [])

    const setCouponData = (event) => {
        setCoupon({ ...coupon, ...{ [event.target.name]: event.target.value } })
    }

    const Save = (couponId, updatedCoupon) => {
        UpdateCoupon(couponId, JSON.stringify(updatedCoupon)).then((response) =>
            setResponse({
                type: 'success',
                message: __(response.message, 'smartpay'),
            })
        )
        dispatch('smartpay/coupons').updateCoupon(updatedCoupon)
    }

    if (!coupon) {
        return <p>Coupon {couponId} not found</p>
    } else {
        console.log(coupon)
    }

    let restrictionElementOutput = restrictionElement.applyFilters(
        'restrictionElementContent',
        []
    )

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
                                onClick={() => Save(coupon.id, coupon)}
                            >
                                {__('Update', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
            <Container>
                <Row className="justify-content-center">
                    <Col xs={9}>
                        {response.message && (
                            <Alert className="mt-3" variant={response.type}>
                                {response.message}
                            </Alert>
                        )}
                    </Col>
                </Row>
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
                                                            onChange={
                                                                setCouponData
                                                            }
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
                                                            onChange={
                                                                setCouponData
                                                            }
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
                                            tabClassName="text-decoration-none"
                                            eventKey="usage-restriction"
                                            title="Usage Restriction"
                                        >
                                            {restrictionElementOutput}
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
