import { __ } from '@wordpress/i18n'
import { ChevronDown, Search } from 'lucide-react'
import { DeleteCoupon, GetCoupons } from '../../http/coupon'
import { CouponDialog } from './CouponDialog'

const { useState, useEffect, useCallback } = wp.element

/* ── Helpers ──────────────────────────────────────────────── */

const TYPE_OPTIONS = [
	{ value: '',        label: __('All Types', 'smartpay') },
	{ value: 'fixed',   label: __('Fixed',     'smartpay') },
	{ value: 'percent', label: __('Percent',   'smartpay') },
]

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

const expireLabel = (dateStr) => {
	if (!dateStr) return { text: __('Never', 'smartpay'), cls: 'sp-badge--active' }
	const date = new Date(dateStr)
	if (date < new Date()) return { text: __('Expired', 'smartpay'), cls: 'sp-badge--failed' }
	return {
		text: date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
		cls: 'sp-badge--pending',
	}
}

/* ── Row ──────────────────────────────────────────────────── */

const CouponRow = ({ coupon, onDelete, onEdit, openId, setOpenId, checked, onCheck }) => {
	const isOpen  = openId === coupon.id
	const expire  = expireLabel(coupon.expiry_date)
	const isFixed = coupon.discount_type === 'fixed'
	const amount  = isFixed
		? (coupon.discount_amount ?? '—')
		: `${coupon.discount_amount ?? '—'}%`

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td>
				<span style={{ fontWeight: 600, letterSpacing: '0.04em', textTransform: 'uppercase', fontFamily: 'monospace', fontSize: 13 }}>
					{coupon.title || '—'}
				</span>
			</td>

			<td>
				{coupon.discount_type === 'percent'
					? <span className="sp-badge sp-badge--dot sp-badge--trial">{__('Percent', 'smartpay')}</span>
					: <span className="sp-badge sp-badge--dot sp-badge--pending">{__('Fixed', 'smartpay')}</span>
				}
			</td>

			<td className="sp-cell--num">{amount}</td>

			<td>
				<span className={`sp-badge ${expire.cls}`}>{expire.text}</span>
			</td>

			<td className="sp-cell--actions">
				<div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}>
					<button className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : coupon.id)}>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						<button className="sp-dropdown__item"
							onClick={() => { setOpenId(null); onEdit(coupon.id) }}>
							{__('Edit', 'smartpay')}
						</button>
						<div className="sp-dropdown__divider" />
						<button className="sp-dropdown__item sp-dropdown__item--destructive"
							onClick={() => { setOpenId(null); onDelete(coupon.id) }}>
							{__('Delete', 'smartpay')}
						</button>
					</div>
				</div>
			</td>
		</tr>
	)
}

/* ── Main list ────────────────────────────────────────────── */

