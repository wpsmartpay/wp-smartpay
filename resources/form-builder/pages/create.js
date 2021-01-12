import { __ } from '@wordpress/i18n'
import { useHistory } from 'react-router-dom'
import { useState, useReducer } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Save } from '../http/form'
import { FormForm } from './components/Form'
import { smartpayhooks } from './index'

const defaultFormData = {
    title: 'Untitled Form',
    amounts: [
        {
            key: '1234',
            label: '',
            amount: '',
            payment_type: 'onetime'
        },
    ],
    body: `<!-- wp:smartpay-form/name -->
    <div class="wp-block-smartpay-form-name form-element row"><div class="col"><label for="first_name">First Name</label><input type="text" id="first_name" name="smartpay_form[name][first_name]" class="form-control" placeholder="First Name" required value=""/></div><div class="col"><label for="last_name">Last Name</label><input type="text" id="last_name" name="smartpay_form[name][last_name]" class="form-control" placeholder="Last Name" value=""/></div></div>
    <!-- /wp:smartpay-form/name -->

    <!-- wp:smartpay-form/email -->
    <div class="wp-block-smartpay-form-email form-element"><label for="email">Email</label><input type="email" class="form-control" id="email" name="smartpay_form[email]" placeholder="Email" required/></div>
    <!-- /wp:smartpay-form/email -->`,
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

export const CreateForm = () => {
    window.smartPayHooks.applyFilters( 'smartpay.form.price.defaultValue', defaultFormData );


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
