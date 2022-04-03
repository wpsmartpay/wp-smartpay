import { FormAmounts } from './FormAmounts'

export const FormPricingTab = ({ form, setFormData }) => {
    const formPriceSections = window.SMARTPAY_FORM_HOOKS.applyFilters(
        'smartpay.form.option.sections',
        [
            {
                namespace: 'amounts',
                body: <FormAmounts form={form} setFormData={setFormData} />,
            },
        ],
        form,
        setFormData
    )

    return formPriceSections?.map((section) => {
        return (
            <div className="mb-3" key={section.namespace}>
                {section.body}
            </div>
        )
    })
}
