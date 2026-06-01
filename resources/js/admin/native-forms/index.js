import apiFetch from '@wordpress/api-fetch'
import { ChevronDown, Search } from 'lucide-react'
import { NewFormModal } from './NewFormModal'

const { __ } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

/* ── HTTP ─────────────────────────────────────────────────── */

const GetNativeForms = async ({ page = 1, perPage = 10, search = '' }) => {
	const params = new URLSearchParams({
		page,
		per_page: perPage,
		...(search && { search }),
	})
	const base = smartpay.restUrl.replace(/\/$/, '')
	const response = await apiFetch({
		url: `${base}/v1/native-forms?${params.toString()}`,
		headers: { 'X-WP-Nonce': smartpay.apiNonce },
	})
	return response?.forms || {}
}

const DeleteNativeForm = async (id) => {
	if (!window.confirm(__('Delete this form? This cannot be undone.', 'smartpay'))) return false
	try {
		const base = smartpay.restUrl.replace(/\/$/, '')
		await apiFetch({
			url: `${base}/v1/native-forms/${id}`,
			method: 'DELETE',
			headers: { 'X-WP-Nonce': smartpay.apiNonce },
		})
		return true
	} catch (error) {
		window.alert(error.message || __('Failed to delete form', 'smartpay'))
		return false
	}
}

/* ── Helpers ──────────────────────────────────────────────── */

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

const colorIndex = (str) => {
	let h = 0
	for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
	return h
}

const copyShortcode = (code) => {
	const showToast = () => {
		const toast = document.createElement('div')
		toast.textContent = __('Shortcode copied!', 'smartpay')
		toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#111827;color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,0.15)'
		document.body.appendChild(toast)
		setTimeout(() => toast.remove(), 2000)
	}
	if (navigator.clipboard) {
		navigator.clipboard.writeText(code).then(showToast)
	} else {
		const ta = document.createElement('textarea')
		ta.value = code
		ta.style.cssText = 'position:fixed;opacity:0'
		document.body.appendChild(ta)
		ta.select()
		document.execCommand('copy')
		document.body.removeChild(ta)
		showToast()
	}
}

/* ── Row ──────────────────────────────────────────────────── */

const NativeFormRow = ({ form, onDelete, openId, setOpenId, checked, onCheck }) => {
	const isOpen    = openId === form.id
	const initials  = (form.title || '?').substring(0, 2).toUpperCase()
	const isPublish = form.status === 'publish'
	const goal      = form.goal

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td>
				<div className="sp-customer">
					<div className="sp-avatar" data-color={colorIndex(form.title)}>
						{initials}
					</div>
					<div className="sp-customer__info">
						<a href={form.edit_url} className="sp-customer__name"
							style={{ textDecoration: 'none', color: 'inherit' }}>
							{form.title || __('(Untitled)', 'smartpay')}
						</a>
						<div className="sp-customer__email">#{form.id}</div>
					</div>
				</div>
			</td>

			<td>
				{form.shortcode ? (
					<code
						onClick={() => copyShortcode(form.shortcode)}
						title={__('Click to copy', 'smartpay')}
						style={{
							cursor: 'pointer',
							background: 'var(--sp-surface-muted)',
							border: '1px solid var(--sp-border)',
							borderRadius: 4,
							padding: '2px 8px',
							fontSize: 12,
							whiteSpace: 'nowrap',
							userSelect: 'all',
						}}>
						{form.shortcode}
					</code>
				) : (
					<span style={{ color: 'var(--sp-text-subtle)' }}>—</span>
				)}
			</td>

			<td>
				<span className={`sp-badge sp-badge--dot ${isPublish ? 'sp-badge--active' : 'sp-badge--expired'}`}>
					{isPublish ? __('Published', 'smartpay') : (form.status || 'draft')}
				</span>
			</td>

			<td className="sp-cell--muted sp-col--nowrap">{form.date || '—'}</td>

			<td>
				{goal?.enabled ? (
					<div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 3 }}>
						<div style={{ width: 80, height: 5, background: 'var(--sp-border)', borderRadius: 3, overflow: 'hidden' }}>
							<div style={{ width: `${Math.min(100, goal.percentage || 0)}%`, height: '100%', background: '#22c55e', borderRadius: 3 }} />
						</div>
						<span style={{ fontSize: 10, color: 'var(--sp-text-subtle)' }}>
							{Math.round(goal.current || 0)} / {Math.round(goal.target || 0)}
						</span>
					</div>
				) : (
					<span style={{ color: 'var(--sp-text-subtle)' }}>—</span>
				)}
			</td>

			<td className="sp-cell--actions">
				<div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}>
					<button className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : form.id)}>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						{form.preview_url && (
							<a href={form.preview_url} target="_blank" rel="noopener noreferrer"
								className="sp-dropdown__item" onClick={() => setOpenId(null)}>
								{__('Preview', 'smartpay')}
							</a>
						)}
						<a href={form.edit_url} className="sp-dropdown__item"
							onClick={() => setOpenId(null)}>
							{__('Edit', 'smartpay')}
						</a>
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

