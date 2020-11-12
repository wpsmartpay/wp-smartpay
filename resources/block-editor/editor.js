import {
    Popover,
    SlotFillProvider,
    DropZoneProvider,
    FocusReturnProvider,
} from '@wordpress/components'

import { useState } from '@wordpress/element'

import { InterfaceSkeleton, FullscreenMode } from '@wordpress/interface'

import Notices from './components/notices'
import Header from './components/header'
import Sidebar from './components/sidebar'
import BlockEditor from './components/block-editor'

function Editor({ settings }) {
    const [formData, setformData] = useState('')
    return (
        <>
            <FullscreenMode isActive={false} />
            <SlotFillProvider>
                <DropZoneProvider>
                    <FocusReturnProvider>
                        <InterfaceSkeleton
                            header={<Header formData={formData} />}
                            sidebar={<Sidebar />}
                            content={
                                <>
                                    {/* <Notices /> */}
                                    <BlockEditor
                                        setformData={setformData}
                                        settings={settings}
                                    />
                                </>
                            }
                        />

                        <Popover.Slot />
                    </FocusReturnProvider>
                </DropZoneProvider>
            </SlotFillProvider>
        </>
    )
}

export default Editor
