import {
    __experimentalInputControl as InputControl,
    Flex,
    FlexBlock,
    PanelBody,
    ToggleControl,
} from '@wordpress/components'

import { InspectorControls } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    const onShowLastNameChange = (show) => {
        setAttributes({ showLastName: show })
    }

    return (
        <>
            <div className="form-element">
                <Flex>
                    <FlexBlock>
                        <InputControl
                            name="first_name"
                            label={__('First Name', 'smartpay')}
                        />
                    </FlexBlock>
                    {attributes.showLastName && (
                        <FlexBlock>
                            <InputControl
                                name="last_name"
                                label={__('Last Name', 'smartpay')}
                            />
                        </FlexBlock>
                    )}
                </Flex>
            </div>

            <InspectorControls>
                <PanelBody
                    title={__('Settings', 'smartpay')}
                    initialOpen={true}
                >
                    <ToggleControl
                        label={__('Show last name fields', 'smartpay')}
                        checked={attributes.showLastName}
                        value={true}
                        onChange={onShowLastNameChange}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
