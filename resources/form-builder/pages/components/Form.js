import { __ } from '@wordpress/i18n'
import { parse } from '@wordpress/blocks'
import { Container, Tabs, Tab, Form, Button } from 'react-bootstrap'
import { Alert } from '../../components/Alert'

import { FormBuilder } from './FormBuilder'
import { FormOptionTab } from './FormOptionTab'

export const FormForm = ({
    form,
    onSubmit,
    setFormData,
    shouldReset = false,
}) => {
    const checkRequiredBlocks = (blocks) => {
        const requiredBlocks = { name: 0, email: 0 }

        if (blocks.length) {
            blocks.map((block) => {
                if ('smartpay-form/name' === block.name) {
                    requiredBlocks.name = requiredBlocks.name + 1
                } else if ('smartpay-form/email' === block.name) {
                    requiredBlocks.email = requiredBlocks.email + 1
                }
            })
        }

        return requiredBlocks
    }

    const saveForm = () => {
        const blocks = parse(form.body)

        const requiredBlocks = checkRequiredBlocks(blocks)

        if (requiredBlocks.name < 1) {
            Alert('You must have one name field', 'error')
        } else if (requiredBlocks.name > 1) {
            Alert('Your form contains more than one name field', 'error')
        } else if (requiredBlocks.email < 1) {
            Alert('You must have one email field', 'error')
        } else if (requiredBlocks.email > 1) {
            Alert('Your form contains more than one email field', 'error')
        } else {
            onSubmit()
        }
    }

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
                                className="mr-3 border-0"
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
                            <div className="d-flex flex-row">
                                {form.id && (
                                    <Form.Control
                                        size="sm"
                                        type="text"
                                        value={`[smartpay_form id="${form.id}"]`}
                                        readOnly
                                        className="mr-2"
                                    />
                                )}
                                {form.id &&
                                    form.extra?.form_preview_page_permalink && (
                                        <>
                                            <Button
                                                variant="link"
                                                href={
                                                    form.extra
                                                        .form_preview_page_permalink
                                                }
                                                target="_blank"
                                                className="btn btn-sm text-decoration-none px-3 mr-2"
                                            >
                                                {__('Preview', 'smartpay')}
                                            </Button>
                                        </>
                                    )}
                                <Button
                                    onClick={saveForm}
                                    className="btn btn-primary btn-sm text-decoration-none px-3"
                                >
                                    {__(
                                        form.id ? 'Save' : 'Publish',
                                        'smartpay'
                                    )}
                                </Button>
                            </div>
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
                            <FormBuilder
                                form={form}
                                setFormData={setFormData}
                                shouldReset={shouldReset}
                            />
                        </Tab>
                        <Tab
                            eventKey="options"
                            className="text-decoration-none mt-3"
                            title={__('Options', 'smartpay')}
                        >
                            <FormOptionTab
                                form={form}
                                setFormData={setFormData}
                            />
                        </Tab>
                    </Tabs>
                </div>
            </Container>
        </>
    )
}
