import domReady from '@wordpress/dom-ready'
import { render } from '@wordpress/element'
import { HashRouter, Route, Switch, Link } from 'react-router-dom'

import { Dashboard } from './pages/dashboard'

// Product
import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'
import { EditProduct } from './pages/product/edit'

// Customer
import { CustomerList } from './pages/customer/index'
import { ShowCustomer } from './pages/customer/show'

// Coupon
import { CouponList } from './pages/coupon/index'
import { CreateCoupon } from './pages/coupon/create'
import { EditCoupon } from './pages/coupon/edit'

// Payment
import { PaymentList } from './pages/payment/index'
import { CreatePayment } from './pages/payment/create'
import { EditPayment } from './pages/payment/edit'

// Other pages
import { NotFound } from './pages/not-found'

import './store/index'

import './admin/menu-fix'

//Hooks
import { createHooks } from '@wordpress/hooks'
export const smartPayRouteHooks = createHooks()
window.smartPayRouteHooks = smartPayRouteHooks

domReady(function () {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Switch>
                        {/* Dashboard */}
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
                        <Route
                            exact
                            path="/customers"
                            component={CustomerList}
                        />
                        <Route
                            exact
                            path="/customers/:customerId/"
                            component={ShowCustomer}
                        />

                        {/* Coupon */}
                        <Route exact path="/coupons" component={CouponList} />
                        <Route
                            exact
                            path="/coupons/create"
                            component={CreateCoupon}
                        />
                        <Route
                            exact
                            path="/coupons/:couponId/edit"
                            component={EditCoupon}
                        />

                        {/* Payment */}
                        <Route exact path="/payments" component={PaymentList} />
                        <Route
                            exact
                            path="/payments/create"
                            component={CreatePayment}
                        />
                        <Route
                            exact
                            path="/payments/:paymentId/edit"
                            component={EditPayment}
                        />

                        {window.smartPayRouteHooks.applyFilters(
                            'smartPayAdminRoute',
                            [],
                            Route,
                            Link
                        )}

                        {/* Not Found */}
                        <Route component={NotFound} />
                    </Switch>
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
