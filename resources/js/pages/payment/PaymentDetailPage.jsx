import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { GetPayment, Update } from '@/http/payment'
import { ActivityLogSection } from './ActivityLogSection'
import Swal from 'sweetalert2/dist/sweetalert2'

const { Header } = window.WPSmartPayUI

/* ── Helpers ──────────────────────────────────────────────── */

const decodeHtmlEntity = (str) => {
	if (!str) return ''
	const txt = document.createElement('textarea')
	txt.innerHTML = str
	return txt.value
}
const currencySymbol = decodeHtmlEntity(window.smartpay?.options?.currencySymbol) || '$'

const fmtAmount = (amount, currency) => {
	try {
		return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency || 'USD' }).format(parseFloat(amount) || 0)
	} catch {
		return currencySymbol + (parseFloat(amount) || 0).toFixed(2)
	}
}

const statusClass = (status) => {
	const map = {
		completed:  'sp-badge--active',
		pending:    'sp-badge--pending',
		processing: 'sp-badge--pending',
		failed:     'sp-badge--failed',
		refunded:   'sp-badge--expired',
		revoked:    'sp-badge--expired',
		abandoned:  'sp-badge--expired',
	}
	return map[(status || '').toLowerCase()] || 'sp-badge--expired'
}

const STATUS_OPTIONS = [
	{ value: 'pending',    label: __('Pending',    'smartpay') },
	{ value: 'completed',  label: __('Completed',  'smartpay') },
	{ value: 'refunded',   label: __('Refunded',   'smartpay') },
	{ value: 'failed',     label: __('Failed',     'smartpay') },
	{ value: 'abandoned',  label: __('Abandoned',  'smartpay') },
	{ value: 'revoked',    label: __('Revoked',    'smartpay') },
	{ value: 'processing', label: __('Processing', 'smartpay') },
]

/* ── Sub-components ───────────────────────────────────────── */

const DetailCard = ({ title, badge, children }) => (
	<div className="sp-detail-card">
		<div className="sp-detail-card__header">
			<span className="sp-detail-card__title">{title}</span>
			{badge && <span className="sp-detail-card__badge">{badge}</span>}
		</div>
		<div className="sp-detail-card__body">{children}</div>
	</div>
)

const Field = ({ label, children }) => (
	<div>
		<div className="sp-detail-field__label">{label}</div>
		<div className="sp-detail-field__value">{children || '—'}</div>
	</div>
)

/* ── Skeleton ─────────────────────────────────────────────── */

const SkeletonLoader = () => (
	<>
		<Header title={__('Payment Details', 'smartpay')} subtitle={__('Loading…', 'smartpay')} />
		<div className="sp-layout">
			<div style={{ width: 100, height: 16, background: '#f3f4f6', borderRadius: 4, marginBottom: 18 }} />
			<div className="sp-detail-grid">
				<div>
					{[1, 2].map((i) => (
						<div key={i} className="sp-detail-card" style={{ marginBottom: 16 }}>
							<div className="sp-detail-card__header">
								<div style={{ width: 80, height: 12, background: '#f3f4f6', borderRadius: 3 }} />
							</div>
							<div className="sp-detail-card__body">
								<div style={{ display: 'flex', gap: 32 }}>
									{[1, 2, 3, 4].map((j) => (
										<div key={j}>
											<div style={{ width: 48, height: 9, background: '#f3f4f6', borderRadius: 3, marginBottom: 6 }} />
											<div style={{ width: 72, height: 14, background: '#f3f4f6', borderRadius: 3 }} />
										</div>
									))}
								</div>
							</div>
						</div>
					))}
				</div>
				<div>
					<div className="sp-detail-card">
						<div className="sp-detail-card__header">
							<div style={{ width: 60, height: 12, background: '#f3f4f6', borderRadius: 3 }} />
						</div>
						<div className="sp-detail-card__body">
							{[1, 2, 3].map((i) => (
								<div key={i} style={{ width: '100%', height: 34, background: '#f3f4f6', borderRadius: 5, marginBottom: 8 }} />
							))}
						</div>
					</div>
				</div>
			</div>
		</div>
	</>
)

