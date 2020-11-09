import { __ } from '@wordpress/i18n'

export default function Header() {
	return (
		<div
			className="smartpay-block-editor-header"
			role="region"
			aria-label={__(
				'Standalone Editor top bar.',
				'smartpay-block-editor'
			)}
			tabIndex="-1"
		>
			<h1 className="smartpay-block-editor-header__title">
				{__('Smartpay Form Builder', 'smartpay-block-editor')}
			</h1>
		</div>
	)
}
