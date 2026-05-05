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
    Card,
    CardContent,
    Button,
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

                    <div className="p-4 max-w-7xl mx-auto">
                        <Card className="mb-4">
                            <CardContent>
                                <div className="flex items-center justify-between flex-wrap gap-3">
                                    <span className="text-sm text-gray-500 font-mono bg-muted px-2 py-1 rounded">
                                        [smartpay_product id="{product.id}"]
                                    </span>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            variant="default"
                                            size="sm"
                                            onClick={Save}
                                        >
                                            {__('Save', 'smartpay')}
                                        </Button>
                                        {product.id && product.extra?.product_preview_page_permalink && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                href={product.extra.product_preview_page_permalink}
                                                target="_blank"
                                            >
                                                {__('Preview', 'smartpay')}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

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