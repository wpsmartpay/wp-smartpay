import { __ } from '@wordpress/i18n'
import { ChevronDown, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { createHooks } from '@wordpress/hooks'
import { DeleteProduct, GetProducts } from '../../http/product'

const { useState, useEffect, useCallback, useRef } = wp.element

window.SMARTPAY_PRODUCT_HOOKS = createHooks()

/* ── Helpers ──────────────────────────────────────────────── */

const decodeHtmlEntity = (str) => {
	if (!str) return ''
	const txt = document.createElement('textarea')
	txt.innerHTML = str
	return txt.value
}

const currencySymbol = decodeHtmlEntity(window.smartpay?.options?.currencySymbol) || '$'

const fmt = (v) => {
	const n = parseFloat(v)
	return isNaN(n) ? null : currencySymbol + n.toFixed(2)
}

const effectivePrice = (row) => {
	const sale = parseFloat(row.sale_price)
	const base = parseFloat(row.base_price)
	return sale > 0 ? sale : (isNaN(base) ? 0 : base)
}

const colorIndex = (str) => {
	let h = 0
	for (let i = 0; i < (str || '').length; i++) h = (h + str.charCodeAt(i)) % 8
	return h
}

/* ── Sub-components ───────────────────────────────────────── */

const TypeBadge = ({ label }) => {
	const isSubscription = label === 'Subscription'
	return (
		<span className={`sp-badge sp-badge--dot ${isSubscription ? 'sp-badge--trial' : 'sp-badge--active'}`}>
			{label}
		</span>
	)
}

const PriceCell = ({ product }) => {
	const variations = product.variations || []

	if (variations.length > 1) {
		const prices = variations.map(effectivePrice)
		const min = Math.min(...prices)
		const max = Math.max(...prices)
		return (
			<span>
				{min === max
					? fmt(min)
					: <>{fmt(min)} <span style={{ color: 'var(--sp-text-subtle)' }}>–</span> {fmt(max)}</>
				}
			</span>
		)
	}

	if (variations.length === 1) {
		const v    = variations[0]
		const base = fmt(v.base_price)
		const sale = parseFloat(v.sale_price) > 0 ? fmt(v.sale_price) : null
		return sale ? (
			<span>
				<span style={{ textDecoration: 'line-through', color: 'var(--sp-text-subtle)', marginRight: 4 }}>{base}</span>
				{sale}
			</span>
		) : <span>{base || '—'}</span>
	}

	const base = fmt(product.base_price)
	const sale = parseFloat(product.sale_price) > 0 ? fmt(product.sale_price) : null
	return sale ? (
		<span>
			<span style={{ textDecoration: 'line-through', color: 'var(--sp-text-subtle)', marginRight: 4 }}>{base}</span>
			{sale}
		</span>
	) : <span>{base || <span style={{ color: 'var(--sp-text-subtle)' }}>—</span>}</span>
}

const ProductRow = ({ product, onDelete, openId, setOpenId, checked, onCheck }) => {
	const variations = product.variations || []

	const types = []
	if (variations.length > 0) {
		variations.forEach((v) => {
			const t = v.extra?.billing_type
			if (t && !types.includes(t)) types.push(t)
		})
	} else {
		const t = product.extra?.billing_type
		if (t) types.push(t)
	}

	const optionCount = variations.length
	const isOpen      = openId === product.id
	const initials    = (product.title || '?').substring(0, 2).toUpperCase()

	return (
		<tr className={checked ? 'sp-row--selected' : ''}>

			{/* Checkbox */}
			<td className="sp-col--check">
				<input
					type="checkbox"
					className="sp-checkbox sp-row-check"
					checked={checked}
					onChange={onCheck}
				/>
			</td>

			{/* Product name + ID */}
			<td>
				<div className="sp-customer">
					<div className="sp-avatar" data-color={colorIndex(product.title)}>
						{initials}
					</div>
					<div className="sp-customer__info">
						<Link
							to={`/products/${product.id}/edit`}
							className="sp-customer__name"
							style={{ textDecoration: 'none', color: 'inherit' }}
						>
							{product.title || __('Untitled', 'smartpay')}
						</Link>
						<div className="sp-customer__email">#{product.id}</div>
					</div>
				</div>
			</td>

			{/* Type badges */}
			<td>
				{types.length === 0
					? <span style={{ color: 'var(--sp-text-subtle)', fontSize: 12 }}>—</span>
					: <div style={{ display: 'flex', gap: 4, flexWrap: 'wrap' }}>
						{types.map((t) => <TypeBadge key={t} label={t} />)}
					</div>
				}
			</td>

			{/* Options count */}
			<td>
				{optionCount > 0
					? <span style={{ fontSize: 13 }}>
						{optionCount} {optionCount === 1
							? __('option', 'smartpay')
							: __('options', 'smartpay')}
					</span>
					: <span style={{ color: 'var(--sp-text-subtle)', fontSize: 12 }}>{__('Single', 'smartpay')}</span>
				}
			</td>

			{/* Price */}
			<td className="sp-cell--num">
				<PriceCell product={product} />
			</td>

			{/* Date */}
			<td className="sp-cell--muted sp-col--nowrap">
				{product.created_at ? new Date(product.created_at).toLocaleDateString() : '—'}
			</td>

			{/* Row actions */}
			<td className="sp-cell--actions">
				<div
					className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`}
					onClick={(e) => e.stopPropagation()}
				>
					<button
						className="sp-row-actions__trigger"
						aria-label={__('Actions', 'smartpay')}
						onClick={() => setOpenId(isOpen ? null : product.id)}
					>
						···
					</button>
					<div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
						{product?.extra?.product_preview_page_permalink && (
							<a
								href={product.extra.product_preview_page_permalink}
								target="_blank"
								rel="noopener noreferrer"
								className="sp-dropdown__item"
								onClick={() => setOpenId(null)}
							>
								{__('Preview', 'smartpay')}
							</a>
						)}
						<Link
							to={`/products/${product.id}/edit`}
							className="sp-dropdown__item"
							onClick={() => setOpenId(null)}
						>
							{__('Edit', 'smartpay')}
						</Link>
						<div className="sp-dropdown__divider" />
						<button
							className="sp-dropdown__item sp-dropdown__item--destructive"
							onClick={() => { setOpenId(null); onDelete(product.id) }}
						>
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

export const ProductList = () => {
	const { Header } = window.WPSmartPayUI

	const [data,         setData]         = useState([])
	const [isLoading,    setIsLoading]    = useState(false)
	const [searchQuery,  setSearchQuery]  = useState('')
	const [debouncedSearch, setDebouncedSearch] = useState('')
	const [openRowId,    setOpenRowId]    = useState(null)
	const [actionOpen,   setActionOpen]   = useState(false)
	const [checkedIds,   setCheckedIds]   = useState(new Set())
	const [perPage,      setPerPage]      = useState(20)
	const [pagination,   setPagination]   = useState({
		current_page: 1, last_page: 1, total: 0, from: 0, to: 0,
	})

	/* Debounce search → page 1 on change */
	useEffect(() => {
		const t = setTimeout(() => setDebouncedSearch(searchQuery), 400)
		return () => clearTimeout(t)
	}, [searchQuery])

	const fetchProducts = useCallback(async (page = 1, search = '') => {
		setIsLoading(true)
		try {
			const result = await GetProducts({ page, perPage, search, sortBy: 'id:desc' })
			const { data: rows = [], ...paginationData } = result
			setData(rows)
			setPagination(paginationData)
			setCheckedIds(new Set())
		} catch (e) {
			console.error('Failed to load products', e)
		} finally {
			setIsLoading(false)
		}
	}, [perPage])

	useEffect(() => {
		fetchProducts(1, debouncedSearch)
	}, [fetchProducts, debouncedSearch])

	/* Close all dropdowns on outside click */
	useEffect(() => {
		const close = () => { setOpenRowId(null); setActionOpen(false) }
		document.addEventListener('click', close)
		return () => document.removeEventListener('click', close)
	}, [])

	/* Delete */
	const deleteProduct = async (id) => {
		const deleted = await DeleteProduct(id)
		if (deleted) fetchProducts(pagination.current_page, debouncedSearch)
	}

	/* Bulk delete */
	const bulkDelete = async () => {
		if (!window.confirm(
			__('Delete all selected products? This cannot be undone.', 'smartpay')
		)) return
		await Promise.all([...checkedIds].map((id) => {
			const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
			return fetch(`${baseUrl}/v1/products/${id}`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
			})
		}))
		fetchProducts(1, debouncedSearch)
	}

	/* Checkbox helpers */
	const allChecked  = data.length > 0 && checkedIds.size === data.length
	const someChecked = checkedIds.size > 0 && checkedIds.size < data.length

	const toggleAll = () => {
		setCheckedIds(allChecked || someChecked
			? new Set()
			: new Set(data.map((p) => p.id))
		)
	}

	const toggleRow = (id) => {
		const next = new Set(checkedIds)
		next.has(id) ? next.delete(id) : next.add(id)
		setCheckedIds(next)
	}

	/* Pagination */
	const goToPage = (page) => fetchProducts(page, debouncedSearch)

	const hasSelection = checkedIds.size > 0

	return (
		<>
			<Header
				title={__('Products', 'smartpay')}
				subtitle={__('Manage your products here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Products', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Manage your products here', 'smartpay')}</p>
				</div>

				{/* Toolbar — no layout shift, all buttons always present */}
				<div className="sp-toolbar">
					<div className="sp-search">
						<Search className="sp-search__icon" size={14} />
						<input
							type="search"
							className="sp-search__input"
							placeholder={__('Search by product name', 'smartpay')}
							value={searchQuery}
							onChange={(e) => setSearchQuery(e.target.value)}
						/>
					</div>

					{/* Selection count pill — only appears when rows checked */}
					{hasSelection && (
						<span className="sp-selection-count">
							{checkedIds.size} {__('selected', 'smartpay')}
							<button
								className="sp-selection-count__clear"
								onClick={() => setCheckedIds(new Set())}
								title={__('Clear selection', 'smartpay')}
							>
								✕
							</button>
						</span>
					)}

					<div className="sp-toolbar__spacer" />

					{/* Bulk action dropdown — always visible */}
					<div
						className="sp-action-dropdown"
						onClick={(e) => e.stopPropagation()}
					>
						<button
							className="sp-btn sp-btn--outline"
							disabled={!hasSelection}
							onClick={() => setActionOpen((o) => !o)}
						>
							{__('Select Action', 'smartpay')}
							<ChevronDown size={14} style={{ marginLeft: 2, opacity: 0.6 }} />
						</button>
						<div className={`sp-dropdown${actionOpen ? ' sp-dropdown--open' : ''}`}>
							<button
								className="sp-dropdown__item sp-dropdown__item--destructive"
								onClick={() => {
									setActionOpen(false)
									if (!hasSelection) {
										window.alert(__('Please select one or more products first.', 'smartpay'))
										return
									}
									bulkDelete()
								}}
							>
								{__('Delete selected', 'smartpay')}
							</button>
						</div>
					</div>

					<Link to="/products/create" className="sp-btn sp-btn--primary" style={{ textDecoration: 'none' }}>
						+ {__('Add Product', 'smartpay')}
					</Link>
				</div>

				{/* Table card */}
				<div className="sp-table-card">

					<table className="sp-table">
						<thead>
							<tr>
								<th className="sp-col--check">
									<input
										type="checkbox"
										className="sp-checkbox sp-select-all"
										checked={allChecked}
										ref={(el) => { if (el) el.indeterminate = someChecked }}
										onChange={toggleAll}
									/>
								</th>
								<th>{__('Product', 'smartpay')}</th>
								<th>{__('Type', 'smartpay')}</th>
								<th>{__('Options', 'smartpay')}</th>
								<th className="sp-col--num">{__('Price', 'smartpay')}</th>
								<th>{__('Date', 'smartpay')}</th>
								<th className="sp-col--actions"></th>
							</tr>
						</thead>
						<tbody>
							{isLoading ? (
								<tr>
									<td colSpan={7} className="sp-state-loading">
										{__('Loading…', 'smartpay')}
									</td>
								</tr>
							) : data.length === 0 ? (
								<tr>
									<td colSpan={7}>
										<div className="sp-empty">
											<div className="sp-empty__icon">📦</div>
											<div className="sp-empty__title">{__('No products found', 'smartpay')}</div>
											<div className="sp-empty__desc">
												{searchQuery
													? __('No products match your search. Try a different term.', 'smartpay')
													: __('Add your first product to get started.', 'smartpay')
												}
											</div>
											{!searchQuery && (
												<Link to="/products/create" className="sp-btn sp-btn--primary" style={{ textDecoration: 'none' }}>
													+ {__('Add Product', 'smartpay')}
												</Link>
											)}
										</div>
									</td>
								</tr>
							) : data.map((product) => (
								<ProductRow
									key={product.id}
									product={product}
									onDelete={deleteProduct}
									openId={openRowId}
									setOpenId={setOpenRowId}
									checked={checkedIds.has(product.id)}
									onCheck={() => toggleRow(product.id)}
								/>
							))}
						</tbody>
					</table>

					{/* Pagination */}
					{pagination.total > 0 && (
						<div className="sp-pagination">
							<div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
								<span className="sp-pagination__info">
									{__('Showing', 'smartpay')} {pagination.from}–{pagination.to} {__('of', 'smartpay')} {pagination.total} {__('products', 'smartpay')}
								</span>
								<select
									className="sp-filter-select"
									style={{ fontSize: 12, padding: '0 22px 0 8px' }}
									value={perPage}
									onChange={(e) => { setPerPage(Number(e.target.value)) }}
								>
									{PER_PAGE_OPTIONS.map((n) => (
										<option key={n} value={n}>{n} {__('per page', 'smartpay')}</option>
									))}
								</select>
							</div>
							<div className="sp-pagination__nav">
								<button
									className="sp-pagination__btn"
									disabled={pagination.current_page <= 1}
									onClick={() => goToPage(pagination.current_page - 1)}
								>
									‹
								</button>
								<span style={{ padding: '0 10px', fontSize: 12, color: 'var(--sp-text-muted)' }}>
									{pagination.current_page} / {pagination.last_page}
								</span>
								<button
									className="sp-pagination__btn"
									disabled={pagination.current_page >= pagination.last_page}
									onClick={() => goToPage(pagination.current_page + 1)}
								>
									›
								</button>
							</div>
						</div>
					)}

				</div>
			</div>
		</>
	)
}
