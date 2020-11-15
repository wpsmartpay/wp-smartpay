import { __ } from '@wordpress/i18n'
import { Container, Form, Alert, Button } from 'react-bootstrap'
import { InterfaceSkeleton } from '@wordpress/interface'
import { useState } from '@wordpress/element'
import { serialize, parse } from '@wordpress/blocks'
import {
    Popover,
    SlotFillProvider,
    DropZoneProvider,
    FocusReturnProvider,
} from '@wordpress/components'
import { Save } from '../http/form'

import { BlockEditor } from '../components/block-editor'
import { Sidebar } from '../components/sidebar'

const defaultFormData = {
    title: '',
    body: '',
}

export const CreateForm = () => {
    const [form, setform] = useState(defaultFormData)
    const [response, setRespose] = useState({})
    const [shouldReset, setShouldReset] = useState(false)

    const setformData = (data) => {
        setform({ ...form, ...data })
    }

    const SaveForm = () => {
        Save(JSON.stringify(form)).then((response) => {
            setform(defaultFormData)
            setShouldReset(true)

            // TODO: Set data to store
            // TODO: Toggle reset value
            setRespose({
                type: 'success',
                message: __(response.message, 'smartpay'),
            })
        })
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
                            {__('Create New Form', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                onClick={SaveForm}
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container style={{ marginTop: '80px' }}>
                {response.message && (
                    <Alert className="mt-3" variant={response.type}>
                        {response.message}
                    </Alert>
                )}
                <div className="bg-white mt-5">
                    <Form.Group controlId="title" className="mb-5">
                        <Form.Control
                            type="text"
                            name="title"
                            value={form.title || ''}
                            onChange={(e) => {
                                setformData({
                                    [e.target.name]: e.target.value,
                                })
                            }}
                            placeholder={__(
                                'Your awesome product title here',
                                'smartpay'
                            )}
                        />
                    </Form.Group>

                    <SlotFillProvider>
                        <DropZoneProvider>
                            <FocusReturnProvider>
                                <InterfaceSkeleton
                                    content={
                                        <BlockEditor
                                            resetBlocks={shouldReset}
                                            onBlockUpdate={(blocks) => {
                                                setformData({
                                                    body: serialize(blocks),
                                                })
                                            }}
                                            settings={
                                                window.smartPayBlockEditorSettings ||
                                                {}
                                            }
                                            storedBlocks={parse(form.body)}
                                        />
                                    }
                                    sidebar={
                                        <div>
                                            <Sidebar />
                                        </div>
                                    }
                                />
                                <Popover.Slot />
                            </FocusReturnProvider>
                        </DropZoneProvider>
                    </SlotFillProvider>
                </div>
            </Container>
        </>
    )
}
