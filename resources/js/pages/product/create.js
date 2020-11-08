import { __ } from '@wordpress/i18n'
import { Container, Nav, Form, Button } from 'react-bootstrap'

export const CreateProduct = () => {
    const createProduct = data => {
        console.log(tinyMCE.activeEditor.getContent())
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
                                className="btn btn-primary px-3"
                                onClick={createProduct}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
        </>
    )
}
