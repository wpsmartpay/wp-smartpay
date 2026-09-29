import { __ } from '@wordpress/i18n'
import { AlertTriangle } from 'lucide-react'

/**
 * Deprecation banner shown at the top of a feature that is paused in 3.0.0.
 *
 * Amber/warning variant built on the sp-* design tokens. Persistent (a
 * deprecation notice should not be dismissible). Pass a custom title/message,
 * or rely on the generic defaults.
 *
 * @param {Object} props
 * @param {string} [props.feature]  Human label, e.g. "Products" — used in the default message.
 * @param {string} [props.title]    Override the heading.
 * @param {string} [props.message]  Override the body copy.
 */
export const DeprecatedBanner = ( { feature, title, message } ) => {
	const heading = title || __( 'This feature is paused in 3.0.0', 'smartpay' )

	const body =
		message ||
		( feature
			? // translators: %s: feature name, e.g. "Products".
			  __( '%s are paused in this release and will return in a future update. You can still view existing data, but creating new items is discouraged.', 'smartpay' ).replace( '%s', feature )
			: __( 'This feature is paused in this release and will return in a future update.', 'smartpay' ) )

	return (
		<div
			className="smartpay-deprecated-banner"
			role="alert"
			style={ {
				display:      'flex',
				alignItems:   'flex-start',
				gap:          12,
				background:   '#fffbeb',
				border:       '1px solid #fde68a',
				borderRadius: 'var(--sp-radius, 8px)',
				padding:      '14px 16px',
				marginBottom: 20,
			} }
		>
			<AlertTriangle
				style={ { width: 20, height: 20, color: '#b45309', flexShrink: 0, marginTop: 1 } }
				aria-hidden="true"
			/>
			<div style={ { flex: 1, minWidth: 0 } }>
				<div style={ { fontSize: 13, fontWeight: 700, color: '#92400e', lineHeight: 1.4 } }>
					{ heading }
				</div>
				<div style={ { fontSize: 12.5, color: '#b45309', marginTop: 3, lineHeight: 1.5 } }>
					{ body }
				</div>
			</div>
		</div>
	)
}

export default DeprecatedBanner
