import { FormSettings } from "./FormSettings";

export const FormOptionTab = ({ form, setFormData }) => {
    const formOptionSections = window.SMARTPAY_FORM_HOOKS.applyFilters(
        'smartpay.form.option.sections',
        [
            {
                namespace: 'settings',
                body: <FormSettings form={form} setFormData={setFormData} />,
            },
        ],
        form,
        setFormData
    )

    return formOptionSections?.map((section) => {
        return (
            <div className="mb-3" key={section.namespace}>
                {section.body}
            </div>
        )
    })
}
