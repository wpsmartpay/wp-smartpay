import { __ } from '@wordpress/i18n'
import { useHistory } from 'react-router-dom'
import { useState, useReducer } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Save } from '../http/form'
import { FormForm } from './components/Form'

const defaultFormData = {
    title: 'Untitled Form',
    body: `<!-- wp:smartpay-form/name {"fields":[{"attributes":{"name":"first_name","value":"","class":"","placeholder":"First Name","isRequired":true},"settings":{"visible":true,"label":"First Name","helpMessage":""},"validationRules":[{"required":{"value":true,"message":"This field is required"}}]},{"attributes":{"name":"middle_name","value":"","class":"","placeholder":"Middle Name","isRequired":false},"settings":{"visible":true,"label":"Middle Name","helpMessage":""},"validationRules":[]},{"attributes":{"name":"last_name","value":"","class":"","placeholder":"Last Name","isRequired":false},"settings":{"visible":true,"label":"Last Name","helpMessage":""},"validationRules":[]}],"className":"form-element"} --><div class="wp-block-smartpay-form-name row form-element"><div class="col"><div class="form-element"><label for="first_name">First Name</label><input type="text" id="first_name" name="smartpay_form[name][first_name]" class="form-control" placeholder="First Name" required value=""/></div></div><div class="col"><div class="form-element"><label for="middle_name">Middle Name</label><input type="text" id="middle_name" name="smartpay_form[name][middle_name]" class="form-control" placeholder="Middle Name" value=""/></div></div><div class="col"><div class="form-element"><label for="last_name">Last Name</label><input type="text" id="last_name" name="smartpay_form[name][last_name]" class="form-control" placeholder="Last Name" value=""/></div></div></div><!-- /wp:smartpay-form/name --><!-- wp:smartpay-form/email --><div class="wp-block-smartpay-form-email form-element"><label for="email">Email</label><input type="email" class="form-control" id="email" name="smartpay_form[email]" placeholder="Email" required/></div><!-- /wp:smartpay-form/email -->`,
    fields: [],
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
