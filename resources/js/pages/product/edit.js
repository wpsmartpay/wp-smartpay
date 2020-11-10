import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Container, Form, Button, Alert } from 'react-bootstrap'
import { useReducer, useState } from '@wordpress/element'
import { SaveProduct } from '../../http/product'
import { ProductForm } from './components/form'

const { useEffect } = wp.element
const { useSelect, select, dispatch } = wp.data

export const EditProduct = () => {
    const { productId } = useParams()

    const product = useSelect(
        (select) => select('smartpay/products').getProduct(productId),
        [productId]
    )

    return <>OK</>
}
