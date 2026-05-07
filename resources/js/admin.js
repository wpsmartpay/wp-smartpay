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
import { PaymentDetailPage } from './pages/payment/PaymentDetailPage'

// Form Data
import { FormData } from './pages/form-data/index.jsx'

// Other pages
import { NotFound } from './pages/not-found'
// import { AdminFooter } from './components/AdminFooter'

import './store/index'

import './admin/menu-fix'

// Pro CSS
import './utils/pro-css'

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
                            path="/customers/:customerId"
                            element={<ShowCustomer />}
                        />

                        {/* Coupon */}
                        <Route exact path="/coupons" element={<CouponList />} />

                        {/* Payment */}
                        <Route exact path="/payments" element={<PaymentList />} />
                        {/* Reserve /payments/new for pro plugin — must come before :paymentId */}
                        <Route exact path="/payments/new" element={null} />
                        <Route exact path="/payments/:paymentId" element={<PaymentDetailPage />} />

                        {/* Form Data */}
                        <Route exact path="/form-data" element={<FormData />} />

                        <Route element={<NotFound />} />
                    </Routes>
                </HashRouter>

                {/* load the smartpay-pro routes — pass our Routes so pro can extend them */}
                {window.smartPayRouteHooks.applyFilters(
                    'smartPayAdminRoute',
                    null,
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
                            path="/customers/:customerId"
                            element={<ShowCustomer />}
                        />

                        {/* Coupon */}
                        <Route exact path="/coupons" element={<CouponList />} />

                        {/* Payment */}
                        <Route exact path="/payments" element={<PaymentList />} />
                        {/* Reserve /payments/new for pro plugin — must come before :paymentId */}
                        <Route exact path="/payments/new" element={null} />
                        <Route exact path="/payments/:paymentId" element={<PaymentDetailPage />} />

                        {/* Form Data */}
                        <Route exact path="/form-data" element={<FormData />} />

                        <Route element={<NotFound />} />
                    </Routes>,
                )}

                {/* <AdminFooter /> */}
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
