import { __ } from '@wordpress/i18n'
import { Container, Button, Alert } from 'react-bootstrap'
import { useReducer, useState } from '@wordpress/element'
import { SaveProduct } from '../../http/product'
import { ProductForm } from './components/form'

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

export const CreateProduct = () => {
    const [response, setRespose] = useState({})

    const [product, setProductData] = useReducer(reducer, defaultProduct)

    const createProduct = () => {
        SaveProduct(
            JSON.stringify({
                ...product,
                description: tinyMCE.activeEditor.getContent(),
            })
        ).then((response) => {
            setProductData(defaultProduct)

            // TODO: Set product to store
            tinymce.get('description').setContent('')
            setRespose({
                type: 'success',
                message: 'Product created successfully',
            })
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
    )
}
