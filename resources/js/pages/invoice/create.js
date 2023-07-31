import {__} from '@wordpress/i18n'
import {Link} from 'react-router-dom'
import {Container, Table, Button, Form, Col, Card, Row} from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'

const {useEffect, useState} = wp.element
const {useSelect, dispatch} = wp.data
import {invoiceDefaultData} from "../../utils/constant";
import {useReducer} from "@wordpress/element";
import AsyncSelect from 'react-select'

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}


export const CreateInvoice = () => {

    const customerOptions = [
        {value: 'customer1', label: 'John Doe'},
        {value: 'customer2', label: 'Adam smith'},
        {value: 'customer3', label: 'Iris Warna'}
    ]

    const productOptions = [
        {value: 'product1', label: 'Product 1'},
        {value: 'product2', label: 'Product 2'},
        {value: 'product3', label: 'Product 3'}
    ]


    const [invoiceData, setInvoiceData] = useReducer(reducer, invoiceDefaultData)

    const _setInvoiceData = (event) => {
        setInvoiceData({[event.target.name]: event.target.value})
    }

    const _setSelectData = (customer) => {
        console.log(customer)
    }

    const _setProductData = (product) => {
        console.log(product)
    }

    const _setItemData = (item, event) => {
        setItemData(item, {
            [event.target.name]: event.target.value,
        })
    }

    const setItemData = (item, data) => {
        const items = invoiceData.items.map((v) =>
            v.key === item.key ? {...v, ...data} : v
        )

        setInvoiceData({items})
    }

    const setCustomData = (item, data) => {
        const customData = invoiceData.customData.map((v) =>
            v.key === item.key ? {...v, ...data} : v
        )

        setInvoiceData({customData})
    }

    const _setCustomData = (item, event) => {
        setCustomData(item, {
            [event.target.name]: event.target.value,
        })
    }

    const removeCustomData = (item) => {
        if (invoiceData.customData.length === 1) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: __(
                    'You can not remove the last item',
                    'smartpay'
                ),
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                showClass: {
                    popup: 'swal2-noanimation',
                },
                hideClass: {
                    popup: '',
                },
            });
            return
        }
        setInvoiceData({
            customData: invoiceData.customData.filter(
                (v) => v.key !== item.key
            ),
        })
    }

    const removeItem = (item) => {
        if (invoiceData.items.length === 1) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: __(
                    'You can not remove the last item',
                    'smartpay'
                ),
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                showClass: {
                    popup: 'swal2-noanimation',
                },
                hideClass: {
                    popup: '',
                },
            });
            return
        }
        setInvoiceData({
            items: invoiceData.items.filter(
                (v) => v.key !== item.key
            ),
        })
    }

    const addItem = () => {
        setInvoiceData({
            items: [
                ...invoiceData.items,
                {
                    ...invoiceDefaultData.items[0],
                    key: Date.now(),
                },
            ],
        })
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Create Invoice', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container>
                <Form className="my-3">
                    <Card className="mb-4">
                        <Card.Body className="bg-white">
                            <Row>
                                <Col md={12}>
                                    <Form.Group controlId="customerId">
                                        <Form.Label className="mb-2"><strong>Customer</strong></Form.Label>
                                        <AsyncSelect onChange={_setSelectData} options={customerOptions}/>
                                    </Form.Group>
                                </Col>
                            </Row>
                        </Card.Body>
                    </Card>

                    <Card className="mb-4">
                        <Card.Body className="bg-white">
                            {invoiceData.items.map((item) => {
                                return (
                                    <Row key={item.key}>
                                        <Col md={4}>
                                            <Form.Group controlId="productId">
                                                <Form.Label className="font-weight-bold">Product</Form.Label>
                                                <AsyncSelect className="mt-2" onChange={_setProductData}
                                                             options={productOptions}/>
                                            </Form.Group>
                                        </Col>

                                        <Col md={2}>
                                            <Form.Group controlId="qty">
                                                <Form.Label className="font-weight-bold">Qty</Form.Label>
                                                <Form.Control
                                                    className="mt-2"
                                                    type="number"
                                                    name="qty"
                                                    value={item.qty || ''}
                                                    onChange={_setItemData.bind(this, item)}
                                                />
                                            </Form.Group>
                                        </Col>

                                        <Col md={2}>
                                            <Form.Group controlId="unit_price">
                                                <Form.Label className="font-weight-bold">Unit Price</Form.Label>
                                                <Form.Control
                                                    className="mt-2"
                                                    type="number"
                                                    name="unit_price"
                                                    value={item.unitPrice || ''}
                                                    onChange={_setItemData.bind(this, item)}
                                                />
                                            </Form.Group>
                                        </Col>

                                        <Col md={3}>
                                            <Form.Group controlId="amount">
                                                <Form.Label className="font-weight-bold">Amount</Form.Label>
                                                <Form.Control
                                                    className="mt-2"
                                                    type="number"
                                                    name="amount"
                                                    value={item.amount || ''}
                                                    onChange={_setItemData.bind(this, item)}
                                                />
                                            </Form.Group>
                                        </Col>

                                        <Col md={1}>
                                            <Form.Group controlId="remove">
                                                <Form.Label className="font-weight-bold">Remove</Form.Label>
                                                <Button
                                                    className="mt-2"
                                                    variant="outline-danger"
                                                    onClick={() => removeItem(item)}
                                                >
                                                    x
                                                </Button>
                                            </Form.Group>
                                        </Col>
                                    </Row>
                                )
                            })}
                            <Row>
                                <Col md={12}>
                                    <Button
                                        variant="outline-primary"
                                        onClick={addItem}
                                    >
                                        Add Item
                                    </Button>
                                </Col>
                            </Row>

                            {/*border*/}
                            <hr className="mt-4 mb-4"/>
                            <Row className="mt-3">
                                <Col md={7}>

                                </Col>
                                <Col md={5}>
                                    <Row className="mb-2">
                                        <Col md={6}><Form.Label className="font-weight-bold">Subtotal</Form.Label></Col>
                                        <Col md={6}><Form.Label className="font-weight-bold float-right">$
                                            120.00</Form.Label></Col>
                                    </Row>
                                    <hr className="mt-4 mb-2"/>
                                    <Row>
                                        <Col md={6}><Form.Label className="font-weight-bold">Total</Form.Label></Col>
                                        <Col md={6}><Form.Label className="font-weight-bold float-right">$
                                            12050.00</Form.Label></Col>
                                    </Row>
                                </Col>
                            </Row>
                        </Card.Body>
                    </Card>

                    <Card className="mb-4">
                        <Card.Body className="bg-white">
                            <Row className="mt-3">
                                <Col md={5}>
                                    {invoiceData.customData.map((item) => {
                                        return (
                                            <Row key={item.key}>
                                                <Col md={12}>
                                                    <Form.Group controlId="customData">
                                                        <Form.Label className="mb-2"><strong>Custom
                                                            Data</strong></Form.Label>
                                                        <div className="d-flex items-center">
                                                            <Form.Control
                                                                className="mt-2"
                                                                type="text"
                                                                name="key"
                                                                placeholder="Key"
                                                                onChange={_setCustomData.bind(this, item)}
                                                            />
                                                            <Form.Control
                                                                className="mt-2"
                                                                type="text"
                                                                name="value"
                                                                placeholder="Value"
                                                                onChange={_setCustomData.bind(this, item)}
                                                            />

                                                            {/* if the length of customData is greater than 1 then show the button*/}
                                                            {invoiceData.customData.length > 1 && (
                                                                <Button
                                                                    className="mt-2 ml-1"
                                                                    variant="outline-secondary"
                                                                    onClick={() => removeCustomData(item)}
                                                                >
                                                                    x
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </Form.Group>
                                                </Col>
                                            </Row>
                                        )
                                    })}
                                    <Button
                                        className="mt-2"
                                        variant="outline-primary"
                                        onClick={() => {
                                            setInvoiceData({
                                                customData: [
                                                    ...invoiceData.customData,
                                                    {
                                                        ...invoiceDefaultData.customData[0],
                                                        key: Date.now(),
                                                    },
                                                ],
                                            })
                                        }}
                                    >
                                        Add data
                                    </Button>
                                </Col>
                                <Col md={1}></Col>
                                <Col md={6} className="border-left">
                                    <Form.Group controlId="notes">
                                        <Form.Label className="mb-2"><strong>Notes</strong></Form.Label>
                                        <Form.Control
                                            className="mt-2"
                                            as="textarea"
                                            rows={3}
                                            placeholder="Notes"
                                        />
                                    </Form.Group>
                                </Col>
                            </Row>
                        </Card.Body>
                    </Card>
                    <Row className="mt-3">
                        <Col md={12}>
                            <Button
                                variant="primary"
                                type="submit"
                                className=""
                            >
                                Create Invoice
                            </Button>
                        </Col>
                    </Row>
                </Form>
            </Container>
        </>
    )
}
