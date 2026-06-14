import { CATEGORIES, TEMPLATES } from './templates'

const { __ }                = wp.i18n
const { useState, useMemo } = wp.element
const { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } = window.WPSmartPayUI

/* ─── Blank/New Option Card (soft gradient layout) ───────────────── */
const BlankTemplateCard = ( { onUse } ) => {
	const [ hov, setHov ] = useState( false )
	return (
		<div
			onMouseEnter={ () => setHov( true ) }
			onMouseLeave={ () => setHov( false ) }
			onClick={ onUse }
			role="button"
			tabIndex={ 0 }
			onKeyDown={ ( e ) => { if ( e.key === 'Enter' || e.key === ' ' ) onUse() } }
			style={ {
				borderRadius:  '16px',
				background:    'linear-gradient(135deg, var(--sp-brand-light, #eef0f9) 0%, #e0f2fe 100%)', // Soft brand-blue gradient
				padding:       '24px',
				display:       'flex',
				flexDirection: 'column',
				justifyContent: 'space-between',
				minHeight:     '250px',
				cursor:        'pointer',
				border:        'none',
				boxShadow:     hov ? '0 8px 30px rgba(41, 60, 129, 0.12)' : '0 2px 8px rgba(0,0,0,0.02)',
				transform:     hov ? 'translateY(-2px)' : 'none',
				transition:    'all 0.2s ease',
			} }
		>
			{/* Icon with Blue background */}
			<div style={ {
				width:          '36px',
				height:         '36px',
				borderRadius:   '50%',
				background:     'var(--sp-brand, #293c81)',
				display:        'flex',
				alignItems:     'center',
				justifyContent: 'center',
				color:          '#ffffff',
				marginBottom:   '16px',
			} }>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
					<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
				</svg>
			</div>

			{/* Info */}
			<div style={ { flex: 1, display: 'flex', flexDirection: 'column', gap: '8px' } }>
				<h3 style={ { margin: 0, fontSize: '16px', fontWeight: 700, color: 'var(--sp-brand-dark, #1e2f6e)', lineHeight: 1.3 } }>
					{ __( 'Create Your Template', 'smartpay' ) }
				</h3>
				<p style={ { margin: 0, fontSize: '12.5px', color: 'var(--sp-brand, #293c81)', lineHeight: 1.4 } }>
					{ __( 'A structured guide to help educators deliver effective and engaging lessons. Tailor your teaching moments with precision and purpose.', 'smartpay' ) }
				</p>
			</div>

			{/* Action Footer */}
			<div style={ { display: 'flex', alignItems: 'center', gap: '10px', marginTop: '16px' } }>
				<button
					onClick={ (e) => { e.stopPropagation(); onUse() } }
					style={ {
						background:   '#111827',
						color:        '#ffffff',
						border:       'none',
						borderRadius: '9999px',
						padding:      '8px 20px',
						fontSize:     '13px',
						fontWeight:   600,
						cursor:       'pointer',
					} }
				>
					{ __( 'Create One', 'smartpay' ) }
				</button>
				<div style={ {
					width:          '32px',
					height:         '32px',
					borderRadius:   '50%',
					background:     'rgba(255, 255, 255, 0.4)',
					display:        'flex',
					alignItems:     'center',
					justifyContent: 'center',
					color:          'var(--sp-brand, #293c81)',
				} }>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
						<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
					</svg>
				</div>
			</div>
		</div>
	)
}

