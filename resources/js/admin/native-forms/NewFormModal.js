import { CATEGORIES, TEMPLATES } from './templates'

const { __ }                = wp.i18n
const { useState, useMemo } = wp.element
const { Dialog, DialogContent, DialogHeader, DialogTitle } = window.WPSmartPayUI

/* ─── Template card ──────────────────────────────────────────────── */
const TemplateCard = ( { template, onUse } ) => {
	const [ hov, setHov ] = useState( false )
	const fieldCount      = template.fields.filter( ( f ) => f !== 'submit' ).length

	return (
		<div
			onMouseEnter={ () => setHov( true ) }
			onMouseLeave={ () => setHov( false ) }
			style={ {
				border:        hov ? '1px solid #9ca3af' : '1px solid #e5e7eb',
				borderRadius:  '8px',
				background:    '#fff',
				overflow:      'hidden',
				display:       'flex',
				flexDirection: 'column',
				boxShadow:     hov ? '0 2px 8px rgba(0,0,0,0.08)' : 'none',
				transition:    'border-color 0.15s, box-shadow 0.15s',
			} }
		>
			<div style={ { padding: '12px 14px', flex: 1, display: 'flex', flexDirection: 'column', gap: '5px' } }>
				<div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between' } }>
					<span style={ {
						fontSize:      '10px',
						fontWeight:    600,
						color:         '#6b7280',
						background:    '#f3f4f6',
						borderRadius:  '20px',
						padding:       '2px 8px',
						textTransform: 'capitalize',
					} }>
						{ template.category }
					</span>
					<span style={ { fontSize: '11px', color: '#9ca3af' } }>
						{ fieldCount } { __( 'fields', 'smartpay' ) }
					</span>
				</div>

				<p style={ { margin: 0, fontSize: '13px', fontWeight: 700, color: '#111827', lineHeight: 1.3 } }>
					{ template.name }
				</p>

				{ template.description && (
					<p style={ {
						margin:          0,
						fontSize:        '11px',
						color:           '#6b7280',
						lineHeight:      1.5,
						display:         '-webkit-box',
						WebkitLineClamp: 2,
						WebkitBoxOrient: 'vertical',
						overflow:        'hidden',
					} }>
						{ template.description }
					</p>
				) }
			</div>

			<div style={ { padding: '0 14px 12px' } }>
				<button
					onClick={ () => onUse( template ) }
					style={ {
						width:        '100%',
						background:   hov ? '#111827' : '#f9fafb',
						color:        hov ? '#fff' : '#374151',
						border:       '1px solid #e5e7eb',
						borderRadius: '6px',
						padding:      '7px 0',
						fontSize:     '12px',
						fontWeight:   600,
						cursor:       'pointer',
						transition:   'background 0.15s, color 0.15s',
					} }
				>
					{ __( 'Use Template', 'smartpay' ) }
				</button>
			</div>
		</div>
	)
}

/* ─── Picker option card ─────────────────────────────────────────── */
const PickerCard = ( { icon, title, description, onClick } ) => {
	const [ hov, setHov ] = useState( false )
	return (
		<button
			onClick={ onClick }
			onMouseEnter={ () => setHov( true ) }
			onMouseLeave={ () => setHov( false ) }
			style={ {
				border:        hov ? '2px solid #293c81' : '2px solid #e5e7eb',
				borderRadius:  '12px',
				padding:       '32px 24px',
				background:    '#fff',
				cursor:        'pointer',
				textAlign:     'center',
				display:       'flex',
				flexDirection: 'column',
				alignItems:    'center',
				gap:           '12px',
				boxShadow:     hov ? '0 4px 16px rgba(41,60,129,0.12)' : 'none',
				transition:    'border-color 0.15s, box-shadow 0.15s',
				width:         '100%',
			} }
		>
			<div style={ {
				width:          '52px',
				height:         '52px',
				borderRadius:   '12px',
				background:     hov ? '#eef0f9' : '#f1f5f9',
				display:        'flex',
				alignItems:     'center',
				justifyContent: 'center',
				transition:     'background 0.15s',
			} }>
				{ icon }
			</div>
			<div>
				<p style={ { margin: '0 0 4px', fontSize: '15px', fontWeight: 700, color: '#111827' } }>
					{ title }
				</p>
				<p style={ { margin: 0, fontSize: '12px', color: '#9ca3af', lineHeight: 1.5 } }>
					{ description }
				</p>
			</div>
		</button>
	)
}

