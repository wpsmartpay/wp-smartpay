import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { useReducer, useEffect } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { UpdateProduct } from '../../http/product'
import { ProductForm } from './components/form'
import { productDefaultData } from '../../utils/constant'

const { useSelect, dispatch } = wp.data

const {
    Header,
} = window.WPSmartPayUI

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const EditProduct = () => {
    const { productId } = useParams()
    const [product, setProductData] = useReducer(reducer, productDefaultData)

    const productData = useSelect(
        (select) => select('smartpay/products').getProduct(productId),
        [productId]
    )

    useEffect(() => {
        if (productData && productData.hasOwnProperty('variations')) {
            setProductData({
                ...productData,
                variations: productData.variations.map((variation) => {
                    return { ...variation, key: `old-${variation.id}` }
                }),
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
                showClass: {
                    popup: 'swal2-noanimation',
                },
                hideClass: {
                    popup: '',
                },
            })
        })
    }

    return (
        <>
            {product && (
                <>
                    <Header
                        title={__('Edit Product', 'smartpay')}
                        subtitle={`#${product.id}`}
                    />

                    <div className="sp-layout">
                        <div className="sp-detail-card" style={{ marginBottom: 16 }}>
                            <div className="sp-detail-card__body">
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10 }}>
                                    <code style={{ background: 'var(--sp-surface-muted)', border: '1px solid var(--sp-border)', borderRadius: 'var(--sp-radius-sm)', padding: '4px 10px', fontSize: 12, fontFamily: 'monospace', color: 'var(--sp-text-muted)' }}>
                                        {`[smartpay_product id="${product.id}"]`}
                                    </code>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                        <button type="button" className="sp-btn sp-btn--primary" onClick={Save}>
                                            {__('Save', 'smartpay')}
                                        </button>
                                        {product.id && product.extra?.product_preview_page_permalink && (
                                            <a href={product.extra.product_preview_page_permalink} target="_blank" rel="noopener noreferrer" className="sp-btn sp-btn--outline" style={{ textDecoration: 'none' }}>
                                                {__('Preview', 'smartpay')}
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ProductForm
                            product={product}
                            setProductData={setProductData}
                        />
                    </div>
                </>
            )}
        </>
    )
}