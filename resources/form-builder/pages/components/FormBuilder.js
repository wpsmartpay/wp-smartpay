import { __ } from '@wordpress/i18n'
import { serialize, parse } from '@wordpress/blocks'
import {
    Popover,
    SlotFillProvider,
    DropZoneProvider,
    FocusReturnProvider,
} from '@wordpress/components'
import { InterfaceSkeleton } from '@wordpress/interface'
import { BlockEditor } from '../../components/block-editor'
import { Sidebar } from '../../components/sidebar'

export const FormBuilder = ({ form, setFormData, shouldReset }) => {
    return (
        <div
            className="smartpay-form-block-editor block-editor"
            style={{ minHeight: '70vh' }}
        >
            <SlotFillProvider>
                <DropZoneProvider>
                    <FocusReturnProvider>
                        <InterfaceSkeleton
                            content={
                                <BlockEditor
                                    resetBlocks={shouldReset}
                                    onBlockUpdate={(blocks) => {
                                        setFormData({
                                            body: serialize(blocks),
                                        })
                                    }}
                                    settings={
                                        window.smartPayBlockEditorSettings || {}
                                    }
                                    storedBlocks={parse(form.body || [])}
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
    )
}
