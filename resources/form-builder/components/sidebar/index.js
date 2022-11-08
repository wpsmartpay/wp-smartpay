import { Panel, createSlotFill } from '@wordpress/components'

import { __ } from '@wordpress/i18n'

const { Slot: InspectorSlot, Fill: InspectorFill } = createSlotFill(
    'SmartPayFormEditorSidebarInspector'
)

export const Sidebar = () => {
    return (
        <div
            className="smartpay-block-editor-sidebar"
            role="region"
            tabIndex="-1"
        >
            <Panel header={__('Block Option', 'smartpay')}>
                <InspectorSlot bubblesVirtually />
            </Panel>
        </div>
    )
}

Sidebar.InspectorFill = InspectorFill
