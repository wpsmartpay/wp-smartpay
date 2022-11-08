import '@wordpress/format-library'

import {
    BlockEditorKeyboardShortcuts,
    BlockEditorProvider,
    BlockInspector,
    BlockList,
    BlockTools,
    ObserveTyping,
    WritingFlow,
} from '@wordpress/block-editor'
import { useEffect, useMemo, useState } from '@wordpress/element'

import { Popover } from '@wordpress/components'
import { Sidebar } from '../sidebar'
import { uploadMedia } from '@wordpress/media-utils'
import { useSelect } from '@wordpress/data'

export const BlockEditor = ({
    settings: _settings,
    storedBlocks = [],
    resetBlocks,
    onBlockUpdate,
}) => {
    const [blocks, updateBlocks] = useState([])

    const canUserCreateMedia = useSelect((select) => {
        const _canUserCreateMedia = select('core').canUser('create', 'media')
        return _canUserCreateMedia || _canUserCreateMedia !== false
    }, [])

    const settings = useMemo(() => {
        if (!canUserCreateMedia) {
            return _settings
        }
        return {
            ..._settings,
            mediaUpload({ onError, ...rest }) {
                uploadMedia({
                    wpAllowedMimeTypes: _settings.allowedMimeTypes,
                    onError: ({ message }) => onError(message),
                    ...rest,
                })
            },
        }
    }, [canUserCreateMedia, _settings])

    useEffect(() => {
        if (!blocks.length && storedBlocks?.length) {
            handleUpdateBlocks(storedBlocks)
        }
    }, [storedBlocks])

    useEffect(() => {
        onBlockUpdate(blocks)
    }, [blocks])

    useEffect(() => {
        if (resetBlocks) {
            updateBlocks([])
        }
    }, [resetBlocks])

    /**
     * Wrapper for updating blocks. Required as `onInput` callback passed to
     * `BlockEditorProvider` is now called with more than 1 argument. Therefore
     * attempting to setState directly via `updateBlocks` will trigger an error
     * in React.
     */
    const handleUpdateBlocks = (blocks) => {
        updateBlocks(blocks)
    }

    const handlePersistBlocks = (newBlocks) => {
        updateBlocks(newBlocks)
    }

    return (
        <div className="smartpay-block-editor">
            <BlockEditorProvider
                value={blocks}
                onInput={handleUpdateBlocks}
                onChange={handlePersistBlocks}
                settings={settings}
            >
                <Sidebar.InspectorFill>
                    <BlockInspector />
                </Sidebar.InspectorFill>

                <div className="editor-styles-wrapper">
                    <BlockEditorKeyboardShortcuts />
                    <BlockTools>
                        <WritingFlow>
                            <ObserveTyping>
                                <BlockList />
                            </ObserveTyping>
                        </WritingFlow>
                    </BlockTools>
                    <Popover.Slot />
                </div>
            </BlockEditorProvider>
        </div>
    )
}
