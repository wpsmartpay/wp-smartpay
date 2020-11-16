import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Container, Alert, Button } from 'react-bootstrap'
import { useEffect, useState, useReducer } from '@wordpress/element'
import { useSelect, dispatch } from '@wordpress/data'

import { Update } from '../http/form'
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

export const EditForm = () => {
    const { formId } = useParams()

    const [form, setformData] = useReducer(reducer, defaultFormData)
    const [response, setResponse] = useState({})

    const formData = useSelect(
        (select) => select('smartpay/forms').getForm(formId),
        [formId]
    )

    useEffect(() => {
        setformData(formData)
    }, [formId, formData])

    const updateForm = () => {
        Update(formId, JSON.stringify(form)).then((response) => {
            dispatch('smartpay/forms').updateForm(form)

            setResponse({
                type: 'success',
                message: __(response.message, 'smartpay'),
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
            >
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Create New Form', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                onClick={updateForm}
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container style={{ marginTop: '80px' }}>
                {response.message && (
                    <Alert className="mt-3" variant={response.type}>
                        {response.message}
                    </Alert>
                )}

                <FormForm form={form} setformData={setformData} />
            </Container>
        </>
    )
}
