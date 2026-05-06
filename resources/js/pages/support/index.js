import domReady from '@wordpress/dom-ready'
import { render } from '@wordpress/element'
import { SupportPage } from './SupportPage'

domReady(function () {
    const root = document.getElementById('smartpay-support')
    if (root) {
        render(<SupportPage />, root)
    }
})
