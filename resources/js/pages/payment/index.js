import { __ } from '@wordpress/i18n'
import { ChevronDown, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { DeletePayment, GetPayments } from '@/http/payment'
import { PaymentDetailsDialog } from './PaymentDetailsDialog'

const { useState, useEffect, useCallback } = wp.element

/* ── Helpers ──────────────────────────────────────────────── */

const decodeHtmlEntity = (str) => {
	if (!str) return ''
	const txt = document.createElement('textarea')
	txt.innerHTML = str
	return txt.value
}
const currencySymbol = decodeHtmlEntity(window.smartpay?.options?.currencySymbol) || '$'

const colorIndex = (str) => {
	let h = 0
	for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
	return h
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

const periodLabel = (period) => {
	const map = { day: 'day', week: 'wk', month: 'mo', year: 'yr' }
	return map[(period || '').toLowerCase()] || period || ''
}

const isSubscription = (billingType) =>
	typeof billingType === 'string' && billingType.toLowerCase() === 'subscription'

const fmtAmount = (amount, currency) => {
	try {
		return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency || 'USD' }).format(parseFloat(amount) || 0)
	} catch {
		return currencySymbol + (parseFloat(amount) || 0).toFixed(2)
	}
}

/* ── Row ──────────────────────────────────────────────────── */

const PaymentRow = ({ payment, onDelete, onView, openId, setOpenId, checked, onCheck }) => {
	const isOpen      = openId === payment.id
	const billingType = payment?.data?.billing_type
	const period      = periodLabel(payment?.data?.billing_period)
	const dateStr     = payment.completed_at || payment.created_at
	const dateLabel   = dateStr
		? new Date(dateStr).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' })
		: '—'

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td>
				<div className="sp-customer">
					<div className="sp-avatar" data-color={colorIndex(payment.email)}>
						{(payment.email || '?').substring(0, 2).toUpperCase()}
					</div>
					<div className="sp-customer__info">
						<Link to={`/payments/${payment.id}`} className="sp-customer__name"
							style={{ textDecoration: 'none', color: 'inherit' }}>
							{payment.email || '—'}
						</Link>
						<div className="sp-customer__email">#{payment.id}</div>
					</div>
				</div>
			</td>

			<td>
				{isSubscription(billingType)
					? <span className="sp-badge sp-badge--dot sp-badge--trial">{__('Subscription', 'smartpay')}</span>
					: <span className="sp-badge sp-badge--dot sp-badge--active">{__('One-time', 'smartpay')}</span>
				}
			</td>

			<td>
				{payment.type === 'Product Purchase'
					? <span className="sp-badge sp-badge--expired">#{payment.data?.product_id || '—'} product</span>
					: payment.type === 'Form Payment'
					? <span className="sp-badge sp-badge--pending">#{payment.data?.form_id || '—'} form</span>
					: <span style={{ color: 'var(--sp-text-subtle)' }}>—</span>
				}
			</td>

			<td className="sp-cell--muted sp-col--nowrap">{dateLabel}</td>

			<td>
				<span className={`sp-badge sp-badge--dot ${statusClass(payment.status)}`}>
					{payment.status || 'pending'}
				</span>
			</td>

			<td className="sp-cell--num">
				{fmtAmount(payment.amount, payment.currency)}
				{isSubscription(billingType) && period && (
					<span style={{ color: 'var(--sp-text-subtle)', fontSize: 11, marginLeft: 3 }}>/ {period}</span>
				)}
			</td>

			<td className="sp-cell--actions">
				<div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}>
					<button className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : payment.id)}>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						<Link to={`/payments/${payment.id}`} className="sp-dropdown__item"
							onClick={() => setOpenId(null)}>
							{__('View Details', 'smartpay')}
						</Link>
						<button className="sp-dropdown__item"
							onClick={() => { setOpenId(null); onView(payment.id) }}>
							{__('Quick View', 'smartpay')}
						</button>
						<div className="sp-dropdown__divider" />
						<button className="sp-dropdown__item sp-dropdown__item--destructive"
							onClick={() => { setOpenId(null); onDelete(payment.id) }}>
							{__('Delete', 'smartpay')}
						</button>
					</div>
				</div>
			</td>
		</tr>
	)
}

