import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="row">
            {attributes.fields.map((field, index) => {
                return (
                    !!field.settings.visible && (
                        <div className="col" key={index}>
                            <div className="form-element">
                                <label for={field.attributes.name}>
                                    {field.settings.label}
                                </label>
                                <input
                                    type="text"
                                    id={field.attributes.name}
                                    name={`smartpay_form[${attributes.attributes.name}][${field.attributes.name}]`}
                                    className="form-control"
                                    placeholder={field.attributes.placeholder}
                                    required={field.attributes.isRequired}
                                    value=""
                                />
                            </div>
                        </div>
                    )
                )
            })}
        </div>

        // <div className="form-element">
        //     <div className="form-row">
        //         <div className="col">
        //             <label for="smartpay_first_name">
        //                 {__('First Name', 'smartpay')}
        //             </label>
        //             <input
        //                 type="text"
        //                 className="form-control"
        //                 id="smartpay_first_name"
        //                 name="smartpay_first_name"
        //             />
        //         </div>

        //         {attributes.showLastName && (
        //             <div className="col">
        //                 <label for="smartpay_last_name">
        //                     {__('Last Name', 'smartpay')}
        //                 </label>
        //                 <input
        //                     type="text"
        //                     className="form-control"
        //                     id="smartpay_last_name"
        //                     name="smartpay_last_name"
        //                 />
        //             </div>
        //         )}
        //     </div>
        // </div>
    )
}
