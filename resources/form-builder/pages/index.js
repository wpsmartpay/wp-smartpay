import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Container, Table, Button, Alert } from 'react-bootstrap'
import { useState, useEffect } from '@wordpress/element'
import { useSelect, dispatch } from '@wordpress/data'
import { Delete } from '../http/form'

export const FormList = () => {
    const [forms, setForms] = useState([])
    const [response, setResponse] = useState({})

    const formsData = useSelect(
        (select) => select('smartpay/forms').getForms(),
        []
    )

    useEffect(() => {
        setForms(formsData)
    }, [formsData])

    const deleteForm = (formId) => {
        Delete(formId).then((response) => {
            dispatch('smartpay/forms').deleteForm(formId)
            setResponse({
                type: 'success',
                message: __(response.message, 'smartpay'),
            })
        })
    }

    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Forms', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Link
                                role="button"
                                className="btn btn-primary btn-sm text-decoration-none px-3"
                                to="create"
                            >
                                {__('Add new', 'smartpay')}
                            </Link>
                        </div>
                    </div>
                </Container>
            </div>

            <Container className="mt-3">
                {response.message && (
                    <Alert className="mt-3" variant={response.type}>
                        {response.message}
                    </Alert>
                )}

                <div className="bg-white">
                    <Table className="table">
                        <thead>
                            <tr className="text-white bg-dark">
                                <th className="w-75 text-left">
                                    <strong>{__('Title', 'smartpay')}</strong>
                                </th>
                                <th className="w-25 text-right">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {forms.map((form, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{form.title || ''}</td>
                                        <td className="text-right">
                                            <Link
                                                className="btn-sm p-0 mr-2"
                                                to={`/${form.id}/edit`}
                                            >
                                                {__('Edit', 'smartpay')}
                                            </Link>
                                            <Button
                                                className="btn-sm p-0"
                                                onClick={() =>
                                                    deleteForm(form.id)
                                                }
                                                variant="link"
                                            >
                                                {__('Delete', 'smartpay')}
                                            </Button>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </Table>
                </div>
            </Container>
        </>
    )
}
