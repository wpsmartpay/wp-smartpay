import { __ } from '@wordpress/i18n'
import { Eye, Printer, Search, Share2 } from 'lucide-react'
import { Link } from 'react-router-dom'
import { useState, useEffect, useCallback } from '@wordpress/element'
import { GetFormSubmissions } from '../../http/form-data'

/* ── Helpers ──────────────────────────────────────────────── */

const PER_PAGE_OPTIONS = [10, 25, 50, 100]

const statusClass = (status) => {
	const map = {
		completed: 'sp-badge--active',
		pending:   'sp-badge--pending',
		failed:    'sp-badge--failed',
	}
	return map[(status || '').toLowerCase()] || 'sp-badge--pending'
}

const capitalize = (str) => {
	if (!str) return ''
	return str.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

const flattenFormData = (obj, prefix = '') => {
	const rows = []
	if (!obj || typeof obj !== 'object') return rows
	for (const [key, val] of Object.entries(obj)) {
		const label = prefix ? `${prefix} › ${capitalize(key)}` : capitalize(key)
		if (val && typeof val === 'object' && !Array.isArray(val)) {
			rows.push(...flattenFormData(val, label))
		} else {
			rows.push({ label, value: Array.isArray(val) ? val.join(', ') : (val ?? '—') })
		}
	}
	return rows
}

const quickView = (payment) => {
	const formData  = payment.extra?.form_data || payment.data?.form_data || {}
	const fields    = flattenFormData(formData)
	const formTitle = payment.data?.form_title || __('Form Submission', 'smartpay')

	const overlay = document.createElement('div')
	overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99998;display:flex;align-items:center;justify-content:center;padding:20px'

	const dialog = document.createElement('div')
	dialog.style.cssText = 'background:#fff;border-radius:12px;max-width:600px;width:100%;max-height:80vh;overflow:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)'
	dialog.innerHTML = `
		<div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between">
			<div>
				<div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px">${formTitle}</div>
				<h3 style="margin:0;font-size:18px;font-weight:600;color:#111827">${__('Submission', 'smartpay')} #${payment.id}</h3>
			</div>
			<button id="sp-qv-close" style="background:none;border:none;cursor:pointer;padding:8px;color:#6b7280">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
			</button>
		</div>
		<div style="padding:24px">
			${fields.length === 0 ? '<p style="color:#6b7280;text-align:center;padding:20px 0">No submission data available.</p>' : `
			<table style="width:100%;border-collapse:collapse">
				<tbody>
					${fields.map(({ label, value }) => `
						<tr style="border-bottom:1px solid #f3f4f6">
							<td style="padding:12px 0;font-size:13px;font-weight:500;color:#374151;width:40%;vertical-align:top">${label}</td>
							<td style="padding:12px 0;font-size:13px;color:#111827;text-align:right;word-break:break-all">${String(value)}</td>
						</tr>
					`).join('')}
				</tbody>
			</table>
			`}
		</div>
	`

	overlay.appendChild(dialog)
	document.body.appendChild(overlay)
	document.getElementById('sp-qv-close').addEventListener('click', () => overlay.remove())
	overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove() })
}

const shareLink = (payment) => {
	const url = `${window.smartpay.adminUrl}admin.php?page=smartpay#/payments/${payment.id}`
	if (!navigator.clipboard) return
	navigator.clipboard.writeText(url).then(() => {
		const toast = document.createElement('div')
		toast.textContent = __('Link copied!', 'smartpay')
		toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#111827;color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,0.15)'
		document.body.appendChild(toast)
		setTimeout(() => toast.remove(), 2000)
	})
}

/* ── Row ──────────────────────────────────────────────────── */

const FormDataRow = ({ row, checked, onCheck }) => {
	const formData  = row.extra?.form_data || row.data?.form_data || {}
	const fd        = formData?.name || {}
	const name      = fd.first_name || fd.last_name
		? `${fd.first_name || ''} ${fd.last_name || ''}`.trim()
		: (formData?.name ? String(formData.name) : '—')
	const email     = formData?.email || '—'
	const formTitle = row.data?.form_title || row.data?.form_id || '—'
	const dateLabel = row.created_at
		? new Date(row.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
		: '—'

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>
			<td className="sp-col--check">
				<input type="checkbox" className="sp-checkbox sp-row-check"
					checked={checked} onChange={onCheck} />
			</td>

			<td>
				<Link to={`/payments/${row.id}`} style={{ color: 'var(--sp-brand)', fontWeight: 500, textDecoration: 'none', fontSize: 13 }}>
					#{row.id}
				</Link>
			</td>

			<td style={{ fontSize: 13, color: 'var(--sp-text)' }}>{formTitle}</td>

			<td style={{ fontSize: 13, color: 'var(--sp-text)' }}>{name}</td>

			<td className="sp-cell--muted" style={{ fontSize: 13 }}>{email}</td>

			<td className="sp-cell--muted sp-col--nowrap">{dateLabel}</td>

			<td>
				<span className={`sp-badge sp-badge--dot ${statusClass(row.status)}`}>
					{row.status || 'pending'}
				</span>
			</td>

			<td className="sp-cell--actions" style={{ textAlign: 'right' }}>
				<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 6 }}>
					<button className="sp-btn sp-btn--icon" title={__('Quick View', 'smartpay')}
						onClick={() => quickView(row)}>
						<Eye size={14} />
					</button>
					<button className="sp-btn sp-btn--icon" title={__('Copy link', 'smartpay')}
						onClick={() => shareLink(row)}>
						<Share2 size={14} />
					</button>
				</div>
			</td>
		</tr>
	)
}

