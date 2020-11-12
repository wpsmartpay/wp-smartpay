import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'
import { dispatch } from '@wordpress/data'

import { SaveForm } from '../../../js/http/form'

export default function Header({ formData }) {
    const submitHandler = (event) => {
        event.preventDefault()
        SaveForm(formData).then((response) => {
            console.log(response)
        })
    }
    return (
        <div
            className="smartpay-block-editor-header"
            role="region"
            aria-label={__(
                'Standalone Editor top bar.',
                'smartpay-block-editor'
            )}
            tabIndex="-1"
        >
            <h1 className="smartpay-block-editor-header__title">
                {__('Smartpay Form Builder', 'smartpay-block-editor')}
            </h1>

            <Button onClick={submitHandler} isPrimary>
                {__('Save', 'smartpay-block-editor')}
            </Button>
        </div>
    )
}
