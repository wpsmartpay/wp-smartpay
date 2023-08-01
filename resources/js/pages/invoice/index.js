import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

export const Invoices = () => {
    const [invoices, setInvoices] = useState([])

    const invoiceList = useSelect((select) =>
        select('smartpay/invoices').getInvoices()
    )

    useEffect(() => {
        setInvoices(invoiceList)
    }, [invoiceList])

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Invoices', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                                to="/invoices/create"
                            >
                                {__('Create Invoice', 'smartpay')}
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                <div className="bg-white">
                    <Table className="table">
                        <thead>
                        <tr className="bg-light">
                            <th className="w-5 text-left">
                                {__('ID', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Customer', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Type', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Amount', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Date', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Status', 'smartpay')}
                            </th>
                            <th className="w-30 text-left">
                                {__('Actions', 'smartpay')}
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </Table>
                </div>
            </Container>
        </>
    )
}
