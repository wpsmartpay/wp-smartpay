import {
    __experimentalInputControl as InputControl,
    Flex,
    FlexBlock,
} from '@wordpress/components'

import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    return (
        <>
            <div className="form-element">
                <Flex>
                    <FlexBlock>
                        <InputControl
                            name="customer_email"
                            label={__('Email', 'smartpay')}
                        />
                    </FlexBlock>
                </Flex>
            </div>
        </>
    )
}
