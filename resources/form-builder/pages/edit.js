import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { useEffect, useReducer } from '@wordpress/element'
import { useSelect, dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Update } from '../http/form'
import { FormForm } from './components/Form'

const defaultFormData = {
    title: '',
    amounts: [],
    body: '',
    fields: [],
    settings: {
        allowCustomAmount: false,
        customAmountLabel: __('Pay what you want', 'smartpay'),
    },
}

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const EditForm = () => {
    const { formId } = useParams()

    const [form, setFormData] = useReducer(reducer, defaultFormData)

    const formData = useSelect(
        (select) => select('smartpay/forms').getForm(formId),
        [formId]
    )

    useEffect(() => {
        setFormData(formData)
    }, [formId, formData])

    const updateForm = () => {
        Update(formId, JSON.stringify(form)).then((response) => {
            dispatch('smartpay/forms').updateForm(form)

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

    return (
        <>
            <div
                className="text-black bg-white border-bottom"
                style={{
                    position: 'fixed',
                    left: '160px',
                    right: 0,
                    top: '32px',
                    zIndex: 99,
                }}
            ></div>

            <FormForm
                onSubmit={updateForm}
                form={form}
                setFormData={setFormData}
            />
        </>
    )
}
