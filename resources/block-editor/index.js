import domReady from '@wordpress/dom-ready'
import { render, useEffect } from '@wordpress/element'
import { registerCoreBlocks } from '@wordpress/block-library'
import Editor from './editor'

import './styles.scss'

domReady(function () {
    const FormBuilder = () => {
        const settings = window.smartPayBlockEditorSettings || {}

        useEffect(() => {
            registerCoreBlocks()
        }, [])
        return <Editor settings={settings} />
    }

    render(
        <FormBuilder />,
        document.getElementById('smartpay-form-block-editor')
    )
})
