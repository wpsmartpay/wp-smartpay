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

// Native Forms
import { NativeFormList } from './admin/native-forms'

// Other pages
import { NotFound } from './pages/not-found'
import { SubscriptionsLockedPage, ReportsLockedPage, InvoicesLockedPage, WebhooksLockedPage } from './components/LockedFeaturePage'
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
                {/* Free plugin router — always rendered */}
                <HashRouter>
                    <Routes>
                        <Route exact path="/" element={<Dashboard />} />

                        <Route exact path="/products"                 element={<ProductList />} />
                        <Route exact path="/products/create"          element={<CreateProduct />} />
                        <Route exact path="/products/:productId/edit" element={<EditProduct />} />

                        <Route exact path="/customers"                element={<CustomerList />} />
                        <Route exact path="/customers/:customerId"    element={<ShowCustomer />} />

                        <Route exact path="/coupons"                  element={<CouponList />} />

                        <Route exact path="/payments"                 element={<PaymentList />} />
                        <Route exact path="/payments/new"             element={null} />
                        <Route exact path="/payments/:paymentId"      element={<PaymentDetailPage />} />

                        <Route exact path="/form-data"                element={<FormData />} />

                        <Route exact path="/native-forms" element={<NativeFormList />} />

                        <Route
                            exact path="/subscriptions"
                            element={window.smartpayProData?.isActive ? null : <SubscriptionsLockedPage />}
                        />
                        <Route
                            exact path="/reports"
                            element={window.smartpayProData?.isActive ? null : <ReportsLockedPage />}
                        />
                        <Route exact path="/invoices"           element={window.smartpayProData?.isActive ? null : <InvoicesLockedPage />} />
                        <Route exact path="/invoices/:invoiceId" element={window.smartpayProData?.isActive ? null : <InvoicesLockedPage />} />
                        <Route exact path="/webhooks"           element={window.smartpayProData?.isActive ? null : <WebhooksLockedPage />} />

                        <Route element={<NotFound />} />
                    </Routes>
                </HashRouter>

                {/* Pro plugin adds its own HashRouter with pro-only routes */}
                {window.smartPayRouteHooks.applyFilters('smartPayAdminRoute', null)}
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))
})
