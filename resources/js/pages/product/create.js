import { __ } from '@wordpress/i18n'
import { Container, Button, Alert } from 'react-bootstrap'
import { useHistory } from 'react-router-dom'
import { useReducer } from '@wordpress/element'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { SaveProduct } from '../../http/product'
import { ProductForm } from './components/form'
import { productDefaultData } from '../../utils/constant'

const { dispatch } = wp.data

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const CreateProduct = () => {
    const [product, setProductData] = useReducer(reducer, productDefaultData)
    const history = useHistory()

    const createProduct = () => {
        SaveProduct(JSON.stringify(product)).then((response) => {
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

            // history.push(`/products/${response.product.id}/edit`)
        })
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Create Product', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-sm btn-primary px-3"
                                onClick={createProduct}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
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
    )
}
