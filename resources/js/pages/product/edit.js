import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Container, Form, Button, Alert } from 'react-bootstrap'
import { useReducer, useState } from '@wordpress/element'
import { UpdateProduct } from '../../http/product'
import { ProductForm } from './components/form'

const { useCallback, useEffect } = wp.element
const { useSelect, select } = wp.data

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
    const [response, setRespose] = useState({})

    const products = useSelect(
        (select) => select('smartpay/products').getProducts(),
        [productId]
    )

    const getProduct = useCallback(() => {
        let product = select('smartpay/products').getProduct(productId)

        if (product && product.hasOwnProperty('variations')) {
            product = {
                ...product,
                variations: product.variations.map((variation) => {
                    return { ...variation, key: `old-${variation.id}` }
                }),
            }
        }
        setProductData(product)
    }, [productId])

    useEffect(() => {
        getProduct()
    }, [productId, products])

    const Save = () => {
        UpdateProduct(productId, JSON.stringify(product)).then((response) => {
            setRespose({
                type: 'success',
                message: 'Product updated',
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
                                    {__('SmartPay', 'smartpay')}
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
                        {response.message && (
                            <Alert className="mt-3" variant={response.type}>
                                {response.message}
                            </Alert>
                        )}
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
