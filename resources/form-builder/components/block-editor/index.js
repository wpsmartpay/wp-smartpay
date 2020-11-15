// import '@wordpress/editor'
import '@wordpress/format-library'
import { useSelect } from '@wordpress/data'
import { useEffect, useState, useMemo } from '@wordpress/element'
import { uploadMedia } from '@wordpress/media-utils'

import {
    BlockEditorKeyboardShortcuts,
    BlockEditorProvider,
    BlockList,
    BlockInspector,
    WritingFlow,
    ObserveTyping,
} from '@wordpress/block-editor'

import { Sidebar } from '../sidebar'

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
        if (storedBlocks?.length) {
            handleUpdateBlocks(storedBlocks)
        }
    }, [])

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
                    <WritingFlow>
                        <ObserveTyping>
                            <BlockList className="smartpay-block-editor__block-list" />
                        </ObserveTyping>
                    </WritingFlow>
                </div>
            </BlockEditorProvider>
        </div>
    )
}
