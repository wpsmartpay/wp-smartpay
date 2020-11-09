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
			<div className={'form-element'}>
				<Flex>
					<FlexBlock>
						<InputControl
							name="first_name"
							label={__('First Name')}
						/>
					</FlexBlock>
					{attributes.showLastName && (
						<FlexBlock>
							<InputControl
								name="last_name"
								label={__('Last Name')}
							/>
						</FlexBlock>
					)}
				</Flex>
			</div>

			<InspectorControls>
				<PanelBody title={__('Settings')} initialOpen={true}>
					<ToggleControl
						label={__('Show Last Name fields')}
						checked={attributes.showLastName}
						value={true}
						onChange={onShowLastNameChange}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	)
}
