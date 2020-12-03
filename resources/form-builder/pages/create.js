import { __ } from '@wordpress/i18n'
import { useHistory } from 'react-router-dom'
import { useState, useReducer } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Save } from '../http/form'
import { FormForm } from './components/Form'

const defaultFormData = {
    title: 'Untitled Form',
    body: `<!-- wp:smartpay-form/name -->
        <div class="wp-block-smartpay-form-name form-element"><div class="form-row"><div class="col"><label for="smartpay_first_name">First Name</label><input type="text" class="form-control" id="smartpay_first_name" name="smartpay_first_name"/></div><div class="col"><label for="smartpay_last_name">Last Name</label><input type="text" class="form-control" id="smartpay_last_name" name="smartpay_last_name"/></div></div></div>
        <!-- /wp:smartpay-form/name -->

        <!-- wp:smartpay-form/email -->
        <div class="wp-block-smartpay-form-email form-element"><div class="form-group"><label for="smartpay_email">Email</label><input type="email" class="form-control" id="smartpay_email" name="smartpay_email"/></div></div>
        <!-- /wp:smartpay-form/email -->`,
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
            onSubmit={saveForm}
            shouldReset={shouldReset}
            setFormData={setFormData}
        />
    )
}
