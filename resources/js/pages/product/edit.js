import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { useReducer, useEffect } from '@wordpress/element'
import { Container, Form, Button, Alert } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { UpdateProduct } from '../../http/product'
import { ProductForm } from './components/form'

const { useSelect, dispatch } = wp.data

const defaultProduct = {
    title: '',
    covers: [],
    description: '',
    variations: [],
    base_price: '',
    sale_price: '',
    files: [],
}

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const EditProduct = () => {
    const { productId } = useParams()
    const [product, setProductData] = useReducer(reducer, defaultProduct)

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
                    <div className="text-black bg-white border-bottom d-fixed">
                        <Container>
                            <div className="d-flex align-items-center justify-content-between">
                                <h2 className="text-black">
                                    {__('Edit Product', 'smartpay')}
                                </h2>
                                <div className="ml-auto">
                                    <div className="d-flex flex-row">
                                        <Form.Control
                                            size="sm"
                                            type="text"
                                            value={`[smartpay_product id="${product.id}"]`}
                                            readOnly
                                            className="mr-2"
                                        />
                                        <Button
                                            type="button"
                                            className="btn btn-sm btn-primary px-3"
                                            onClick={Save}
                                        >
                                            {__('Save', 'smartpay')}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </Container>
                    </div>

                    <Container>
                        <ProductForm
                            product={product}
                            setProductData={setProductData}
                        />
                    </Container>
                </>
            )}
        </>
    )
}