/* ── Main list ────────────────────────────────────────────── */

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

const STATUS_OPTIONS = [
	{ value: '',            label: __('All Statuses', 'smartpay') },
	{ value: 'completed',   label: __('Completed',    'smartpay') },
	{ value: 'pending',     label: __('Pending',      'smartpay') },
	{ value: 'failed',      label: __('Failed',       'smartpay') },
	{ value: 'refunded',    label: __('Refunded',     'smartpay') },
	{ value: 'processing',  label: __('Processing',   'smartpay') },
	{ value: 'revoked',     label: __('Revoked',      'smartpay') },
	{ value: 'abandoned',   label: __('Abandoned',    'smartpay') },
]

const TYPE_OPTIONS = [
	{ value: '',                 label: __('All Types', 'smartpay') },
	{ value: 'form_payment',     label: __('Form',      'smartpay') },
	{ value: 'product_purchase', label: __('Product',   'smartpay') },
]

export const PaymentList = () => {
	const { Header } = window.WPSmartPayUI

	const [data,              setData]              = useState([])
	const [isLoading,         setIsLoading]         = useState(false)
	const [searchQuery,       setSearchQuery]       = useState('')
	const [debouncedSearch,   setDebouncedSearch]   = useState('')
	const [openRowId,         setOpenRowId]         = useState(null)
	const [actionOpen,        setActionOpen]        = useState(false)
	const [checkedIds,        setCheckedIds]        = useState(new Set())
	const [perPage,           setPerPage]           = useState(20)
	const [paymentStatus,     setPaymentStatus]     = useState('')
	const [paymentType,       setPaymentType]       = useState('')
	const [selectedPaymentId, setSelectedPaymentId] = useState(null)
	const [isDialogOpen,      setIsDialogOpen]      = useState(false)
	const [pagination,        setPagination]        = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchPayments = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetPayments({ page, perPage, search, status: paymentStatus, type: paymentType, sortBy: 'id:desc' })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load payments', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage, paymentStatus, paymentType])

	useEffect(() => {
		fetchPayments(1, debouncedSearch)
	}, [fetchPayments, debouncedSearch])

	useEffect(() => {
		const close = () => { setOpenRowId(null); setActionOpen(false) }
		document.addEventListener('click', close)
		return () => document.removeEventListener('click', close)
	}, [])

	const deletePayment = async (id) => {
		const deleted = await DeletePayment(id)
		if (deleted) fetchPayments(pagination.current_page, debouncedSearch)
	}

	const bulkDelete = async () => {
		if (!window.confirm(__('Delete all selected payments? This cannot be undone.', 'smartpay'))) return
		await Promise.all([...checkedIds].map((id) => {
			const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${baseUrl}/v1/payments/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
			})
		}))
		fetchPayments(1, debouncedSearch)
	}

	const allChecked   = data.length > 0 && checkedIds.size === data.length
	const someChecked  = checkedIds.size > 0 && checkedIds.size < data.length
	const hasSelection = checkedIds.size > 0

	const toggleAll = () => setCheckedIds(
		allChecked || someChecked ? new Set() : new Set(data.map((r) => r.id))
	)
	const toggleRow = (id) => {
		const next = new Set(checkedIds)
		next.has(id) ? next.delete(id) : next.add(id)
		setCheckedIds(next)
	}

	const goToPage = (page) => fetchPayments(page, debouncedSearch)

	const proActions = window.wp?.hooks?.applyFilters('smartpay_payment_list_actions', []) || []

	return (
		<>
			<Header
				title={__('Payments', 'smartpay')}
				subtitle={__('Manage your payments here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Payments', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Manage your payments here', 'smartpay')}</p>
				</div>

				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input type="search" className="sp-search__input"
							placeholder={__('Search by email or transaction ID', 'smartpay')}
							value={searchQuery}
							onChange={(e) => setSearchQuery(e.target.value)} />
					</div>

					<select className="sp-filter-select" value={paymentStatus}
						onChange={(e) => setPaymentStatus(e.target.value)}>
						{STATUS_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
					</select>

					<select className="sp-filter-select" value={paymentType}
						onChange={(e) => setPaymentType(e.target.value)}>
						{TYPE_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
					</select>

					{hasSelection && (
						<span className="sp-selection-count">
							{checkedIds.size} {__('selected', 'smartpay')}
							<button className="sp-selection-count__clear"
								onClick={() => setCheckedIds(new Set())} title={__('Clear selection', 'smartpay')}>
								✕
							</button>
						</span>
					)}

					<div className="sp-toolbar__spacer" />

					<div className="sp-action-dropdown" onClick={(e) => e.stopPropagation()}>
						<button className="sp-btn sp-btn--outline"
							onClick={() => setActionOpen((o) => !o)}>
							{__('Select Action', 'smartpay')}
							<ChevronDown size={14} style={{ marginLeft: 2, opacity: 0.6 }} />
						</button>
						<div className={`sp-dropdown${actionOpen ? ' sp-dropdown--open' : ''}`}>
							<button className="sp-dropdown__item sp-dropdown__item--destructive"
								onClick={() => {
									setActionOpen(false)
									if (!hasSelection) {
										window.alert(__('Please select one or more payments first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>

					{proActions}
				</div>

				<div className="sp-table-card">
					<table className="sp-table">
						<thead>
							<tr>
								<th className="sp-col--check">
									<input type="checkbox" className="sp-checkbox sp-select-all"
										checked={allChecked}
										ref={(el) => { if (el) el.indeterminate = someChecked }}
										onChange={toggleAll} />
								</th>
								<th>{__('Customer', 'smartpay')}</th>
								<th>{__('Type', 'smartpay')}</th>
								<th>{__('Source', 'smartpay')}</th>
								<th>{__('Date', 'smartpay')}</th>
								<th>{__('Status', 'smartpay')}</th>
								<th className="sp-col--num">{__('Amount', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={8} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={8}>
									<div className="sp-empty">
										<div className="sp-empty__icon">💳</div>
										<div className="sp-empty__title">{__('No payments found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery || paymentStatus || paymentType
												? __('No payments match your filters. Try adjusting them.', 'smartpay')
												: __('Payments will appear here once customers complete checkout.', 'smartpay')
											}
										</div>
									</div>
								</td></tr>
							) : data.map((payment) => (
								<PaymentRow
									key={payment.id}
									payment={payment}
									onDelete={deletePayment}
									onView={(id) => { setSelectedPaymentId(id); setIsDialogOpen(true) }}
									openId={openRowId}
									setOpenId={setOpenRowId}
									checked={checkedIds.has(payment.id)}
									onCheck={() => toggleRow(payment.id)}
								/>
							))}
						</tbody>
					</table>

					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('payments', 'smartpay')}
								</span>
								<select className="sp-filter-select"
									style={{ height: 28, fontSize: 12, padding: '0 22px 0 8px' }}
									value={perPage}
									onChange={(e) => setPerPage(Number(e.target.value))}>
									{PER_PAGE_OPTIONS.map((n) => (
										<option key={n} value={n}>{n} {__('per page', 'smartpay')}</option>
									))}
								</select>
							</div>
							<div className="sp-pagination__nav">
								<button className="sp-pagination__btn"
									disabled={pagination.current_page <= 1}
									onClick={() => goToPage(pagination.current_page - 1)}>‹</button>
								<span style={{ padding: '0 10px', fontSize: 12, color: 'var(--sp-text-muted)' }}>
									{pagination.current_page} / {pagination.last_page}
								</span>
								<button className="sp-pagination__btn"
									disabled={pagination.current_page >= pagination.last_page}
									onClick={() => goToPage(pagination.current_page + 1)}>›</button>
							</div>
						</div>
					)}
				</div>
			</div>

			<PaymentDetailsDialog
				paymentId={selectedPaymentId}
				open={isDialogOpen}
				onOpenChange={(open) => {
					setIsDialogOpen(open)
					if (!open) {
						setSelectedPaymentId(null)
						fetchPayments(pagination.current_page, debouncedSearch)
					}
				}}
			/>
		</>
	)
}
