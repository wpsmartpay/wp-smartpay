import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'

const { useEffect } = wp.element
const { useSelect, dispatch } = wp.data

export const CouponList = () => {
    useEffect(() => {
        dispatch('smartpay/coupons').getCoupons()
    }, [])

    const coupons = useSelect((select) =>
        select('smartpay/coupons').getCoupons()
    )

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Coupons', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                to="/coupons/create"
                            >
                                {__('Create', 'smartpay')}
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
                            {coupons.map((coupons) => {
                                return (
                                    <tr key={coupons.id}>
                                        <td>{coupons.title || ''}</td>
                                        <td className="text-right">
                                            <Link
                                                className="btn-sm p-0 mr-2"
                                                to={`/coupons/${coupons.id}/edit`}
                                            >
                                                {__('Edit', 'smartpay')}
                                            </Link>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteProduct(coupons)
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
