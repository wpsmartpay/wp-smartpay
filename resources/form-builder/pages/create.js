import { __ } from '@wordpress/i18n'
import { Container, Button } from 'react-bootstrap'
import { useHistory } from 'react-router-dom'
import { useState, useReducer } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Save } from '../http/form'
import { FormForm } from './components/form'

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
    const [form, setformData] = useReducer(reducer, defaultFormData)
    const [shouldReset, setShouldReset] = useState(false)
    const history = useHistory()

    const saveForm = () => {
        Save(JSON.stringify(form)).then((response) => {
            setformData(defaultFormData)
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
            >
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Create New Form', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                onClick={saveForm}
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container style={{ marginTop: '80px' }}>
                <FormForm
                    form={form}
                    shouldReset={shouldReset}
                    setformData={setformData}
                />
            </Container>
        </>
    )
}
