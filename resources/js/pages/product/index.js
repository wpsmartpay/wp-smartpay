import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

const { useEffect } = wp.element
const { useSelect, dispatch } = wp.data

export const ProductList = () => {
    useEffect(() => {
        dispatch('smartpay/products').getProducts()
    }, [])

    const products = useSelect((select) =>
        select('smartpay/products').getProducts()
    )

    const deleteProduct = () => {
        // FIXME
        console.log('Delete product')
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Products', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                                to="/products/create"
                            >
                                {__('Add new', 'smartpay')}
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                            <tr className="text-white bg-dark">
                                <th className="w-75 text-left">
                                    <strong>{__('Title', 'smartpay')}</strong>
                                </th>
                                <th className="w-25 text-right">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.map((product, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{product.title || ''}</td>
                                        <td className="text-right">
                                            <Link
                                                className="btn-sm p-0 mr-2"
                                                to={`/products/${product.id}/edit`}
                                            >
                                                {__('Edit', 'smartpay')}
                                            </Link>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteProduct(product)
                                                }
                                                variant="link"
                                            >
                                                {__('Delete', 'smartpay')}
                                            </Button>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </Table>
                </div>
            </Container>
        </>
    )
}