/* ── Bulk print ───────────────────────────────────────────── */

const doBulkPrint = (rows) => {
	if (!rows.length) return
	const win = window.open('', '_blank')
	win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>${__('Form Submissions', 'smartpay')}</title><style>
		* { box-sizing: border-box; margin: 0; padding: 0; }
		body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #111827; padding: 20px; }
		h1 { font-size: 20px; font-weight: 700; margin-bottom: 24px; }
		.submissions { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
		.card { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
		.card-header { background: #f9fafb; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
		.card-header-title { font-size: 13px; font-weight: 600; color: #374151; }
		.card-header-id { font-size: 12px; color: #6b7280; }
		.card-body { padding: 16px; }
		.card-body table { width: 100%; border-collapse: collapse; }
		.card-body tr { border-bottom: 1px solid #f3f4f6; }
		.card-body tr:last-child { border-bottom: none; }
		.card-body td { padding: 8px 0; font-size: 13px; }
		.card-body td:first-child { font-weight: 500; color: #6b7280; width: 40%; }
		.empty { color: #9ca3af; font-size: 13px; }
		@media print { .submissions { display: block; } .card { margin-bottom: 24px; } }
	</style></head><body>
		<h1>${__('Form Submissions', 'smartpay')} (${rows.length})</h1>
		<div class="submissions">
			${rows.map((row) => {
				const formData = row.extra?.form_data || row.data?.form_data || {}
				const fields   = flattenFormData(formData)
				const title    = row.data?.form_title || __('Form Submission', 'smartpay')
				return `
					<div class="card">
						<div class="card-header">
							<div class="card-header-title">${title}</div>
							<div class="card-header-id">#${row.id}</div>
						</div>
						<div class="card-body">
							${fields.length === 0
								? '<p class="empty">No data</p>'
								: `<table><tbody>${fields.map(({ label, value }) => `<tr><td>${label}</td><td>${String(value)}</td></tr>`).join('')}</tbody></table>`
							}
						</div>
					</div>
				`
			}).join('')}
		</div>
	</body></html>`)
	win.document.close()
	win.print()
}

/* ── Main list ────────────────────────────────────────────── */

export const FormData = () => {
	const { Header } = window.WPSmartPayUI

	const [data,            setData]            = useState([])
	const [isLoading,       setIsLoading]       = useState(false)
	const [searchQuery,     setSearchQuery]     = useState('')
	const [debouncedSearch, setDebouncedSearch] = useState('')
	const [checkedIds,      setCheckedIds]      = useState(new Set())
	const [perPage,         setPerPage]         = useState(20)
	const [pagination,      setPagination]      = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchSubmissions = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetFormSubmissions({ page, perPage, search, sortBy: 'id:desc' })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load submissions', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage])

	useEffect(() => {
		fetchSubmissions(1, debouncedSearch)
	}, [fetchSubmissions, debouncedSearch])

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

	const goToPage = (page) => fetchSubmissions(page, debouncedSearch)

	const handlePrint = () => {
		const selected = data.filter((r) => checkedIds.has(r.id))
		doBulkPrint(selected)
	}

	return (
		<>
			<Header
				title={__('Form Data', 'smartpay')}
				subtitle={__('View all form submissions', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Form Data', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('View all form submissions', 'smartpay')}</p>
				</div>

				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input type="search" className="sp-search__input"
							placeholder={__('Search submissions…', 'smartpay')}
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

					{hasSelection && (
						<button className="sp-btn sp-btn--outline" onClick={handlePrint}>
							<Printer size={13} style={{ marginRight: 5 }} />
							{__('Print Selected', 'smartpay')}
						</button>
					)}
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
								<th>{__('ID', 'smartpay')}</th>
								<th>{__('Form', 'smartpay')}</th>
								<th>{__('Name', 'smartpay')}</th>
								<th>{__('Email', 'smartpay')}</th>
								<th>{__('Date', 'smartpay')}</th>
								<th>{__('Status', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr><td colSpan={8} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
							) : data.length === 0 ? (
								<tr><td colSpan={8}>
									<div className="sp-empty">
										<div className="sp-empty__icon">📋</div>
										<div className="sp-empty__title">{__('No submissions found', 'smartpay')}</div>
										<div className="sp-empty__desc">
											{searchQuery
												? __('No submissions match your search. Try a different term.', 'smartpay')
												: __('Form submissions will appear here once customers submit a form.', 'smartpay')
											}
										</div>
									</div>
								</td></tr>
							) : data.map((row) => (
								<FormDataRow
									key={row.id}
									row={row}
									checked={checkedIds.has(row.id)}
									onCheck={() => toggleRow(row.id)}
								/>
							))}
						</tbody>
					</table>

					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('submissions', 'smartpay')}
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
