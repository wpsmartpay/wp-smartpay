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

import { Dashboard } from './pages/dashboard'
import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'

import './store/index'

domReady(function() {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Route exact path="/" component={Dashboard} />
                    <Route
                        exact
                        path="/products/list"
                        component={ProductList}
                    />
                    <Route
                        exact
                        path="/products/create"
                        component={CreateProduct}
                    />
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))

    feather.replace()
})
