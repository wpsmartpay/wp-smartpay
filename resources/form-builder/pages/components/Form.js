import { Button, TextControl, TabPanel } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import { parse } from '@wordpress/blocks'

import { Alert } from '../../components/Alert'
import { FormBuilder } from './FormBuilder'
import { FormOptionTab } from './FormOptionTab'
import { FormPricingTab } from './FormPricingTab'

export const FormForm = ({ form, onSubmit, setFormData, shouldReset = false }) => {
    const checkRequiredBlocks = (blocks) => {
        const requiredBlocks = { name: 0, email: 0 }

        if (blocks.length) {
            blocks.map((block) => {
                if ('smartpay-form/name' === block.name) {
                    requiredBlocks.name = requiredBlocks.name + 1
                } else if ('smartpay-form/email' === block.name) {
                    requiredBlocks.email = requiredBlocks.email + 1
                }
            })
        }

        return requiredBlocks
    }

    const saveForm = () => {
        const blocks = parse(form.body)
        const requiredBlocks = checkRequiredBlocks(blocks)

        if (requiredBlocks.name < 1) {
            Alert('You must have one name field', 'error')
        } else if (requiredBlocks.name > 1) {
            Alert('Your form contains more than one name field', 'error')
        } else if (requiredBlocks.email < 1) {
            Alert('You must have one email field', 'error')
        } else if (requiredBlocks.email > 1) {
            Alert('Your form contains more than one email field', 'error')
        } else {
            onSubmit()
        }
    }

    const tabs = [
        {
            name: 'builder',
            title: __('Builder', 'smartpay'),
            className: 'smartpay-tab smartpay-tab--builder',
        },
        {
            name: 'pricing',
            title: __('Pricing', 'smartpay'),
            className: 'smartpay-tab smartpay-tab--pricing',
        },
        {
            name: 'options',
            title: __('Options', 'smartpay'),
            className: 'smartpay-tab smartpay-tab--options',
        },
    ]

    return (
        <>
            {/* Top toolbar */}
            <div className="smartpay-form-header">
                <div className="smartpay-form-header__inner">
                    <h2 className="smartpay-form-header__title">
                        {form.id
                            ? __('Edit Form', 'smartpay')
                            : __('Create Form', 'smartpay')}
                    </h2>
                    <div className="smartpay-form-header__actions">
                        {form.id && (
                            <TextControl
                                value={`[smartpay_form id="${form.id}"]`}
                                readOnly
                                className="smartpay-form-header__shortcode"
                            />
                        )}
                        {form.id && form.extra?.form_preview_page_permalink && (
                            <Button
                                variant="tertiary"
                                href={form.extra.form_preview_page_permalink}
                                target="_blank"
                                className="smartpay-form-header__preview-btn"
                            >
                                {__('Preview', 'smartpay')}
                            </Button>
                        )}
                        <Button
                            variant="primary"
                            onClick={saveForm}
                            className="smartpay-form-header__save-btn"
                        >
                            {form.id
                                ? __('Save', 'smartpay')
                                : __('Publish', 'smartpay')}
                        </Button>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="smartpay-form-content">
                <div className="smartpay-form-content__inner">
                    <TextControl
                        value={form.title || ''}
                        onChange={(value) => {
                            setFormData({ title: value })
                        }}
                        placeholder={__('Your awesome form title here', 'smartpay')}
                        className="smartpay-form-content__title"
                        __nextHasNoMarginBottom
                    />

                    <TabPanel
                        className="smartpay-form-tabs"
                        activeClass="is-active"
                        tabs={tabs}
                    >
                        {(tab) => {
                            switch (tab.name) {
                                case 'builder':
                                    return (
                                        <div className="smartpay-form-tab-content">
                                            <FormBuilder
                                                form={form}
                                                setFormData={setFormData}
                                                shouldReset={shouldReset}
                                            />
                                        </div>
                                    )
                                case 'pricing':
                                    return (
                                        <div className="smartpay-form-tab-content">
                                            <FormPricingTab
                                                form={form}
                                                setFormData={setFormData}
                                            />
                                        </div>
                                    )
                                case 'options':
                                    return (
                                        <div className="smartpay-form-tab-content">
                                            <FormOptionTab
                                                form={form}
                                                setFormData={setFormData}
                                            />
                                        </div>
                                    )
                                default:
                                    return null
                            }
                        }}
                    </TabPanel>
                </div>
            </div>
        </>
    )
}
