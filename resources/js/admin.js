// import 'bootstrap'
import feather from 'feather-icons'

// import './admin/media-selector'
// import './admin/product'

jQuery(function($) {
    feather.replace()
})

// React

const { render } = wp.element
import domReady from '@wordpress/dom-ready'
import { HashRouter, Route } from 'react-router-dom'

import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'

domReady(function() {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Route exact path="/product/list" component={ProductList} />
                    <Route path="/product/create" component={CreateProduct} />
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))

    feather.replace()
})