/* ─── Premium Template Card ───────────────────────────────────────── */
const TemplateCard = ( { template, onUse } ) => {
	const [ hov, setHov ] = useState( false )
	const fieldCount      = template.fields.filter( ( f ) => f !== 'submit' ).length

	// Category specific symbols matching reference icons
	const getCategorySymbol = (cat) => {
		switch (cat) {
			case 'donation':
				return '☀️'
			case 'subscription':
				return '✉️'
			case 'checkout':
			default:
				return '🌐'
		}
	}

	return (
		<div
			onMouseEnter={ () => setHov( true ) }
			onMouseLeave={ () => setHov( false ) }
			onClick={ () => onUse( template ) }
			role="button"
			tabIndex={ 0 }
			onKeyDown={ ( e ) => { if ( e.key === 'Enter' || e.key === ' ' ) onUse( template ) } }
			style={ {
				borderRadius:  '16px',
				background:    '#ffffff',
				border:        `1px solid ${ hov ? 'var(--sp-brand, #293c81)' : '#e5e7eb' }`,
				padding:       '24px',
				display:       'flex',
				flexDirection: 'column',
				justifyContent: 'space-between',
				minHeight:     '250px',
				cursor:        'pointer',
				boxShadow:     hov ? '0 8px 30px rgba(0,0,0,0.06)' : '0 2px 8px rgba(0,0,0,0.02)',
				transform:     hov ? 'translateY(-2px)' : 'none',
				transition:    'all 0.2s ease',
			} }
		>
			{/* Icon container */}
			<div style={ {
				width:          '36px',
				height:         '36px',
				borderRadius:   '50%',
				background:     '#f3f4f6',
				display:        'flex',
				alignItems:     'center',
				justifyContent: 'center',
				marginBottom:   '16px',
			} }>
				<span style={ { fontSize: '18px' } }>{ getCategorySymbol(template.category) }</span>
			</div>

			{/* Details */}
			<div style={ { flex: 1, display: 'flex', flexDirection: 'column', gap: '6px' } }>
				<div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between' } }>
					<span style={ {
						fontSize:      '10px',
						fontWeight:    600,
						color:         'var(--sp-brand, #293c81)',
						background:    'var(--sp-brand-light, #eef0f9)',
						borderRadius:  '9999px',
						padding:       '2px 9px',
						textTransform: 'capitalize',
					} }>
						{ template.category }
					</span>
					<span style={ { fontSize: '11px', color: '#9ca3af' } }>
						{ fieldCount } { __( 'fields', 'smartpay' ) }
					</span>
				</div>

				<h3 style={ { margin: '6px 0 0', fontSize: '15px', fontWeight: 700, color: '#111827', lineHeight: 1.3 } }>
					{ template.name }
				</h3>

				<p style={ {
					margin:          '6px 0 0',
					fontSize:        '12px',
					color:           '#6b7280',
					lineHeight:      1.5,
					display:         '-webkit-box',
					WebkitLineClamp: 3,
					WebkitBoxOrient: 'vertical',
					overflow:        'hidden',
				} }>
					{ template.description || __( 'A structured payment form template tailored to capture details with precision and purpose.', 'smartpay' ) }
				</p>
			</div>

			{/* Action Footer */}
			<div style={ { display: 'flex', alignItems: 'center', gap: '10px', marginTop: '16px' } }>
				<button
					onClick={ (e) => { e.stopPropagation(); onUse(template) } }
					style={ {
						background:   '#111827',
						color:        '#ffffff',
						border:       'none',
						borderRadius: '9999px',
						padding:      '8px 20px',
						fontSize:     '13px',
						fontWeight:   600,
						cursor:       'pointer',
					} }
				>
					{ __( 'Use Template', 'smartpay' ) }
				</button>
				<div style={ {
					width:          '32px',
					height:         '32px',
					borderRadius:   '50%',
					background:     '#ffffff',
					border:         '1px solid #e5e7eb',
					display:        'flex',
					alignItems:     'center',
					justifyContent: 'center',
					color:          '#9ca3af',
				} }>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
						<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
					</svg>
				</div>
			</div>
		</div>
	)
}

