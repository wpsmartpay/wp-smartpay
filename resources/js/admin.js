// import 'bootstrap'
// import feather from 'feather-icons'

// import './admin/media-selector'
// import './admin/product'

// jQuery(function($) {
//     feather.replace()
// })

// React

const { render } = wp.element
import domReady from '@wordpress/dom-ready'

import { CreateProduct } from './pages/product/create'

domReady(function() {
    const SmartPay = () => {
        return (
            <div>
                <CreateProduct />
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