/* ─── Picker view ────────────────────────────────────────────────── */
const PickerView = ( { onBlank, onTemplate } ) => (
	<div style={ { padding: '8px 0 8px' } }>
		<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', maxWidth: '440px', margin: '0 auto' } }>
			<PickerCard
				onClick={ onBlank }
				title={ __( 'Start from Blank', 'smartpay' ) }
				description={ __( 'Build your form from scratch', 'smartpay' ) }
				icon={
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#293c81" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
						<rect x="3" y="3" width="18" height="18" rx="2"/>
						<line x1="12" y1="8" x2="12" y2="16"/>
						<line x1="8" y1="12" x2="16" y2="12"/>
					</svg>
				}
			/>
			<PickerCard
				onClick={ onTemplate }
				title={ __( 'From Template', 'smartpay' ) }
				description={ __( 'Start with a pre-built template', 'smartpay' ) }
				icon={
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#293c81" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
						<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
						<rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
					</svg>
				}
			/>
		</div>
	</div>
)

/* ─── Template browser ───────────────────────────────────────────── */
const TemplateBrowser = ( { onUse } ) => {
	const [ activeCategory, setCategory ] = useState( 'all' )
	const [ searchQuery, setSearch ]      = useState( '' )

	const filtered = useMemo( () => TEMPLATES.filter( ( t ) => {
		const matchCat    = activeCategory === 'all' || t.category === activeCategory
		const q           = searchQuery.trim().toLowerCase()
		const matchSearch = ! q || t.name.toLowerCase().includes( q )
		return matchCat && matchSearch
	} ), [ activeCategory, searchQuery ] )

	const counts = useMemo( () => {
		const c = { all: TEMPLATES.length }
		TEMPLATES.forEach( ( t ) => { c[ t.category ] = ( c[ t.category ] || 0 ) + 1 } )
		return c
	}, [] )

	return (
		<div style={ { display: 'flex', height: '520px', overflow: 'hidden', margin: '0 -24px -24px' } }>
			{/* Sidebar */}
			<aside style={ {
				width:      '180px',
				flexShrink: 0,
				borderRight:'1px solid #e5e7eb',
				overflowY:  'auto',
				paddingTop: '12px',
			} }>
				<p style={ { fontSize: '10px', fontWeight: 700, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '0.07em', padding: '0 12px', margin: '0 0 6px' } }>
					{ __( 'Category', 'smartpay' ) }
				</p>
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
								padding:        '7px 12px',
								border:         'none',
								background:     active ? '#eef0f9' : 'transparent',
								color:          active ? '#293c81' : '#374151',
								fontWeight:     active ? 600 : 400,
								fontSize:       '12px',
								cursor:         'pointer',
								textAlign:      'left',
								borderLeft:     active ? '3px solid #293c81' : '3px solid transparent',
								transition:     'background 0.12s',
							} }
						>
							<span>{ cat.label }</span>
							<span style={ {
								fontSize:     '10px',
								background:   active ? 'rgba(41,60,129,0.15)' : '#f3f4f6',
								color:        active ? '#293c81' : '#6b7280',
								borderRadius: '20px',
								padding:      '1px 6px',
								fontWeight:   600,
							} }>{ counts[ cat.slug ] || 0 }</span>
						</button>
					)
				} ) }
			</aside>

			{/* Main */}
			<div style={ { flex: 1, display: 'flex', flexDirection: 'column', minWidth: 0 } }>
				<div style={ { padding: '12px 16px', borderBottom: '1px solid #e5e7eb', flexShrink: 0 } }>
					<div style={ { display: 'flex', alignItems: 'center', gap: '8px', background: '#f9fafb', border: '1px solid #e5e7eb', borderRadius: '6px', padding: '6px 12px' } }>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
							<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
						</svg>
						<input
							type="text"
							placeholder={ __( 'Search templates…', 'smartpay' ) }
							value={ searchQuery }
							onChange={ ( e ) => setSearch( e.target.value ) }
							style={ { flex: 1, border: 'none', outline: 'none', fontSize: '13px', background: 'transparent', color: '#111827' } }
						/>
						{ searchQuery && (
							<button
								onClick={ () => setSearch( '' ) }
								style={ { border: 'none', background: 'none', cursor: 'pointer', color: '#9ca3af', lineHeight: 1, padding: 0, fontSize: '16px' } }
							>×</button>
						) }
					</div>
				</div>

				<div style={ { flex: 1, overflowY: 'auto', padding: '16px' } }>
					{ filtered.length === 0 ? (
						<div style={ { textAlign: 'center', padding: '48px 20px', color: '#9ca3af' } }>
							<p style={ { margin: 0, fontSize: '14px' } }>{ __( 'No templates found', 'smartpay' ) }</p>
							<p style={ { margin: '4px 0 0', fontSize: '12px' } }>{ __( 'Try a different category or search term', 'smartpay' ) }</p>
						</div>
					) : (
						<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(175px, 1fr))', gap: '12px' } }>
							{ filtered.map( ( t ) => (
								<TemplateCard key={ t.id } template={ t } onUse={ onUse } />
							) ) }
						</div>
					) }
				</div>
			</div>
		</div>
	)
}

