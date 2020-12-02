import { __ } from '@wordpress/i18n'
import { useHistory } from 'react-router-dom'
import { useState, useReducer } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Save } from '../http/form'
import { FormForm } from './components/Form'

const defaultFormData = {
    title: '',
    body: '',
}

const reducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const CreateForm = () => {
    const [form, setFormData] = useReducer(reducer, defaultFormData)
    const [shouldReset, setShouldReset] = useState(false)
    const history = useHistory()

    const saveForm = () => {
        Save(JSON.stringify(form)).then((response) => {
            setFormData(defaultFormData)
            setShouldReset(true)

            dispatch('smartpay/forms').setForm(response.form)
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
            history.push(`/${response.form.id}/edit`)
        })
    }

    return (
        <FormForm
            form={form}
            saveForm={saveForm}
            shouldReset={shouldReset}
            setFormData={setFormData}
        />
    )
}
