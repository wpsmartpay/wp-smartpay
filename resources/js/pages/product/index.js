import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Nav, Form, Button } from 'react-bootstrap'

const { useEffect } = wp.element;
const { useSelect, dispatch } = wp.data;

export const ProductList = () => {
    useEffect(() => {
        dispatch('smartpay/products').getProducts();
    }, []);

    const addNewProduct = () => dispatch('smartpay/products').addProduct(Math.trunc(Math.random() * 100));

    const products = useSelect(select => select("smartpay/products").getProducts());

    { console.log(products) }
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay | Product', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                to="/product/create"
                            >
                                Create
                            </Link>
                            <button
                                onClick={addNewProduct}
                                className="btn btn-primary btn-sm text-decoration-none"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                    {console.log(products)}
                    {/* {products.map((p, i) => <p key={i}>{p.title}</p>)} */}
                </Container>
            </div>
        </>
    )
};
