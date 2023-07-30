import domReady from '@wordpress/dom-ready'
import { render } from '@wordpress/element'
import { HashRouter, Route, Routes, Link } from 'react-router-dom'

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
import { Invoices } from "./pages/invoice";
import {CreateInvoice} from "./pages/invoice/create";

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
import {EditInvoice} from "./pages/invoice/edit";
export const smartPayRouteHooks = createHooks()
window.smartPayRouteHooks = smartPayRouteHooks

domReady(function () {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Routes>
                        {/* Dashboard */}
                        <Route exact path="/" element={<Dashboard />} />

                        {/* Product */}
                        <Route
                            exact
                            path="/products"
                            element={<ProductList />}
                        />
                        <Route
                            exact
                            path="/products/create"
                            element={<CreateProduct />}
                        />
                        <Route
                            exact
                            path="/products/:productId/edit"
                            element={<EditProduct />}
                        />

                        {/* Customer */}
                        <Route
                            exact
                            path="/customers"
                            element={<CustomerList />}
                        />

                        {/* Invoices */}
                        <Route
                            exact
                            path="/invoices"
                            element={<Invoices />}
                        />

                        <Route
                            exact
                            path="/invoices/create"
                            element={<CreateInvoice />}
                        />

                        <Route
                            exact
                            path="/invoices/:invoiceId/edit"
                            element={<EditInvoice />}
                        />

                        <Route
                            exact
                            path="/customers/:customerId/"
                            element={<ShowCustomer />}
                        />

                        {/* Coupon */}
                        <Route exact path="/coupons" element={<CouponList />} />
                        <Route
                            exact
                            path="/coupons/create"
                            element={<CreateCoupon />}
                        />
                        <Route
                            exact
                            path="/coupons/:couponId/edit"
                            element={<EditCoupon />}
                        />

                        {/* Payment */}
                        <Route
                            exact
                            path="/payments"
                            element={<PaymentList />}
                        />
                        <Route
                            exact
                            path="/payments/create"
                            element={<CreatePayment />}
                        />
                        <Route
                            exact
                            path="/payments/:paymentId/edit"
                            element={<EditPayment />}
                        />

                        <Route element={<NotFound />} />
                    </Routes>
                </HashRouter>

                {/* load the smartpay-pro routes */}
                {window.smartPayRouteHooks.applyFilters(
                    'smartPayAdminRoute',
                    [],
                )}
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
