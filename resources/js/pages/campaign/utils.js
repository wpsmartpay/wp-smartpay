const { __, sprintf } = wp.i18n

const decodeHtmlEntity = (str) => {
    if (!str) return ''
    const txt = document.createElement('textarea')
    txt.innerHTML = str
    return txt.value
}

export const currencySymbol = () => decodeHtmlEntity(window.smartpay?.options?.currencySymbol) || '$'

export const money = (value, digits = 0) =>
    `${currencySymbol()}${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits || 2,
    })}`

export const count = (value) => Number(value || 0).toLocaleString()

/** Goal value in its unit: money for `amount`, a count otherwise. */
export const goalValue = (value, type) => (type === 'amount' ? money(value) : count(value))

export const GOAL_TYPES = [
    { value: 'amount', label: __('Amount raised', 'smartpay'), help: __('Total money collected', 'smartpay') },
    { value: 'donations', label: __('Number of donations', 'smartpay'), help: __('Count of gifts', 'smartpay') },
    { value: 'donors', label: __('Number of donors', 'smartpay'), help: __('Unique people', 'smartpay') },
]

export const DONOR_TYPES = {
    first_time: __('First-time', 'smartpay'),
    repeat: __('Repeat', 'smartpay'),
    monthly: __('Recurring', 'smartpay'),
}

export const colorIndex = (str) => {
    let h = 0
    for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
    return h
}

export const initials = (name) =>
    (name || '?')
        .replace(/[^\p{L}\p{N}\s]/gu, '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p[0])
        .join('')
        .toUpperCase() || '?'

export const shortDate = (value) => {
    if (!value) return '—'
    const d = new Date(value.replace(' ', 'T'))
    return isNaN(d) ? value : d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

export const timeAgo = (value) => {
    if (!value) return ''
    const d = new Date(value.replace(' ', 'T') + 'Z')
    const s = Math.max(0, (Date.now() - d.getTime()) / 1000)
    if (s < 3600) return sprintf(__('%d minutes ago', 'smartpay'), Math.max(1, Math.round(s / 60)))
    if (s < 86400) return sprintf(__('%d hours ago', 'smartpay'), Math.round(s / 3600))
    if (s < 172800) return __('Yesterday', 'smartpay')
    return sprintf(__('%d days ago', 'smartpay'), Math.round(s / 86400))
}

export const DonorTypeBadge = ({ type, monthlyAmount }) => {
    const cls = type === 'monthly' ? 'sp-badge--success' : type === 'repeat' ? 'sp-badge--warning' : 'sp-badge--neutral'
    const label = DONOR_TYPES[type] || type
    return (
        <span className={`sp-badge ${cls}`}>
            {type === 'monthly' && monthlyAmount ? sprintf(__('%1$s · %2$s/mo', 'smartpay'), label, money(monthlyAmount)) : label}
        </span>
    )
}

export const GoalBar = ({ progress, width = 90 }) => (
    <div className="sp-campaign-bar" style={{ width }}>
        <span style={{ width: `${Math.min(100, progress?.percentage || 0)}%` }} />
    </div>
)

export const errorMessage = (e) => e?.message || __('Something went wrong. Please try again.', 'smartpay')
