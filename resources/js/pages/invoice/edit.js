import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import {ProductForm} from "../product/components/form";
const { useEffect, useState } = wp.element
const { useSelect, dispatch } = wp.data

export const EditInvoice = () => {

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Edit Invoice', 'smartpay')}
                        </h2>
                    </div>
                </Container>
            </div>

            <Container>
                <div className="text-center">
                    Edit Invoice form
                </div>
            </Container>
        </>
    )
}
