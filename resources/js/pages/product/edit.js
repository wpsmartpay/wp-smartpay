import { __ } from '@wordpress/i18n'
import { Link, useParams } from 'react-router-dom'
import { useReducer, useEffect, useState } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2'
import { UpdateProduct } from '../../http/product'
import { ProductForm, CoverImageCard } from './components/form'
import { productDefaultData } from '../../utils/constant'
import { DeprecatedBanner } from '../../components/DeprecatedBanner'

const { useSelect, dispatch } = wp.data

const { Header } = window.WPSmartPayUI

const reducer = (state, data) => ({ ...state, ...data })

const TABS = [
    { key: 'details',  label: __('Product Details', 'smartpay') },
    { key: 'pricing',  label: __('Pricing',         'smartpay') },
    { key: 'checkout', label: __('Checkout',         'smartpay') },
]

export const EditProduct = () => {
    const { productId } = useParams()
    const [product, setProductData] = useReducer(reducer, productDefaultData)
    const [activeTab, setActiveTab] = useState('details')

    const productData = useSelect(
        (select) => select('smartpay/products').getProduct(productId),
        [productId]
    )

    useEffect(() => {
        if (productData && productData.hasOwnProperty('variations')) {
            setProductData({
                ...productData,
                variations: productData.variations.map((variation) => ({
                    ...variation,
                    key: `old-${variation.id}`,
                })),
            })
        }
    }, [productData])

    const Save = () => {
        UpdateProduct(productId, JSON.stringify(product)).then((response) => {
            dispatch('smartpay/products').updateProduct(response.product)
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
        })
    }

    return (
        <>
            {product && (
                <>
                    <Header
                        title={__('Edit Product', 'smartpay')}
                        subtitle={product.id ? `#${product.id}` : ''}
                    />
                    <div className="sp-layout">

                        <DeprecatedBanner feature={__('Products', 'smartpay')} />

                        {/* Page action bar */}
                        <div className="sp-page-head">
                            <div>
                                <p className="sp-page-head__breadcrumb">
                                    <span>{__('Products', 'smartpay')}</span>
                                    <span>
                                        {__('Edit Product', 'smartpay')}
                                        {product.id ? ` #${product.id}` : ''}
                                    </span>
                                </p>
                            </div>
                            <div className="sp-page-head__actions">
                                {product.id && product.extra?.product_preview_page_permalink && (
                                    <a
                                        href={product.extra.product_preview_page_permalink}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="sp-btn sp-btn--outline"
                                        style={{ textDecoration: 'none' }}
                                    >
                                        {__('Preview', 'smartpay')} ↗
                                    </a>
                                )}
                                <button
                                    type="button"
                                    className="sp-btn sp-btn--primary"
                                    onClick={Save}
                                >
                                    {__('Save Changes', 'smartpay')}
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

                                {product.id && (
                                    <div className="sp-detail-card">
                                        <div className="sp-detail-card__header">
                                            <span className="sp-detail-card__title">
                                                {__('Shortcode', 'smartpay')}
                                            </span>
                                        </div>
                                        <div className="sp-detail-card__body">
                                            <code
                                                style={{
                                                    display: 'block',
                                                    background: 'var(--sp-surface-muted)',
                                                    border: '1px solid var(--sp-border)',
                                                    borderRadius: 'var(--sp-radius-sm)',
                                                    padding: '8px 12px',
                                                    fontSize: 12,
                                                    fontFamily: 'monospace',
                                                    color: 'var(--sp-text-muted)',
                                                    wordBreak: 'break-all',
                                                }}
                                            >
                                                {`[smartpay_product id="${product.id}"]`}
                                            </code>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    )
}
