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
import { useReducer } from '@wordpress/element'

const initialState = {
    title: '',
    description: '',
    discounttype: 'fixed',
    amount: '',
    expirydate: '',
}

const reducer = (state, action) => {
    if (action.type == 'reset') {
        return initialState
    }

    const result = { ...state }
    result[action.type] = action.value
    return result
}

export const CreateCoupon = () => {
    const [state, dispatch] = useReducer(reducer, initialState)

    const couponCreateHandler = (event) => {
        event.preventDefault()

        fetch(`${smartpay.restUrl}/v1/coupons`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': smartpay.apiNonce,
            },
            body: JSON.stringify(state),
        }).then((response) => {
            if (response.status == 200) {
                let $alert = document.getElementById('coupon-alert')
                $alert.classList.remove('d-none')
            }
        })

        dispatch({ type: 'reset' })
    }

    const changeHandler = (event) => {
        dispatch({ type: event.target.name, value: event.target.value })
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
                                onClick={couponCreateHandler}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
            <Container>
                <Row className="justify-content-center">
                    <Col xs={9}>
                        <Alert
                            id="coupon-alert"
                            className="mt-5 d-none"
                            variant="success"
                        >
                            Coupon Added Successfully
                        </Alert>
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
                                        value={state.title}
                                        onChange={changeHandler}
                                        type="text"
                                        placeholder="Enter coupon code here"
                                    />
                                </Form.Group>
                                <Form.Group controlId="couponForm.description">
                                    <Form.Control
                                        name="description"
                                        value={state.description}
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
                                        defaultActiveKey="general"
                                    >
                                        <Tab
                                            eventKey="general"
                                            title="General"
                                        >
                                            <Row>
                                                <Col>
                                                    <Form.Group controlId="couponForm.discountType">
                                                        <Form.Label className="mb-2 d-inline-block">
                                                            Discount type
                                                        </Form.Label>
                                                        <Form.Control
                                                            name="discounttype"
                                                            as="select"
                                                            value={
                                                                state.discounttype
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
                                                            name="amount"
                                                            value={state.amount}
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
                                                    name="expirydate"
                                                    type="date"
                                                    value={state.expirydate}
                                                    onChange={changeHandler}
                                                />
                                            </Form.Group>
                                        </Tab>
                                        <Tab
                                            eventKey="usageRestriction"
                                            title="Usage Restriction"
                                        >
                                            <div className="border rounded bg-light text-center p-5 d-flex flex-column align-items-center">
                                                <h3 className="mt-1">
                                                    {__('Upgrade to pro', 'smartpay')}
                                                </h3>
                                            </div>
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
