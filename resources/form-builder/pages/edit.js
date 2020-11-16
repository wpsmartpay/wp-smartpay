import { __ } from '@wordpress/i18n'
import { useParams } from 'react-router-dom'
import { Container, Alert, Button } from 'react-bootstrap'
import {
    useCallback,
    useEffect,
    useState,
    useReducer,
} from '@wordpress/element'

import { useSelect, select } from '@wordpress/data'
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
    const [response, setRespose] = useState({})
    const [shouldReset, setShouldReset] = useState(false)

    const forms = useSelect((select) => select('smartpay/forms').getForms(), [
        formId,
    ])

    const getForm = useCallback(() => {
        let form = select('smartpay/forms').getForm(formId)
        setformData(form)
    }, [formId])

    useEffect(() => {
        getForm()
    }, [formId, forms])

    const UpdateForm = () => {
        Update(formId, JSON.stringify(form)).then((response) => {
            // TODO: Set data to store
            // TODO: Toggle reset value
            setRespose({
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
                                onClick={UpdateForm}
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