export const NativeFormList = () => {
	const { Header } = window.WPSmartPayUI

	const [data,            setData]            = useState([])
	const [isLoading,       setIsLoading]       = useState(false)
	const [searchQuery,     setSearchQuery]     = useState('')
	const [debouncedSearch, setDebouncedSearch] = useState('')
	const [showModal,       setShowModal]       = useState(false)
	const [openRowId,       setOpenRowId]       = useState(null)
	const [actionOpen,      setActionOpen]      = useState(false)
	const [checkedIds,      setCheckedIds]      = useState(new Set())
	const [perPage,         setPerPage]         = useState(20)
	const [pagination,      setPagination]      = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	const addNewUrl = smartpay.adminUrl.replace(/admin\.php$/, 'post-new.php') + '?post_type=smartpay_form'

	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchForms = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetNativeForms({ page, perPage, search })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load native forms', e)
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
		const deleted = await DeleteNativeForm(id)
		if (deleted) fetchForms(pagination.current_page, debouncedSearch)
	}

	const bulkDelete = async () => {
		if (!window.confirm(__('Delete all selected forms? This cannot be undone.', 'smartpay'))) return
		await Promise.all([...checkedIds].map((id) => {
			const base = smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${base}/v1/native-forms/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': smartpay.apiNonce },
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

	const openModal  = (e) => { e.stopPropagation(); setShowModal(true) }
	const closeModal = () => setShowModal(false)
	const goBlank    = () => { closeModal(); window.location.href = addNewUrl }

	return (
		<>
			<Header
				title={__('Forms', 'smartpay')}
				subtitle={__('Create and manage payment forms', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Forms', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Manage your payment forms here', 'smartpay')}</p>
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
										window.alert(__('Please select one or more forms first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>

					<button className="sp-btn sp-btn--primary" onClick={openModal}>
						+ {__('New Form', 'smartpay')}
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
								<th>{__('Name', 'smartpay')}</th>
								<th>{__('Shortcode', 'smartpay')}</th>
								<th>{__('Status', 'smartpay')}</th>
								<th>{__('Created', 'smartpay')}</th>
								<th>{__('Goal', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={7} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={7}>
									<div className="sp-empty">
										<div className="sp-empty__icon">📝</div>
										<div className="sp-empty__title">{__('No forms found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery
												? __('No forms match your search. Try a different term.', 'smartpay')
												: __('Create your first form to start collecting payments.', 'smartpay')
											}
										</div>
										{!searchQuery && (
											<button className="sp-btn sp-btn--primary" onClick={openModal}>
												+ {__('New Form', 'smartpay')}
											</button>
										)}
									</div>
								</td></tr>
							) : data.map((form) => (
								<NativeFormRow
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

			<NewFormModal open={showModal} onClose={closeModal} onBlank={goBlank} />
		</>
	)
}
