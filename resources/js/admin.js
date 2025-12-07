import domReady from '@wordpress/dom-ready'
import { render } from '@wordpress/element'
import { HashRouter, Route, Routes } from 'react-router-dom'

import { Dashboard } from './pages/dashboard'

// Product
import { CreateProduct } from './pages/product/create'
import { EditProduct } from './pages/product/edit'
import { ProductList } from './pages/product/index'

// Customer
import { CustomerList } from './pages/customer/index'
import { ShowCustomer } from './pages/customer/show'

// Coupon
import { CouponList } from './pages/coupon/index'

// Payment
import { PaymentList } from './pages/payment/index'

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
                        <Route
                            exact
                            path="/customers/:customerId/"
                            element={<ShowCustomer />}
                        />

                        {/* Coupon */}
                        <Route exact path="/coupons" element={<CouponList />} />

                        {/* Payment */}
                        <Route exact path="/payments" element={<PaymentList />} />

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
