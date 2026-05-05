import { __ } from '@wordpress/i18n'
import { Button } from 'react-bootstrap'
import { useNavigate } from 'react-router-dom'
import { useReducer } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { SaveProduct } from '../../http/product'
import { ProductForm } from './components/form'
import { productDefaultData } from '../../utils/constant'

const { dispatch } = wp.data

const {
    Header,
    Card,
    CardContent,
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

            <div className="p-4 max-w-7xl mx-auto">
                <Card className="mb-4">
                    <CardContent>
                        <div className="flex justify-end">
                            <Button
                                variant="default"
                                size="sm"
                                onClick={createProduct}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <ProductForm
                    product={product}
                    setProductData={setProductData}
                />
            </div>
        </>
    )
}