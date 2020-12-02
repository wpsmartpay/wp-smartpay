import { __ } from '@wordpress/i18n'

import { Container, Form, Card, Accordion, Button } from 'react-bootstrap'
import { FormBuilder } from './FormBuilder'
import { AccordionPanel } from './AccordionPanel'
import { FormAmount } from './FormAmount'

export const FormForm = ({
    form,
    saveForm,
    setFormData,
    shouldReset = false,
}) => {
    return (
        <>
            <div
                className="text-black bg-white border-bottom"
                style={{
                    position: 'fixed',
                    left: '160px',
                    right: 0,
                    top: '32px',
                    zIndex: 99,
                }}
            >
                <Container>
                    <div className="d-flex align-items-center justify-content-between py-2">
                        <div className="w-50">
                            <Form.Control
                                className="mr-3"
                                size="sm"
                                type="text"
                                name="title"
                                value={form.title || ''}
                                onChange={(e) => {
                                    setFormData({
                                        [e.target.name]: e.target.value,
                                    })
                                }}
                                placeholder={__(
                                    'Your awesome product title here',
                                    'smartpay'
                                )}
                            />
                        </div>
                        <div className="ml-auto">
                            <Button
                                onClick={saveForm}
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container style={{ marginTop: '80px' }} className="pt-2">
                <Accordion defaultActiveKey="amounts">
                    <AccordionPanel
                        eventKey="amounts"
                        title={__('Form Amounts', 'smartpay')}
                        body={<FormAmount />}
                    />
                    <AccordionPanel
                        eventKey="builder"
                        title={__('Form Builder', 'smartpay')}
                        body={
                            <FormBuilder
                                form={form}
                                setFormData={setFormData}
                                shouldReset={shouldReset}
                            />
                        }
                    />
                </Accordion>
            </Container>
        </>
    )
}
