import { __ } from '@wordpress/i18n'
import { Container, Form, Button } from 'react-bootstrap'

export const CreateProduct = () => {
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
                                className="btn btn-primary px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
            <Container>
                <Form className="my-3">
                    <Form.Group controlId="title">
                        <Form.Control type="text" placeholder="Product title" />
                    </Form.Group>
                </Form>
            </Container>
        </>
    )
}
