import { __ } from '@wordpress/i18n'
import { ChevronDown, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Delete, GetForms } from '../http/form'
import { createHooks } from '@wordpress/hooks'
const { useState, useEffect, useCallback } = wp.element

window.SMARTPAY_FORM_HOOKS = createHooks()

/* ── Helpers ──────────────────────────────────────────────── */

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

const colorIndex = (str) => {
	let h = 0
	for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
	return h
}

const fmtDate = (val) => {
	if (!val) return '—'
	const d = new Date(val)
	return isNaN(d) ? val : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

const statusBadgeClass = (status) => {
	if (status === 'publish') return 'sp-badge sp-badge--success'
	if (status === 'draft')   return 'sp-badge sp-badge--neutral'
	return 'sp-badge sp-badge--warning'
}

/* ── Row ──────────────────────────────────────────────────── */

const FormRow = ({ form, onDelete, openId, setOpenId, checked, onCheck }) => {
	const isOpen   = openId === form.id
	const initials = (form.title || '?').substring(0, 2).toUpperCase()

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td className="sp-cell--muted sp-col--nowrap" style={{ fontVariantNumeric: 'tabular-nums', fontSize: 12 }}>
				#{form.id}
			</td>

			<td>
				<div className="sp-customer">
					<div className="sp-avatar" data-color={colorIndex(form.title)}>
						{initials}
					</div>
					<div className="sp-customer__info">
						<Link to={`/${form.id}/edit`} className="sp-customer__name"
							style={{ textDecoration: 'none', color: 'inherit' }}>
							{form.title || __('Untitled', 'smartpay')}
						</Link>
					</div>
				</div>
			</td>

			<td>
				<span className={statusBadgeClass(form.status)}>
					{form.status || 'publish'}
				</span>
			</td>

			<td className="sp-cell--muted sp-col--nowrap">{fmtDate(form.created_at)}</td>

			<td className="sp-cell--muted sp-col--nowrap">{fmtDate(form.updated_at)}</td>

			<td className="sp-cell--actions">
				<div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}>
					<button className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : form.id)}>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						{form?.extra?.form_preview_page_permalink && (
							<a href={form.extra.form_preview_page_permalink} target="_blank"
								rel="noopener noreferrer" className="sp-dropdown__item"
								onClick={() => setOpenId(null)}>
								{__('Preview', 'smartpay')}
							</a>
						)}
						<Link to={`/${form.id}/edit`} className="sp-dropdown__item"
							onClick={() => setOpenId(null)}>
							{__('Edit', 'smartpay')}
						</Link>
						<div className="sp-dropdown__divider" />
						<button className="sp-dropdown__item sp-dropdown__item--destructive"
							onClick={() => { setOpenId(null); onDelete(form.id) }}>
							{__('Delete', 'smartpay')}
						</button>
					</div>
				</div>
			</td>
		</tr>
	)
}

/* ── Main list ────────────────────────────────────────────── */

export const FormList = () => {
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

	const fetchForms = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetForms({ page, perPage, search, sortBy: 'id:desc' })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load forms', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage])

	useEffect(() => {
		fetchForms(1, debouncedSearch)
	}, [fetchForms, debouncedSearch])

	useEffect(() => {
		const close = () => { setOpenRowId(null); setActionOpen(false) }
		document.addEventListener('click', close)
		return () => document.removeEventListener('click', close)
	}, [])

	const deleteForm = async (id) => {
		const deleted = await Delete(id)
		if (deleted) fetchForms(pagination.current_page, debouncedSearch)
	}

	const bulkDelete = async () => {
		if (!window.confirm(__('Delete all selected forms? This cannot be undone.', 'smartpay'))) return
		await Promise.all([...checkedIds].map((id) => {
			const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${baseUrl}/v1/forms/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
			})
		}))
		fetchForms(1, debouncedSearch)
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

	const goToPage = (page) => fetchForms(page, debouncedSearch)

	return (
		<>
			<Header
				title={__('Forms', 'smartpay')}
				subtitle={__('Manage your forms here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Forms (Legacy)', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Build and manage your payment forms', 'smartpay')}</p>
				</div>

				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input type="search" className="sp-search__input"
							placeholder={__('Search by form name', 'smartpay')}
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
										window.alert(__('Please select one or more forms first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>

					<Link to="create" className="sp-btn sp-btn--primary" style={{ textDecoration: 'none' }}>
						+ {__('Add New', 'smartpay')}
					</Link>
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
								<th style={{ width: 60 }}>{__('ID', 'smartpay')}</th>
								<th>{__('Form', 'smartpay')}</th>
								<th>{__('Status', 'smartpay')}</th>
								<th>{__('Start Date', 'smartpay')}</th>
								<th>{__('Last Updated', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={7} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={7}>
									<div className="sp-empty">
										<div className="sp-empty__icon">📋</div>
										<div className="sp-empty__title">{__('No forms found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery
												? __('No forms match your search. Try a different term.', 'smartpay')
												: __('Create your first form to start collecting payments.', 'smartpay')
											}
										</div>
										{!searchQuery && (
											<Link to="create" className="sp-btn sp-btn--primary" style={{ textDecoration: 'none' }}>
												+ {__('Add New', 'smartpay')}
											</Link>
										)}
									</div>
								</td></tr>
							) : data.map((form) => (
								<FormRow
									key={form.id}
									form={form}
									onDelete={deleteForm}
									openId={openRowId}
									setOpenId={setOpenRowId}
									checked={checkedIds.has(form.id)}
									onCheck={() => toggleRow(form.id)}
								/>
							))}
						</tbody>
					</table>

					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('forms', 'smartpay')}
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
