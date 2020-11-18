import React from 'react'

const { __ } = wp.i18n
const { SelectControl } = wp.components

class SelectForm extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <SelectControl
                className={this.props.class}
                value={this.props.formId}
                onChange={this.props.onSetId}
                options={this.props.formOptions}
            />
        )
    }
}

export default SelectForm