/* ─── NewFormModal ───────────────────────────────────────────────── */
/**
 * @param {boolean}  open     — controlled open state
 * @param {Function} onClose  — called when modal closes
 * @param {Function} onBlank  — called when user picks "Start from Blank"
 */
export const NewFormModal = ( { open, onClose, onBlank } ) => {
	const [ view, setView ] = useState( 'picker' )

	const handleOpenChange = ( isOpen ) => {
		if ( ! isOpen ) {
			onClose()
			setTimeout( () => setView( 'picker' ), 200 )
		}
	}

	const handleUseTemplate = ( template ) => {
		const base = ( smartpay.adminUrl || '' ).replace( /admin\.php.*$/, '' )
		window.location.href = `${ base }post-new.php?post_type=smartpay_form&sp_template=${ template.id }`
	}

	return (
		<Dialog open={ open } onOpenChange={ handleOpenChange }>
			<DialogContent className="sm:max-w-3xl">
				<DialogHeader>
					{ view === 'templates' ? (
						<div style={ { display: 'flex', alignItems: 'center', gap: '10px' } }>
							<button
								onClick={ () => setView( 'picker' ) }
								style={ {
									display:      'flex',
									alignItems:   'center',
									border:       '1px solid #e5e7eb',
									borderRadius: '6px',
									padding:      '4px 10px',
									background:   '#fff',
									cursor:       'pointer',
									fontSize:     '13px',
									color:        '#374151',
									gap:          '4px',
									flexShrink:   0,
								} }
							>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
									<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
								</svg>
								{ __( 'Back', 'smartpay' ) }
							</button>
							<DialogTitle style={ { margin: 0 } }>
								{ __( 'Choose a Template', 'smartpay' ) }
							</DialogTitle>
						</div>
					) : (
						<DialogTitle>
							{ __( 'New Form', 'smartpay' ) }
							<br/>
							<small>{ __( 'How would you like to start?', 'smartpay' ) }</small>
						</DialogTitle>
					) }
				</DialogHeader>

				{ view === 'picker' ? (
					<PickerView
						onBlank={ onBlank }
						onTemplate={ () => setView( 'templates' ) }
					/>
				) : (
					<TemplateBrowser onUse={ handleUseTemplate } />
				) }
			</DialogContent>
		</Dialog>
	)
}
