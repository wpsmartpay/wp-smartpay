import {
    Container,
    Button,
    Card,
    Row,
    Col,
    InputGroup,
    FormControl,
    Form,
} from 'react-bootstrap'
import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { useEffect, useReducer, useState } from '@wordpress/element'
const { useSelect, dispatch } = wp.data

const defaultPaymentData = {
    title: '',
    body: '',
}

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const EditPayment = () => {
    const { paymentId } = useParams()

    const [payment, setPaymentData] = useReducer(reducer, defaultPaymentData)
    const [customer, setCustomerData] = useState({})

    const paymentData = useSelect(
        (select) => select('smartpay/payments').getPayment(paymentId),
        [paymentId]
    )
    const customerData = useSelect(
        (select) => {
            if (payment.customer) {
                return select('smartpay/customers').getCustomer(
                    payment.customer
                )
            }
        },
        [payment]
    )

    useEffect(() => {
        setPaymentData(paymentData)
    }, [paymentId, paymentData])

    useEffect(() => {
        setCustomerData(customerData)
    }, [payment, customerData])

    const _setPaymentData = (event) => {
        setPaymentData({ [event.target.name]: event.target.value })
    }

    const Save = () => {}
    return (
        <>
            {payment && (
                <>
                    <div className="text-black bg-white border-bottom d-fixed">
                        <Container>
                            <div className="d-flex align-items-center justify-content-between">
                                <h2 className="text-black">
                                    {__('Edit Payment', 'smartpay')}
                                </h2>
                                <div className="ml-auto">
                                    <Button
                                        type="button"
                                        className="btn btn-primary btn-sm text-decoration-none"
                                        onClick={Save}
                                    >
                                        {__('Update', 'smartpay')}
                                    </Button>
                                </div>
                            </div>
                        </Container>
                    </div>
                    <Container>
                        <Card>
                            <Card.Body>
                                <h3 className="text-black">
                                    {__('Details', 'smartpay')}
                                </h3>
                                <Row className="py-3 align-items-center border-bottom">
                                    <Col>
                                        <div className="d-flex align-items-center">
                                            <h3 className="my-1 mr-3">$10</h3>
                                            <span
                                                className={
                                                    'btn px-2 py-0 pb-1 ' +
                                                    (payment.status == 'publish'
                                                        ? 'btn-success'
                                                        : 'btn-danger')
                                                }
                                            >
                                                {__(payment.status, 'smartpay')}
                                            </span>
                                        </div>
                                    </Col>
                                    <Col>
                                        <h3 className="text-primary m-0 text-center">
                                            {payment.type == 'product_purchase'
                                                ? __(
                                                      'Product Purchase',
                                                      'smartpay'
                                                  )
                                                : __(
                                                      'Form Payment',
                                                      'smartpay'
                                                  )}
                                            {}
                                        </h3>
                                    </Col>
                                    <Col>
                                        <InputGroup>
                                            <Form.Control
                                                name="status"
                                                as="select"
                                                value={payment.status}
                                                onChange={_setPaymentData}
                                            >
                                                <option disabled>
                                                    {__(
                                                        'Select Status',
                                                        'smartpay'
                                                    )}
                                                </option>
                                                <option value="pending">
                                                    {__('Pending', 'smartpay')}
                                                </option>
                                                <option value="complete">
                                                    {__('Complete', 'smartpay')}
                                                </option>
                                                <option value="refunded">
                                                    {__('Refunded', 'smartpay')}
                                                </option>
                                                <option value="failed">
                                                    {__('Failed', 'smartpay')}
                                                </option>
                                                <option value="abandoned">
                                                    {__(
                                                        'Abandoned',
                                                        'smartpay'
                                                    )}
                                                </option>
                                                <option value="revoked">
                                                    {__('Revoked', 'smartpay')}
                                                </option>
                                                <option value="processing">
                                                    {__(
                                                        'Processing',
                                                        'smartpay'
                                                    )}
                                                </option>
                                            </Form.Control>
                                            <InputGroup.Append>
                                                <Button
                                                    class="btn btn-outline-primary"
                                                    type="button"
                                                >
                                                    {__('Save', 'smartpay')}
                                                </Button>
                                            </InputGroup.Append>
                                        </InputGroup>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col>
                                        <p>
                                            <strong>
                                                {__('Date', 'smartpay')}
                                            </strong>
                                        </p>
                                        <p>{payment.created_at}</p>
                                    </Col>
                                    <Col>
                                        <p>
                                            <strong>
                                                {__('Customer', 'smartpay')}
                                            </strong>
                                        </p>
                                        <p>{payment.email}</p>
                                    </Col>
                                    <Col>
                                        <p>
                                            <strong>
                                                {__(
                                                    'Payment Method',
                                                    'smartpay'
                                                )}
                                            </strong>
                                        </p>
                                        <p>{payment.gateway}</p>
                                    </Col>
                                    <Col>
                                        <p>
                                            <strong>
                                                {__(
                                                    'Transaction ID',
                                                    'smartpay'
                                                )}
                                            </strong>
                                        </p>
                                        <p>{payment.transaction_id}</p>
                                    </Col>
                                </Row>
                            </Card.Body>
                        </Card>
                        {payment.type == 'form_payment' && (
                            <>
                                <Card>
                                    <Card.Body>
                                        <h3 className="text-black">
                                            {__(
                                                'Form Payment Details',
                                                'smartpay'
                                            )}
                                        </h3>
                                        <Row>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__('Form', 'smartpay')}
                                                    </strong>
                                                </p>
                                                <p># {payment.data}</p>
                                            </Col>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'Total Amount',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>$10</p>
                                            </Col>
                                        </Row>
                                    </Card.Body>
                                </Card>
                            </>
                        )}
                        {customer && (
                            <>
                                <Card>
                                    <Card.Body>
                                        <h3 className="text-black">
                                            {__('Customer Details', 'smartpay')}
                                        </h3>
                                        <Row>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'First Name',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>{customer.first_name}</p>
                                            </Col>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'Last Name',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>{customer.last_name}</p>
                                            </Col>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'Email',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>{customer.email}</p>
                                            </Col>
                                        </Row>
                                    </Card.Body>
                                </Card>
                            </>
                        )}
                    </Container>
                </>
            )}
        </>
    )
}
