import { __ } from '@wordpress/i18n'
import { parse } from '@wordpress/blocks'
import { Container, Tabs, Tab, Form, Button } from 'react-bootstrap'
import { Alert } from '../../components/Alert'

import { FormBuilder } from './FormBuilder'
import { FormOptionTab } from './FormOptionTab'
import {FormPricingTab} from "./FormPricingTab";

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
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {form.id
                                ? __('Edit Form', 'smartpay')
                                : __('Create Form', 'smartpay')}
                        </h2>
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
                                    {form.id
                                        ? __('Save', 'smartpay')
                                        : __('Publish', 'smartpay')}
                                </Button>
                            </div>
                        </div>
                    </div>
                </Container>
            </div>

            <Container style={{ marginTop: '80px' }}>
                <div className="p-4 bg-white">
                    <Form.Control
                        type="text"
                        className="mb-4"
                        name="title"
                        value={form.title || ''}
                        onChange={(e) => {
                            setFormData({
                                [e.target.name]: e.target.value,
                            })
                        }}
                        placeholder={__(
                            'Your awesome form title here',
                            'smartpay'
                        )}
                    />

                    <Tabs fill defaultActiveKey="builder">
                        <Tab
                            eventKey="builder"
                            className="mt-3"
                            title={
                                <p className="font-weight-bold m-0">
                                    {__('Builder', 'smartpay')}
                                </p>
                            }
                        >
                            <FormBuilder
                                form={form}
                                setFormData={setFormData}
                                shouldReset={shouldReset}
                            />
                        </Tab>
                        <Tab
                            eventKey="pricing"
                            className="mt-3"
                            title={
                                <p className="font-weight-bold m-0">
                                    {__('Pricing', 'smartpay')}
                                </p>
                            }
                        >
                            <FormPricingTab
                                form={form}
                                setFormData={setFormData}
                            />
                        </Tab>

                        <Tab
                            eventKey="options"
                            className="mt-3"
                            title={
                                <p className="font-weight-bold m-0">
                                    {__('Options', 'smartpay')}
                                </p>
                            }
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
