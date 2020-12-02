import { __ } from '@wordpress/i18n'

import { Container, Tabs, Tab, Form, Button } from 'react-bootstrap'
import { FormBuilderTab } from './FormBuilderTab'
import { FormOptionTab } from './FormOptionTab'

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

            <Container style={{ marginTop: '80px' }}>
                <div className="mt-5">
                    <Tabs fill defaultActiveKey="builder">
                        <Tab
                            eventKey="builder"
                            className="text-decoration-none mt-3"
                            title={__('Builder', 'smartpay')}
                        >
                            <FormBuilderTab
                                form={form}
                                setFormData={setFormData}
                                shouldReset={shouldReset}
                            />
                        </Tab>
                        <Tab
                            eventKey="setting"
                            className="text-decoration-none mt-3"
                            title={__('Setting', 'smartpay')}
                        >
                            <FormOptionTab />
                        </Tab>
                    </Tabs>
                </div>
            </Container>
        </>
    )
}
