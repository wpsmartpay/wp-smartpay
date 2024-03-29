import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button, Alert } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteProduct } from '../../http/product'
import { useEffect, useState } from '@wordpress/element'
import { createHooks } from '@wordpress/hooks'
const { useSelect, dispatch } = wp.data

window.SMARTPAY_PRODUCT_HOOKS = createHooks()

export const ProductList = () => {
    const [products, setProducts] = useState([])

    const productList = useSelect((select) => select('smartpay/products').getProducts())

    useEffect(() => {
        setProducts(productList)
    }, [productList])

    const deleteProduct = (productId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                DeleteProduct(productId).then((response) => {
                    dispatch('smartpay/products').deleteProduct(productId)
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
        })
    }

    let uniqueProducts = products?.filter((v, i, a) => a.findIndex(t => (t.id == v.id)) === i)

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
                    <Table>
                        <thead>
                            <tr className="bg-light">
                                <th className="w-50 text-left">
                                    <strong>{__('Title', 'smartpay')}</strong>
                                </th>
                                <th className="text-left">
                                    {__('Date', 'smartpay')}
                                </th>
                                <th className="text-right">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {!uniqueProducts.length && (
                                <tr>
                                    <td className="text-center" colSpan="3">
                                        {__('No product found.', 'smartpay')}
                                    </td>
                                </tr>
                            )}

                            {uniqueProducts.map((product, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{product.title || ''}</td>
                                        <td>{product.created_at || ''}</td>
                                        <td className="text-right">
                                            {product?.extra
                                                ?.product_preview_page_permalink && (
                                                    <Button
                                                        variant="link"
                                                        href={
                                                            product.extra
                                                                .product_preview_page_permalink
                                                        }
                                                        target="_blank"
                                                        className="btn btn-sm text-decoration-none p-0 mr-2"
                                                    >
                                                        {__('Preview', 'smartpay')}
                                                    </Button>
                                                )}
                                            <Link
                                                className="btn btn-sm btn-link p-0 mr-2"
                                                to={`/products/${product.id}/edit`}
                                            >
                                                {__('Edit', 'smartpay')}
                                            </Link>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteProduct(product.id)
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
