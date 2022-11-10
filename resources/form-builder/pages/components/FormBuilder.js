import {parse, serialize} from '@wordpress/blocks'

import {BlockEditor} from '../../components/block-editor'
import {InterfaceSkeleton} from '@wordpress/interface'
import {ShortcutProvider} from '@wordpress/keyboard-shortcuts'
import {Sidebar} from '../../components/sidebar'
import {SlotFillProvider} from '@wordpress/components'

export const FormBuilder = ({form, setFormData, shouldReset}) => {
    const makeFormFields = (blocks) => {
        let fields = []

        blocks.map((block) => {
            const fieldName = block?.attributes?.attributes?.name

            if (fieldName) {
                fields.push({[fieldName]: block.attributes})
            }
        })

        return fields
    }

    return (
        <div
            className="smartpay-form-block-editor block-editor"
            style={{minHeight: '70vh'}}
        >
            <ShortcutProvider>
                <SlotFillProvider>
                    <InterfaceSkeleton
                        content={
                            <BlockEditor
                                resetBlocks={shouldReset}
                                onBlockUpdate={(blocks) => {
                                    setFormData({
                                        body: serialize(blocks),
                                        fields: makeFormFields(blocks),
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
                                <Sidebar/>
                            </div>
                        }
                    />
                </SlotFillProvider>
            </ShortcutProvider>
        </div>
    )
}
