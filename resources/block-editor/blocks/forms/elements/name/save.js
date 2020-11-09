import { Button } from '@wordpress/components'

import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
	console.log(attributes)
	return (
		<div className={'form-element'}>
			<input name="first_name" label="First Name" />

			{attributes.showLastName && (
				<input name="last_name" label="Last Name" />
			)}
		</div>
	)
}
