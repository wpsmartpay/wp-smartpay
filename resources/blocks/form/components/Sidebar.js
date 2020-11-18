import React from 'react'

const { __ } = wp.i18n
const { InspectorControls } = wp.blockEditor
const { SelectControl, TextControl, CardBody } = wp.components

class Sidebar extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <InspectorControls>
                <CardBody>
                    <SelectControl
                        label={__('Shortcode behavior', 'smartpay')}
                        value={this.props.attributes.behavior}
                        onChange={this.props.onSetBehavior}
                        options={[
                            {
                                value: null,
                                label: __('Select a behavior', 'smartpay'),
                                disabled: true,
                            },
                            { value: 'popup', label: __('Popup', 'smartpay') },
                            {
                                value: 'embedded',
                                label: __('Embedded', 'smartpay'),
                            },
                        ]}
                    />

                    {'popup' === this.props.attributes.behavior && (
                        <TextControl
                            label={__('Button label', 'smartpay')}
                            value={this.props.attributes.label}
                            onChange={this.props.onSetLabel}
                        />
                    )}
                </CardBody>
            </InspectorControls>
        )
    }
}

export default Sidebar
