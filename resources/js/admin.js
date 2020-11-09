import feather from 'feather-icons'

const { render } = wp.element
import domReady from '@wordpress/dom-ready'
import { HashRouter, Route } from 'react-router-dom'

import { Dashboard } from './pages/dashboard'

// Product
import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'
import { EditProduct } from './pages/product/edit'

// Customer
import { CustomerList } from './pages/customer/index'
import { CreateCustomer } from './pages/customer/create'
import { EditCustomer } from './pages/customer/edit'

// Coupon
import { CouponList } from './pages/coupon/index'
import { CreateCoupon } from './pages/coupon/create'

import './store/index'

domReady(function() {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Route exact path="/" component={Dashboard} />
                    {/* Product */}
                    <Route exact path="/products" component={ProductList} />
                    <Route
                        exact
                        path="/products/create"
                        component={CreateProduct}
                    />
                    <Route
                        exact
                        path="/products/:productId/edit"
                        component={EditProduct}
                    />

                    {/* Customer */}
                    <Route exact path="/customers" component={CustomerList} />
                    <Route
                        exact
                        path="/customers/create"
                        component={CreateCustomer}
                    />
                    <Route
                        exact
                        path="/customers/:productId/edit"
                        component={EditCustomer}
                    />

                    {/* Coupon */}
                    <Route exact path="/coupons" component={CouponList} />
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
