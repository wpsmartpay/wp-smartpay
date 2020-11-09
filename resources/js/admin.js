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

// Coupons
import { CouponList } from './pages/coupon/index'
import { CreateCoupon } from './pages/coupon/create'

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
                    <Route exact path="/coupons/list" component={CouponList} />
                    <Route
                        exact
                        path="/coupons/create"
                        component={CreateCoupon}
                    />
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))

    feather.replace()
})
