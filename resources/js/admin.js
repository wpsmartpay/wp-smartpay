import feather from 'feather-icons'

const { render } = wp.element
import domReady from '@wordpress/dom-ready'
import { HashRouter, Route } from 'react-router-dom'

import { Dashboard } from './pages/dashboard'

// Products
import { ProductList } from './pages/product/index'
import { CreateProduct } from './pages/product/create'
import { EditProduct } from './pages/product/edit'

// Coupons
import { CouponList } from './pages/coupon/index'
import { CreateCoupon } from './pages/coupon/create'

import './store/index'

domReady(function () {
    const SmartPay = () => {
        return (
            <div>
                <HashRouter>
                    <Route exact path="/" component={Dashboard} />
                    {/* Products */}
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
                    <Route
                        exact
                        path="/products/:productId/edit"
                        component={EditProduct}
                    />

                    {/* Coupons */}
                    <Route exact path="/coupons/list" component={CouponList} />
                    <Route
                        exact
                        path="/coupons/create"
                        render={(props) => (
                            <CreateCoupon
                                {...props}
                                resturl={smartpay.restUrl}
                                nonce={smartpay.apiNonce}
                            />
                        )}
                    />
                </HashRouter>
            </div>
        )
    }

    render(<SmartPay />, document.getElementById('smartpay'))

    feather.replace()
})