export const CouponList = () => {
	const { Header } = window.WPSmartPayUI

	const proActions = window.wp?.hooks?.applyFilters( 'smartpay_coupon_list_actions', [] ) || []

	const [data,             setData]             = useState([])
	const [isLoading,        setIsLoading]        = useState(false)
	const [searchQuery,      setSearchQuery]      = useState('')
	const [debouncedSearch,  setDebouncedSearch]  = useState('')
	const [couponType,       setCouponType]       = useState('')
	const [openRowId,        setOpenRowId]        = useState(null)
	const [actionOpen,       setActionOpen]       = useState(false)
	const [checkedIds,       setCheckedIds]       = useState(new Set())
	const [perPage,          setPerPage]          = useState(20)
	const [selectedCouponId, setSelectedCouponId] = useState(null)
	const [isDialogOpen,     setIsDialogOpen]     = useState(false)
	const [pagination,       setPagination]       = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchCoupons = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetCoupons({ page, perPage, search, type: couponType })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load coupons', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage, couponType])

	useEffect(() => {
		fetchCoupons(1, debouncedSearch)
	}, [fetchCoupons, debouncedSearch])

	useEffect(() => {
		const close = () => { setOpenRowId(null); setActionOpen(false) }
		document.addEventListener('click', close)
		return () => document.removeEventListener('click', close)
	}, [])

	const deleteCoupon = async (id) => {
		const deleted = await DeleteCoupon(id)
		if (deleted) fetchCoupons(pagination.current_page, debouncedSearch)
	}

	const bulkDelete = async () => {
		if (!window.confirm(__('Delete all selected coupons? This cannot be undone.', 'smartpay'))) return
		await Promise.all([...checkedIds].map((id) => {
			const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${baseUrl}/v1/coupons/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
			})
		}))
		fetchCoupons(1, debouncedSearch)
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

	const goToPage = (page) => fetchCoupons(page, debouncedSearch)

	const openCreate = () => { setSelectedCouponId(null); setIsDialogOpen(true) }
	const openEdit   = (id) => { setSelectedCouponId(id); setIsDialogOpen(true) }

	return (
		<>
			<Header
				title={__('Coupons', 'smartpay')}
				subtitle={__('Manage your coupons here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Coupons', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Manage your coupons here', 'smartpay')}</p>
				</div>

				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input type="search" className="sp-search__input"
							placeholder={__('Search by coupon code', 'smartpay')}
							value={searchQuery}
							onChange={(e) => setSearchQuery(e.target.value)} />
					</div>

					<select className="sp-filter-select" value={couponType}
						onChange={(e) => setCouponType(e.target.value)}>
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
							disabled={!hasSelection}
							onClick={() => setActionOpen((o) => !o)}>
							{__('Select Action', 'smartpay')}
							<ChevronDown size={14} style={{ marginLeft: 2, opacity: 0.6 }} />
						</button>
						<div className={`sp-dropdown${actionOpen ? ' sp-dropdown--open' : ''}`}>
							<button className="sp-dropdown__item sp-dropdown__item--destructive"
								onClick={() => {
									setActionOpen(false)
									if (!hasSelection) {
										window.alert(__('Please select one or more coupons first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>

					{proActions}

					<button className="sp-btn sp-btn--primary" onClick={openCreate}>
						+ {__('Create Coupon', 'smartpay')}
					</button>
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
								<th>{__('Code', 'smartpay')}</th>
								<th>{__('Type', 'smartpay')}</th>
								<th className="sp-col--num">{__('Amount', 'smartpay')}</th>
								<th>{__('Expires', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={6} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={6}>
									<div className="sp-empty">
										<div className="sp-empty__icon">🏷️</div>
										<div className="sp-empty__title">{__('No coupons found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery || couponType
												? __('No coupons match your filters. Try adjusting them.', 'smartpay')
												: __('Create your first coupon to offer discounts.', 'smartpay')
											}
										</div>
										{!searchQuery && !couponType && (
											<button className="sp-btn sp-btn--primary" onClick={openCreate}>
												+ {__('Create Coupon', 'smartpay')}
											</button>
										)}
									</div>
								</td></tr>
							) : data.map((coupon) => (
								<CouponRow
									key={coupon.id}
									coupon={coupon}
									onDelete={deleteCoupon}
									onEdit={openEdit}
									openId={openRowId}
									setOpenId={setOpenRowId}
									checked={checkedIds.has(coupon.id)}
									onCheck={() => toggleRow(coupon.id)}
								/>
							))}
						</tbody>
					</table>

					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('coupons', 'smartpay')}
								</span>
								<select className="sp-filter-select"
									style={{ fontSize: 12, padding: '0 22px 0 8px' }}
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

			<CouponDialog
				couponId={selectedCouponId}
				open={isDialogOpen}
				onOpenChange={(open) => {
					setIsDialogOpen(open)
					if (!open) {
						setSelectedCouponId(null)
						fetchCoupons(pagination.current_page, debouncedSearch)
					}
				}}
			/>
		</>
	)
}
