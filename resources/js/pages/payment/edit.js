import {
    Container,
    Button,
    Card,
    Row,
    Col,
    InputGroup,
    Form,
} from 'react-bootstrap'
import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Update } from '../../http/payment'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { useEffect, useState } from '@wordpress/element'
const { useSelect, dispatch } = wp.data

export const EditPayment = () => {
    const { paymentId } = useParams()

    const [payment, setPaymentData] = useState({})
    const [paymentStatus, setPaymentStatus] = useState('pending')

    const paymentData = useSelect(
        (select) => select('smartpay/payments').getPayment(paymentId),
        [paymentId]
    )

    useEffect(() => {
        setPaymentData(paymentData)
        setPaymentStatus(paymentData?.status)
    }, [paymentId, paymentData])

    const _setPaymentStatus = (status) => {
        setPaymentStatus(status)
    }

    const Save = () => {
        Update(
            paymentId,
            JSON.stringify({ ...payment, status: paymentStatus })
        ).then((response) => {
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

            dispatch('smartpay/payments').updatePayment(response.payment)
        })
    }

    return (
        <>
            {payment && (
                <>
                    <div className="text-black bg-white border-bottom d-fixed">
                        <Container>
                            <div className="d-flex align-items-center justify-content-between">
                                <h2 className="text-black">
                                    {__('Payment Details', 'smartpay')}
                                </h2>
                                <div className="ml-auto">
                                    <InputGroup size="sm">
                                        <Form.Control
                                            name="status"
                                            as="select"
                                            value={paymentStatus}
                                            onChange={(e) =>
                                                _setPaymentStatus(
                                                    e.target.value
                                                )
                                            }
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
                                            <option value="completed">
                                                {__('Completed', 'smartpay')}
                                            </option>
                                            <option value="refunded">
                                                {__('Refunded', 'smartpay')}
                                            </option>
                                            <option value="failed">
                                                {__('Failed', 'smartpay')}
                                            </option>
                                            <option value="abandoned">
                                                {__('Abandoned', 'smartpay')}
                                            </option>
                                            <option value="revoked">
                                                {__('Revoked', 'smartpay')}
                                            </option>
                                            <option value="processing">
                                                {__('Processing', 'smartpay')}
                                            </option>
                                        </Form.Control>
                                        <InputGroup.Append>
                                            <Button
                                                className="btn btn-primary btn-sm text-decoration-none text-white"
                                                type="button"
                                                onClick={Save}
                                            >
                                                {__('Update', 'smartpay')}
                                            </Button>
                                        </InputGroup.Append>
                                    </InputGroup>
                                </div>
                            </div>
                        </Container>
                    </div>
                    <Container>
                        <Card>
                            <Card.Body>
                                <Row className="pb-3 align-items-center border-bottom">
                                    <Col>
                                        <div className="d-flex align-items-center">
                                            <h3 className="my-1 mr-3">
                                                ${payment?.amount || 0}
                                            </h3>
                                            <span
                                                className={
                                                    'btn px-2 py-0 pb-1 ' +
                                                    (payment.status ==
                                                    'Completed'
                                                        ? 'btn-success'
                                                        : 'btn-danger')
                                                }
                                            >
                                                {__(payment.status, 'smartpay')}
                                            </span>
                                        </div>
                                    </Col>
                                    <Col>
                                        <h3 className="text-primary m-0 text-right">
                                            {payment.type}
                                        </h3>
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
                                        <p>{payment?.transaction_id || '-'}</p>
                                    </Col>
                                </Row>

                                {payment.type == 'Product Purchase' && (
                                    <>
                                        <h3 className="text-black">
                                            {__('Product Details', 'smartpay')}
                                        </h3>
                                        <Row>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'Product',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>
                                                    {`# ${payment?.data?.product_id}` ||
                                                        '-'}
                                                </p>
                                            </Col>
                                            <Col>
                                                <p>
                                                    <strong>
                                                        {__(
                                                            'Product Price',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </p>
                                                <p>
                                                    {`${payment?.currency} ${payment?.data?.product_price}` ||
                                                        '-'}
                                                </p>
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
                                                <p>
                                                    {`${payment?.currency} ${payment?.data?.total_amount}` ||
                                                        '-'}
                                                </p>
                                            </Col>
                                        </Row>
                                    </>
                                )}

                                {payment.type == 'Form Payment' && (
                                    <>
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
                                                <p># {payment.data?.form_id}</p>
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
                                                <p>
                                                    {payment.data?.total_amount}
                                                </p>
                                            </Col>
                                        </Row>
                                    </>
                                )}

                                {payment?.customer && (
                                    <>
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
                                                <p>
                                                    {
                                                        payment?.customer
                                                            ?.first_name
                                                    }
                                                </p>
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
                                                <p>
                                                    {
                                                        payment?.customer
                                                            ?.last_name
                                                    }
                                                </p>
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
                                                <p>
                                                    {payment?.customer?.email}
                                                </p>
                                            </Col>
                                        </Row>
                                    </>
                                )}
                            </Card.Body>
                        </Card>
                        {/* TODO */}
                        {payment.extra?.form_data && (
                            <Card>
                                <Card.Body>
                                    <>
                                        <h3 className="text-black">
                                            {__('Form Data', 'smartpay')}
                                        </h3>
                                        <Row>
                                            <Col>
                                                {Object.keys(
                                                    payment.extra.form_data
                                                ).map((key, index) => (
                                                    <p key={index}>
                                                        {`${key} : ${payment.extra.form_data[key]}`}
                                                    </p>
                                                ))}
                                            </Col>
                                        </Row>
                                    </>
                                </Card.Body>
                            </Card>
                        )}
                    </Container>
                </>
            )}
        </>
    )
}
