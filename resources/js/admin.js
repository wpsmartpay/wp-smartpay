const { render } = wp.element
import domReady from '@wordpress/dom-ready'
import { HashRouter, Route, Switch } from 'react-router-dom'

import { Dashboard } from './pages/dashboard'

// Product
import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'
import { EditProduct } from './pages/product/edit'

// Form
import { FormList } from './pages/form/index'
import { CreateForm } from './pages/form/create'
import { EditForm } from './pages/form/edit'

// Customer
import { CustomerList } from './pages/customer/index'
import { CreateCustomer } from './pages/customer/create'
import { EditCustomer } from './pages/customer/edit'

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

import menuFix from './admin/menu-fix'

domReady(function () {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Switch>
                        {/* Dashboard */}
                        <Route exact path="/" component={Dashboard} />

                        {/* Product */}
                        <Route path="/products" component={ProductList} />
                        <Route
                            path="/products/create"
                            component={CreateProduct}
                        />
                        <Route
                            path="/products/:productId/edit"
                            component={EditProduct}
                        />

                        {/* Form */}
                        <Route path="/forms" component={FormList} />
                        <Route path="/forms/create" component={CreateForm} />
                        <Route
                            path="/forms/:formId/edit"
                            component={EditForm}
                        />

                        {/* Customer */}
                        <Route path="/customers" component={CustomerList} />
                        <Route
                            path="/customers/create"
                            component={CreateCustomer}
                        />
                        <Route
                            path="/customers/:customerId/edit"
                            component={EditCustomer}
                        />

                        {/* Coupon */}
                        <Route path="/coupons" component={CouponList} />
                        <Route
                            path="/coupons/create"
                            component={CreateCoupon}
                        />
                        <Route
                            path="/coupons/:couponId/edit"
                            component={EditCoupon}
                        />

                        {/* Payment */}
                        <Route path="/payments" component={PaymentList} />
                        <Route
                            path="/payments/create"
                            component={CreatePayment}
                        />
                        <Route
                            path="/payments/:paymentId/edit"
                            component={EditPayment}
                        />

                        {/* Not Found */}
                        <Route component={NotFound} />
                    </Switch>
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})

menuFix('smartpay')
