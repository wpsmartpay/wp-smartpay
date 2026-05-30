import { __ } from '@wordpress/i18n'
import { ChevronDown, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { DeleteCustomer, GetCustomers } from '../../http/customer'

const { useState, useEffect, useCallback } = wp.element

/* ── Helpers ──────────────────────────────────────────────── */

const colorIndex = (str) => {
	let h = 0
	for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
	return h
}

/* ── Row ──────────────────────────────────────────────────── */

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

const CustomerRow = ({ customer, onDelete, openId, setOpenId, checked, onCheck }) => {
	const isOpen    = openId === customer.id
	const fullName  = `${customer.first_name || ''} ${customer.last_name || ''}`.trim() || '—'
	const initials  = (customer.email || fullName || '?').substring(0, 2).toUpperCase()
	const dateLabel = customer.created_at
		? new Date(customer.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
		: '—'

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td>
				<div className="sp-customer">
					<div className="sp-avatar" data-color={colorIndex(customer.email)}>
						{initials}
					</div>
					<div className="sp-customer__info">
						<Link to={`/customers/${customer.id}`} className="sp-customer__name"
							style={{ textDecoration: 'none', color: 'inherit' }}>
							{fullName}
						</Link>
						<div className="sp-customer__email">{customer.email || '—'}</div>
					</div>
				</div>
			</td>

			<td className="sp-cell--muted sp-col--nowrap">{dateLabel}</td>

			<td className="sp-cell--actions">
				<div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}>
					<button className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : customer.id)}>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						<Link to={`/customers/${customer.id}`} className="sp-dropdown__item"
							onClick={() => setOpenId(null)}>
							{__('View Details', 'smartpay')}
						</Link>
						<div className="sp-dropdown__divider" />
						<button className="sp-dropdown__item sp-dropdown__item--destructive"
							onClick={() => { setOpenId(null); onDelete(customer.id) }}>
							{__('Delete', 'smartpay')}
						</button>
					</div>
				</div>
			</td>
		</tr>
	)
}

/* ── Main list ────────────────────────────────────────────── */

export const CustomerList = () => {
	const { Header } = window.WPSmartPayUI

	const [data,            setData]            = useState([])
	const [isLoading,       setIsLoading]       = useState(false)
	const [searchQuery,     setSearchQuery]     = useState('')
	const [debouncedSearch, setDebouncedSearch] = useState('')
	const [openRowId,       setOpenRowId]       = useState(null)
	const [actionOpen,      setActionOpen]      = useState(false)
	const [checkedIds,      setCheckedIds]      = useState(new Set())
	const [perPage,         setPerPage]         = useState(20)
	const [pagination,      setPagination]      = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchCustomers = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetCustomers({ page, perPage, search })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load customers', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage])

	useEffect(() => {
		fetchCustomers(1, debouncedSearch)
	}, [fetchCustomers, debouncedSearch])

	useEffect(() => {
		const close = () => { setOpenRowId(null); setActionOpen(false) }
		document.addEventListener('click', close)
		return () => document.removeEventListener('click', close)
	}, [])

	const deleteCustomer = async (id) => {
		const deleted = await DeleteCustomer(id)
		if (deleted) fetchCustomers(pagination.current_page, debouncedSearch)
	}

	const bulkDelete = async () => {
		if (!window.confirm(__('Delete all selected customers? This cannot be undone.', 'smartpay'))) return
		await Promise.all([...checkedIds].map((id) => {
			const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${baseUrl}/v1/customers/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
			})
		}))
		fetchCustomers(1, debouncedSearch)
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

	const goToPage = (page) => fetchCustomers(page, debouncedSearch)

	return (
		<>
			<Header
				title={__('Customers', 'smartpay')}
				subtitle={__('Manage your customers here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Customers', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Manage your customers here', 'smartpay')}</p>
				</div>

				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input type="search" className="sp-search__input"
							placeholder={__('Search by email or name', 'smartpay')}
							value={searchQuery}
							onChange={(e) => setSearchQuery(e.target.value)} />
					</div>

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
										window.alert(__('Please select one or more customers first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>
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
								<th>{__('Member Since', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={4} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={4}>
									<div className="sp-empty">
										<div className="sp-empty__icon">👤</div>
										<div className="sp-empty__title">{__('No customers found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery
												? __('No customers match your search. Try a different term.', 'smartpay')
												: __('Customers will appear here once someone completes a payment.', 'smartpay')
											}
										</div>
									</div>
								</td></tr>
							) : data.map((customer) => (
								<CustomerRow
									key={customer.id}
									customer={customer}
									onDelete={deleteCustomer}
									openId={openRowId}
									setOpenId={setOpenRowId}
									checked={checkedIds.has(customer.id)}
									onCheck={() => toggleRow(customer.id)}
								/>
							))}
						</tbody>
					</table>

					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('customers', 'smartpay')}
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
		</>
	)
}
