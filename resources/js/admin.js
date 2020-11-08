// import 'bootstrap'
// import feather from 'feather-icons'

// import './admin/media-selector'
// import './admin/product'

// jQuery(function($) {
//     feather.replace()
// })

// React

const { render } = wp.element

import { Header } from './components/layouts/header'
import { CreateProduct } from './pages/product/create'

window.addEventListener('DOMContentLoaded', event => {
    const SmartPay = () => {
        return (
            <div>
                <CreateProduct />
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