/* ── Form data section ────────────────────────────────────── */

const FormDataSection = ({ formData, formFields }) => {
	const build = (fields) => {
		if (!Array.isArray(fields)) return {}
		let tempFields = {}
		fields.forEach((item) => {
			const data = item[Object.keys(item)[0]]
			if (data?.attributes) item = data
			const key = item['attributes']?.['name']
			if (!key) return
			tempFields[key] = item.fields ? build(item.fields) : item.settings?.label
		})
		return tempFields
	}

	const humanize = (str) => String(str).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())

	const flatRows = (data, labels) => {
		const rows = []
		const hasLabels = labels && Object.keys(labels).length > 0
		const entries = Object.entries(data || {})
		for (const [key, val] of entries) {
			if (val === null || val === undefined || val === '') continue
			if (typeof val === 'object' && !Array.isArray(val)) {
				const sub = hasLabels && typeof labels[key] === 'object' ? labels[key] : null
				rows.push(...flatRows(val, sub))
			} else {
				const label = hasLabels && typeof labels[key] === 'string' ? labels[key] : humanize(key)
				rows.push({ label, value: String(val) })
			}
		}
		return rows
	}

	const labels = build(formFields || [])
	const rows = flatRows(formData, labels)

	if (!rows.length) return null

	return (
		<DetailCard title={__('Form Data', 'smartpay')}>
			<table className="sp-kv-table">
				<tbody>
					{rows.map(({ label, value }, i) => (
						<tr key={i}>
							<td>{label}</td>
							<td>{value}</td>
						</tr>
					))}
				</tbody>
			</table>
		</DetailCard>
	)
}

/* ── Main page ────────────────────────────────────────────── */

