import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

const { useEffect } = wp.element
const { useSelect, dispatch } = wp.data

export const ProductList = () => {
    useEffect(() => {
        dispatch('smartpay/products').getProducts()
    }, [])

    const products = useSelect(select =>
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
                                className="btn btn-primary btn-sm text-decoration-none"
                                to="/products/create"
                            >
                                {__('Create', 'smartpay')}
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>

            <Container>
                <div className="card bg-white ">
                    <Table>
                        <thead>
                            <tr>
                                <th className="d-none">#</th>
                                <th>{__('Title', 'smartpay')}</th>
                                <th>{__('Actions', 'smartpay')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.map(product => {
                                return (
                                    <tr key={product.id}>
                                        <td className="d-none">{product.id}</td>
                                        <td>{product.title || ''}</td>
                                        <td>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteProduct(product)
                                                }
                                                variant="link"
                                            >
                                                {__('Edit', 'smartpay')}
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
