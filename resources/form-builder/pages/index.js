import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'
import { Button } from '@wordpress/components'
import { useState, useEffect } from '@wordpress/element'
import { useSelect, dispatch } from '@wordpress/data'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { Delete } from '../http/form'

import { createHooks } from '@wordpress/hooks'

window.SMARTPAY_FORM_HOOKS = createHooks()

export const FormList = () => {
    const [forms, setForms] = useState([])

    const formsData = useSelect(
        (select) => select('smartpay/forms').getForms(),
        []
    )

    useEffect(() => {
        setForms(formsData)
    }, [formsData])

    const deleteForm = (formId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Delete(formId).then((response) => {
                    dispatch('smartpay/forms').deleteForm(formId)
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
        })
    }

    return (
        <>
            <div className="smartpay-form-header">
                <div className="smartpay-form-header__inner">
                    <h2 className="smartpay-form-header__title">
                        {__('Forms', 'smartpay')}
                    </h2>
                    <div className="smartpay-form-header__actions">
                        <Link
                            className="components-button is-primary smartpay-form-header__add-btn"
                            to="create"
                        >
                            {__('Add new', 'smartpay')}
                        </Link>
                    </div>
                </div>
            </div>

            <div className="smartpay-form-list">
                <div className="smartpay-form-list__inner">
                    <table className="smartpay-table">
                        <thead>
                            <tr>
                                <th className="smartpay-table__col--title">
                                    <strong>{__('Title', 'smartpay')}</strong>
                                </th>
                                <th className="smartpay-table__col--date">
                                    {__('Date', 'smartpay')}
                                </th>
                                <th className="smartpay-table__col--actions">
                                    {__('Actions', 'smartpay')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {!forms.length && (
                                <tr>
                                    <td className="smartpay-table__empty" colSpan="3">
                                        {__('No form found.', 'smartpay')}
                                    </td>
                                </tr>
                            )}

                            {forms.map((form, index) => {
                                return (
                                    <tr key={index}>
                                        <td>{form.title || ''}</td>
                                        <td>{form.updated_at || ''}</td>
                                        <td className="smartpay-table__col--actions">
                                            {form?.extra?.form_preview_page_permalink && (
                                                <Button
                                                    variant="link"
                                                    href={form.extra.form_preview_page_permalink}
                                                    target="_blank"
                                                >
                                                    {__('Preview', 'smartpay')}
                                                </Button>
                                            )}
                                            <Link
                                                className="components-button is-link"
                                                to={`/${form.id}/edit`}
                                            >
                                                {__('Edit', 'smartpay')}
                                            </Link>
                                            <Button
                                                variant="link"
                                                isDestructive
                                                onClick={() => deleteForm(form.id)}
                                            >
                                                {__('Delete', 'smartpay')}
                                            </Button>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    )
}
