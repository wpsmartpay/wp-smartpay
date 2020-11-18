import React from 'react'

const { __ } = wp.i18n
const { SelectControl } = wp.components

class SelectProduct extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <SelectControl
                className={this.props.class}
                value={this.props.productId}
                onChange={this.props.onSetId}
                options={this.props.productOptions}
            />
        )
    }
}

export default SelectProduct
