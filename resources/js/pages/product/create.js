import { __ } from '@wordpress/i18n'
import { useNavigate } from 'react-router-dom'
import { useReducer, useState } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2'
import { SaveProduct } from '../../http/product'
import { ProductForm, CoverImageCard } from './components/form'
import { productDefaultData } from '../../utils/constant'

const { dispatch } = wp.data

const { Header } = window.WPSmartPayUI

const reducer = (state, data) => ({ ...state, ...data })

const TABS = [
    { key: 'details',  label: __('Product Details', 'smartpay') },
    { key: 'pricing',  label: __('Pricing',         'smartpay') },
    { key: 'checkout', label: __('Checkout',         'smartpay') },
]

export const CreateProduct = () => {
    const [product, setProductData] = useReducer(reducer, productDefaultData)
    const [activeTab, setActiveTab] = useState('details')
    const navigate = useNavigate()

    const createProduct = () => {
        const title = (product.title || '').trim()
        if (!title) {
            Swal.fire({ toast: true, icon: 'error', title: __('Product title is required.', 'smartpay'), position: 'top-end', showConfirmButton: false, timer: 2500, showClass: { popup: 'swal2-noanimation' }, hideClass: { popup: '' } })
            return
        }
        const basePrice = parseFloat(product.base_price) || 0
        const hasVariationPrice = (product.variations || []).some((v) => parseFloat(v.base_price) > 0)
        if (basePrice <= 0 && !hasVariationPrice) {
            Swal.fire({ toast: true, icon: 'error', title: __('At least one price must be greater than zero.', 'smartpay'), position: 'top-end', showConfirmButton: false, timer: 2500, showClass: { popup: 'swal2-noanimation' }, hideClass: { popup: '' } })
            return
        }

        SaveProduct(JSON.stringify(product))
            .then((response) => {
                setProductData(productDefaultData)
                tinymce.get('description').setContent('')

                dispatch('smartpay/products').setProduct(response.product)
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: __(response.message, 'smartpay'),
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    showClass: { popup: 'swal2-noanimation' },
                    hideClass: { popup: '' },
                })

                navigate(`/products/${response.product.id}/edit`)
            })
            .catch((error) => {
                Swal.fire({
                    toast: true,
                    icon: 'error',
                    title: __(error.message, 'smartpay'),
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    showClass: { popup: 'swal2-noanimation' },
                    hideClass: { popup: '' },
                })
            })
    }

    return (
        <>
            <Header title={__('Create Product', 'smartpay')} />
            <div className="sp-layout">

                {/* Page action bar */}
                <div className="sp-page-head">
                    <div>
                        <p className="sp-page-head__breadcrumb">
                            <span>{__('Products', 'smartpay')}</span>
                            <span>{__('New Product', 'smartpay')}</span>
                        </p>
                    </div>
                    <div className="sp-page-head__actions">
                        <button
                            type="button"
                            className="sp-btn sp-btn--primary"
                            onClick={createProduct}
                        >
                            {__('Publish', 'smartpay')}
                        </button>
                    </div>
                </div>

                <div className="sp-filter-tabs" style={{ marginBottom: 20 }}>
                    {TABS.map((tab) => (
                        <button
                            key={tab.key}
                            type="button"
                            className={`sp-filter-tab${activeTab === tab.key ? ' sp-filter-tab--active' : ''}`}
                            onClick={() => setActiveTab(tab.key)}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="sp-detail-grid">
                    <div>
                        <ProductForm
                            product={product}
                            setProductData={setProductData}
                            activeTab={activeTab}
                        />
                    </div>

                    <div className="sp-detail-sidebar">
                        <CoverImageCard
                            product={product}
                            setProductData={setProductData}
                        />
                    </div>
                </div>
            </div>
        </>
    )
}
