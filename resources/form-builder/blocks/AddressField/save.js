const chunk = (arr, size) => {
    return Array.from({ length: Math.ceil(arr.length / size) }, (v, i) =>
        arr.slice(i * size, i * size + size)
    )
}

export const save = ({ attributes }) => {
    return (
        <div>
            {chunk(attributes.fields, 2).map((items, index) => {
                return (
                    <div className="row" key={index}>
                        {items.map((item, i) => {
                            return (
                                !!item.settings.visible && (
                                    <div className="col" key={i}>
                                        <div className="form-element">
                                            <label for={item.attributes.name}>
                                                {item.settings.label}
                                            </label>
                                            <input
                                                type="text"
                                                id={item.attributes.name}
                                                name={`smartpay_form[${attributes.attributes.name}][${item.attributes.name}]`}
                                                className="form-control"
                                                placeholder={
                                                    item.attributes.placeholder
                                                }
                                                required={
                                                    item.attributes.isRequired
                                                }
                                                value=""
                                            />
                                        </div>
                                    </div>
                                )
                            )
                        })}
                    </div>
                )
            })}
        </div>
    )
}