/* ─── Template Browser Component (Sidebar design) ────────────────── */
const TemplateBrowser = ( { onBlank, onUse } ) => {
	const [ activeCategory, setCategory ] = useState( 'all' )
	const [ searchQuery, setSearch ]      = useState( '' )
	const [ searchFocus, setSearchFocus ] = useState( false )

	const filtered = useMemo( () => TEMPLATES.filter( ( t ) => {
		const matchCat    = activeCategory === 'all' || t.category === activeCategory
		const q           = searchQuery.trim().toLowerCase()
		const matchSearch = ! q || t.name.toLowerCase().includes( q ) || ( t.description || '' ).toLowerCase().includes( q )
		return matchCat && matchSearch
	} ), [ activeCategory, searchQuery ] )

	const counts = useMemo( () => {
		const c = { all: TEMPLATES.length }
		TEMPLATES.forEach( ( t ) => { c[ t.category ] = ( c[ t.category ] || 0 ) + 1 } )
		return c
	}, [] )

	return (
		<div style={ { display: 'flex', flexDirection: 'row', height: 'min(660px, 75vh)', margin: '0 -24px -24px', borderTop: '1px solid #e5e7eb' } }>
			<style>{`
				.sp-template-search-input {
					border: none !important;
					box-shadow: none !important;
					outline: none !important;
					background: transparent !important;
					padding: 0 !important;
					margin: 0 !important;
					height: auto !important;
					min-height: 0 !important;
				}
				.sp-template-search-input:focus {
					border: none !important;
					box-shadow: none !important;
					outline: none !important;
					background: transparent !important;
				}
				.sp-template-search-clear {
					border: none !important;
					background: none !important;
					box-shadow: none !important;
					outline: none !important;
					padding: 0 !important;
					margin: 0 !important;
					width: auto !important;
					height: auto !important;
					min-height: 0 !important;
					color: #9ca3af !important;
					cursor: pointer !important;
					line-height: 1 !important;
				}
				.sp-template-search-clear:hover {
					color: #4b5563 !important;
				}
			`}</style>

			{/* Left Sidebar (Search + Category Tabs) */}
			<aside style={ {
				width:        '240px',
				flexShrink:   0,
				borderRight:  '1px solid #e5e7eb',
				background:   '#ffffff',
				display:      'flex',
				flexDirection: 'column',
				gap:          '20px',
				padding:      '20px 16px',
			} }>
				{/* Search Box in Sidebar */}
				<div style={ {
					display:      'flex',
					alignItems:   'center',
					gap:          '8px',
					background:   '#f3f4f6',
					border:       `1px solid ${ searchFocus ? 'var(--sp-brand, #293c81)' : 'transparent' }`,
					borderRadius: '8px',
					padding:      '8px 12px',
					boxShadow:    searchFocus ? '0 0 0 2px var(--sp-brand-ring, rgba(41, 60, 129, 0.2))' : 'none',
					transition:   'all 0.15s ease',
				} }>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
						<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
					</svg>
					<input
						type="text"
						placeholder={ __( 'Search templates…', 'smartpay' ) }
						value={ searchQuery }
						onChange={ ( e ) => setSearch( e.target.value ) }
						onFocus={ () => setSearchFocus( true ) }
						onBlur={ () => setSearchFocus( false ) }
						className="sp-template-search-input"
						style={ { flex: 1, border: 'none', outline: 'none', fontSize: '13px', background: 'transparent', color: '#111827' } }
					/>
					{ searchQuery && (
						<button
							onClick={ () => setSearch( '' ) }
							className="sp-template-search-clear"
							style={ { border: 'none', background: 'none', cursor: 'pointer', color: '#9ca3af', fontSize: '16px', padding: 0 } }
						>×</button>
					) }
				</div>

				{/* Categories Header */}
				<div>
					<p style={ { fontSize: '11px', fontWeight: 700, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '0.07em', margin: '0 0 10px 4px' } }>
						{ __( 'Categories', 'smartpay' ) }
					</p>

					{/* Vertical Tabs List */}
					<div style={ { display: 'flex', flexDirection: 'column', gap: '4px', overflowY: 'auto' } }>
						{ CATEGORIES.map( ( cat ) => {
							const active = activeCategory === cat.slug
							return (
								<button
									key={ cat.slug }
									onClick={ () => setCategory( cat.slug ) }
									style={ {
										display:        'flex',
										alignItems:     'center',
										justifyContent: 'space-between',
										width:          '100%',
										padding:        '10px 14px',
										border:         'none',
										borderRadius:   '8px',
										background:     active ? 'var(--sp-brand, #293c81)' : 'transparent',
										color:          active ? '#ffffff' : '#374151',
										fontWeight:     active ? 600 : 500,
										fontSize:       '13px',
										cursor:         'pointer',
										textAlign:      'left',
										transition:     'all 0.15s ease',
									} }
									onMouseEnter={ (e) => { if (!active) e.currentTarget.style.background = 'var(--sp-brand-light, #eef0f9)' } }
									onMouseLeave={ (e) => { if (!active) e.currentTarget.style.background = 'transparent' } }
								>
									<span>{ cat.label }</span>
									<span style={ {
										fontSize:     '11px',
										background:   active ? 'rgba(255, 255, 255, 0.2)' : 'var(--sp-brand-light, #eef0f9)',
										color:        active ? '#ffffff' : 'var(--sp-brand, #293c81)',
										borderRadius: '9999px',
										padding:      '2px 8px',
										fontWeight:   600,
									} }>{ counts[ cat.slug ] || 0 }</span>
								</button>
							)
						} ) }
					</div>
				</div>
			</aside>

			{/* Right Hand Templates Grid */}
			<div style={ { flex: 1, overflowY: 'auto', padding: '24px', background: '#f9fafb' } }>
				<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '24px' } }>
					{/* Render Create Blank option as first card if not searching or in checkout category */}
					{ (!searchQuery || activeCategory === 'all') && (
						<BlankTemplateCard onUse={ onBlank } />
					) }

					{/* Standard templates */}
					{ filtered.map( ( t ) => (
						<TemplateCard key={ t.id } template={ t } onUse={ onUse } />
					) ) }
				</div>
			</div>
		</div>
	)
}

/* ─── NewFormModal ───────────────────────────────────────────────── */
export const NewFormModal = ( { open, onClose, onBlank } ) => {
	const handleOpenChange = ( isOpen ) => {
		if ( ! isOpen ) {
			onClose()
		}
	}

	const handleUseTemplate = ( template ) => {
		const base = ( smartpay.adminUrl || '' ).replace( /admin\.php.*$/, '' )
		window.location.href = `${ base }post-new.php?post_type=smartpay_form&sp_template=${ template.id }`
	}

	return (
		<Dialog open={ open } onOpenChange={ handleOpenChange }>
			<DialogContent className="sm:max-w-7xl" style={ { maxWidth: '80%', width: '100%', zIndex: 99999 } }>
				<DialogHeader style={ { padding: '16px 24px 10px', margin: 0 } }>
					<DialogTitle style={ { margin: 0, fontSize: '18px', fontWeight: 700, lineHeight: 1.2 } }>
						{ __( 'Choose & Organise Templates', 'smartpay' ) }
					</DialogTitle>
					<DialogDescription style={ { margin: '2px 0 0', fontSize: '13px', color: '#6b7280', lineHeight: 1.3 } }>
						{ __( 'Select the perfect response from our broad template spectrum', 'smartpay' ) }
					</DialogDescription>
				</DialogHeader>

				<TemplateBrowser onBlank={ onBlank } onUse={ handleUseTemplate } />
			</DialogContent>
		</Dialog>
	)
}
