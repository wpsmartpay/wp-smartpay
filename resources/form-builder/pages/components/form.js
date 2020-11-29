import { __ } from '@wordpress/i18n'
import { serialize, parse } from '@wordpress/blocks'
import {
    Popover,
    SlotFillProvider,
    DropZoneProvider,
    FocusReturnProvider,
} from '@wordpress/components'
import { InterfaceSkeleton } from '@wordpress/interface'

import { Form } from 'react-bootstrap'
import { BlockEditor } from '../../components/block-editor'
import { Sidebar } from '../../components/sidebar'

export const FormForm = ({ form, setformData, shouldReset = false }) => {
    return (
        <>
            <div className="mt-5">
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
                <div className="smartpay-form-block-editor block-editor">
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
                                            storedBlocks={parse(
                                                form.body || []
                                            )}
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
            </div>
        </>
    )
}
