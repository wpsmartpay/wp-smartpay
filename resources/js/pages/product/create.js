import { __ } from '@wordpress/i18n'
import { useNavigate } from 'react-router-dom'
import { useReducer } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { SaveProduct } from '../../http/product'
import { ProductForm } from './components/form'
import { productDefaultData } from '../../utils/constant'

const { dispatch } = wp.data

const {
    Header,
} = window.WPSmartPayUI

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const CreateProduct = () => {
    const [product, setProductData] = useReducer(reducer, productDefaultData)
    const navigate = useNavigate()

    const createProduct = () => {
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
                    showClass: {
                        popup: 'swal2-noanimation',
                    },
                    hideClass: {
                        popup: '',
                    },
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
            <Header
                title={__('Create Product', 'smartpay')}
            />

            <div className="sp-layout">
                <div className="sp-detail-card" style={{ marginBottom: 16 }}>
                    <div className="sp-detail-card__body">
                        <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                            <button type="button" className="sp-btn sp-btn--primary" onClick={createProduct}>
                                {__('Publish', 'smartpay')}
                            </button>
                        </div>
                    </div>
                </div>

                <ProductForm
                    product={product}
                    setProductData={setProductData}
                />
            </div>
        </>
    )
}