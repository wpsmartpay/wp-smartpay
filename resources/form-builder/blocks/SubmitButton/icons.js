/**
 * Curated icon set for the Pay Button block.
 *
 * Slugs MUST stay in sync with smartpay_submit_button_icon_svg() in
 * app/Helpers/smartpay.php — the editor draws these JSX icons, the frontend
 * template draws the matching server-side SVG by the same slug.
 */
const svgProps = {
    width: 20,
    height: 20,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 2,
    strokeLinecap: 'round',
    strokeLinejoin: 'round',
    'aria-hidden': true,
    focusable: false,
}

export const SUBMIT_ICONS = {
    'arrow-right': (
        <svg {...svgProps}>
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
    ),
    lock: (
        <svg {...svgProps}>
            <rect x="5" y="11" width="14" height="9" rx="2" />
            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
        </svg>
    ),
    cart: (
        <svg {...svgProps}>
            <circle cx="9" cy="20" r="1" />
            <circle cx="17" cy="20" r="1" />
            <path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .6h8.2a1 1 0 0 0 1-.8L21 8H6" />
        </svg>
    ),
    check: (
        <svg {...svgProps}>
            <path d="M5 12l5 5L20 6" />
        </svg>
    ),
    dollar: (
        <svg {...svgProps}>
            <path d="M12 2v20M17 6H10a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H7" />
        </svg>
    ),
}

// Order shown in the picker. '' = no icon.
export const SUBMIT_ICON_SLUGS = ['', 'arrow-right', 'lock', 'cart', 'check', 'dollar']