export const PaymentDetailPage = () => {
	const { paymentId } = useParams()
	const navigate = useNavigate()

	const [payment,       setPayment]       = useState(null)
	const [paymentStatus, setPaymentStatus] = useState('pending')
	const [loading,       setLoading]       = useState(true)
	const [saving,        setSaving]        = useState(false)

	useEffect(() => {
		if (!paymentId) return
		setLoading(true)
		GetPayment(paymentId)
			.then((data) => {
				if (data) {
					setPayment(data)
					setPaymentStatus(data.status?.toLowerCase() || 'pending')
				}
			})
			.catch(() => {
				Swal.fire({ icon: 'error', title: __('Error', 'smartpay'), text: __('Failed to load payment.', 'smartpay') })
			})
			.finally(() => setLoading(false))
	}, [paymentId])

	const handleSave = async () => {
		setSaving(true)
		try {
			const response = await Update(paymentId, JSON.stringify({ ...payment, status: paymentStatus }))
			setPayment(response.payment)
			Swal.fire({
				toast: true, icon: 'success',
				title: __(response.message || 'Updated', 'smartpay'),
				position: 'top-end', showConfirmButton: false, timer: 2000,
				showClass: { popup: 'swal2-noanimation' }, hideClass: { popup: '' },
			})
		} catch {
			Swal.fire({ icon: 'error', title: __('Error', 'smartpay'), text: __('Failed to update payment.', 'smartpay') })
		} finally {
			setSaving(false)
		}
	}

	if (loading) return <SkeletonLoader />

	if (!payment) {
		return (
			<>
				<Header title={__('Payment Details', 'smartpay')} subtitle={__('Payment not found', 'smartpay')} />
				<div className="sp-layout">
					<div className="sp-detail-card" style={{ textAlign: 'center', padding: 40 }}>
						<p style={{ color: 'var(--sp-text-muted)', marginBottom: 16 }}>
							{__('Payment not found.', 'smartpay')}
						</p>
						<button className="sp-btn sp-btn--outline" onClick={() => navigate('/payments')}>
							← {__('Back to Payments', 'smartpay')}
						</button>
					</div>
				</div>
			</>
		)
	}

	const isProductPurchase = payment.type === 'Product Purchase'
	const isFormPayment     = payment.type === 'Form Payment'

	/* Collect main sections — pro plugin can splice via applyFilters */
	let mainSections = []

	/* Product details */
	if (isProductPurchase) {
		mainSections.push({
			id: 'product_details',
			component: (
				<DetailCard key="product_details" title={__('Product Details', 'smartpay')}>
					<table className="sp-kv-table">
						<tbody>
							<tr>
								<td>{__('Product', 'smartpay')}</td>
								<td>
									{payment.data?.product_edit_url
										? <a href={payment.data.product_edit_url}>{payment.data.product_title || `#${payment.data.product_id}`}</a>
										: (payment.data?.product_title || `#${payment.data?.product_id}`)
									}
								</td>
							</tr>
							<tr><td>{__('Price', 'smartpay')}</td><td>{payment.currency} {payment.data?.product_price}</td></tr>
							<tr><td>{__('Total Amount', 'smartpay')}</td><td>{payment.currency} {payment.data?.total_amount}</td></tr>
							<tr><td>{__('Billing Type', 'smartpay')}</td><td>{payment.data?.billing_type || '—'}</td></tr>
						</tbody>
					</table>
				</DetailCard>
			),
		})
	}

	/* Form details */
	if (isFormPayment) {
		mainSections.push({
			id: 'form_details',
			component: (
				<DetailCard key="form_details" title={__('Form Details', 'smartpay')}>
					<table className="sp-kv-table">
						<tbody>
							<tr>
								<td>{__('Form', 'smartpay')}</td>
								<td>
									{payment.data?.form_edit_url
										? <a href={payment.data.form_edit_url}>{payment.data?.form_title || `#${payment.data?.form_id}`}</a>
										: (payment.data?.form_title || `#${payment.data?.form_id}`)
									}
									{payment.data?.form_type && (
										<span className="sp-badge sp-badge--expired" style={{ marginLeft: 8, fontSize: 10 }}>
											{payment.data.form_type}
										</span>
									)}
								</td>
							</tr>
							<tr><td>{__('Total Amount', 'smartpay')}</td><td>{payment.data?.total_amount || '—'}</td></tr>
							<tr><td>{__('Billing Type', 'smartpay')}</td><td>{payment.data?.billing_type || '—'}</td></tr>
						</tbody>
					</table>
				</DetailCard>
			),
		})
	}

	/* Customer details */
	if (payment.customer) {
		mainSections.push({
			id: 'customer_details',
			component: (
				<DetailCard key="customer_details" title={__('Customer', 'smartpay')}>
					<div className="sp-detail-fields">
						<Field label={__('First Name', 'smartpay')}>{payment.customer.first_name}</Field>
						<Field label={__('Last Name', 'smartpay')}>{payment.customer.last_name}</Field>
						<Field label={__('Email', 'smartpay')}>{payment.customer.email}</Field>
					</div>
				</DetailCard>
			),
		})
	}

	/* Form data */
	if (payment.extra?.form_data) {
		mainSections.push({
			id: 'form_data',
			component: (
				<FormDataSection
					key="form_data"
					formData={payment.extra.form_data}
					formFields={payment.extra.form_fields}
				/>
			),
		})
	}

	/* Activity log */
	mainSections.push({
		id: 'activity_log',
		component: <ActivityLogSection key="activity_log" paymentId={paymentId} />,
	})

	const sections = window.wp?.hooks?.applyFilters('smartpay_payment_details_sections', mainSections, payment) || mainSections

	return (
		<>
			<Header
				title={__('Payment Details', 'smartpay')}
				subtitle={`#${payment.id} · ${payment.email}`}
			/>

			<div className="sp-layout">

				{/* Back */}
				<Link to="/payments" className="sp-back-btn">
					<span className="sp-back-btn__arrow">←</span>
					{__('Payments', 'smartpay')}
				</Link>

				<div className="sp-detail-grid">

					{/* ── Main column ── */}
					<div>

						{/* Summary card */}
						<div className="sp-detail-card">
							<div className="sp-detail-card__header">
								<span className="sp-detail-card__title">{__('Payment', 'smartpay')}</span>
								<span className="sp-detail-card__badge">#{payment.id}</span>
							</div>
							<div className="sp-detail-card__body">

								{/* Amount + status hero */}
								<div className="sp-detail-hero">
									<span className="sp-detail-amount">
										{fmtAmount(payment.amount, payment.currency)}
									</span>
									<span className={`sp-badge sp-badge--dot ${statusClass(payment.status)}`}>
										{payment.status || 'pending'}
									</span>
									{payment.type && (
										<span className="sp-badge sp-badge--expired" style={{ fontSize: 11 }}>
											{payment.type}
										</span>
									)}
								</div>

								{/* Horizontal fields */}
								<div className="sp-detail-fields">
									<Field label={__('Date', 'smartpay')}>
										{payment.created_at
											? new Date(payment.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })
											: '—'}
									</Field>
									<Field label={__('Customer', 'smartpay')}>
										{payment.customer?.id
											? <Link to={`/customers/${payment.customer.id}`} style={{ color: 'var(--sp-brand)', textDecoration: 'none' }}>{payment.email}</Link>
											: payment.email
										}
									</Field>
									<Field label={__('Gateway', 'smartpay')}>{payment.gateway}</Field>
									{payment.transaction_id && (
										<Field label={__('Transaction ID', 'smartpay')}>
											<span style={{ fontFamily: 'monospace', fontSize: 12 }}>{payment.transaction_id}</span>
										</Field>
									)}
									{payment.subscription_id && (
										<Field label={__('Subscription', 'smartpay')}>
											<Link to={`/subscriptions/${payment.subscription_id}`} style={{ color: 'var(--sp-brand)', textDecoration: 'none' }}>
												#{payment.subscription_id}
											</Link>
										</Field>
									)}
									<Field label={__('Mode', 'smartpay')}>
										<span className={`sp-badge ${payment.mode === 'test' ? 'sp-badge--pending' : 'sp-badge--expired'}`} style={{ fontSize: 11 }}>
											{payment.mode || 'live'}
										</span>
									</Field>
								</div>
							</div>
						</div>

						{/* Dynamic sections (product/form/customer/form-data/activity) */}
						{sections.map((s) => s.component)}
					</div>

					{/* ── Sidebar ── */}
					<div className="sp-detail-sidebar">

						{/* Status update */}
						<div className="sp-detail-card">
							<div className="sp-detail-card__header">
								<span className="sp-detail-card__title">{__('Actions', 'smartpay')}</span>
							</div>
							<div className="sp-detail-card__body">
								<label className="sp-detail-field__label" style={{ display: 'block', marginBottom: 6 }}>
									{__('Payment Status', 'smartpay')}
								</label>
								<select className="sp-filter-select"
									value={paymentStatus}
									onChange={(e) => setPaymentStatus(e.target.value)}>
									{STATUS_OPTIONS.map((o) => (
										<option key={o.value} value={o.value}>{o.label}</option>
									))}
								</select>
								<button className="sp-btn sp-btn--primary"
									onClick={handleSave} disabled={saving}>
									{saving ? __('Updating…', 'smartpay') : __('Update Status', 'smartpay')}
								</button>
							</div>
						</div>

						{/* Transaction info */}
						<div className="sp-detail-card">
							<div className="sp-detail-card__header">
								<span className="sp-detail-card__title">{__('Transaction Info', 'smartpay')}</span>
							</div>
							<div className="sp-detail-card__body">
								<table className="sp-kv-table">
									<tbody>
										<tr><td>{__('Payment ID', 'smartpay')}</td><td>#{payment.id}</td></tr>
										<tr><td>{__('Gateway', 'smartpay')}</td><td>{payment.gateway || '—'}</td></tr>
										{payment.transaction_id && (
											<tr>
												<td>{__('Txn ID', 'smartpay')}</td>
												<td style={{ fontFamily: 'monospace', fontSize: 11, wordBreak: 'break-all' }}>{payment.transaction_id}</td>
											</tr>
										)}
										<tr>
											<td>{__('Mode', 'smartpay')}</td>
											<td>
												<span className={`sp-badge ${payment.mode === 'test' ? 'sp-badge--pending' : 'sp-badge--expired'}`} style={{ fontSize: 10 }}>
													{payment.mode || 'live'}
												</span>
											</td>
										</tr>
										<tr><td>{__('Currency', 'smartpay')}</td><td>{payment.currency || '—'}</td></tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</>
	)
}
