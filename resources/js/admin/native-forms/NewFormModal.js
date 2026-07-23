import { CATEGORIES, TEMPLATES } from './templates'

const { __ }                = wp.i18n
const { useState, useMemo } = wp.element
const { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } = window.WPSmartPayUI

/* ─── Design tokens (mirrors .agent/refs/shadcn-ui-example.md) ────── */
const T = {
	bg:         '#ffffff',
	surface:    '#f9fafb',
	border:     '#e5e7eb',
	text:       '#111827',
	textBody:   '#374151',
	textMuted:  '#6b7280',
	placeholder:'#9ca3af',
	accent:     '#6366f1',
	accentDark: '#4f46e5',
	accentSoft: '#eef2ff',
	accentRing: '0 0 0 3px rgba(99,102,241,0.15)',
}

/* Field-type → wireframe bar width, so each card previews its rough shape. */
const FIELD_BAR = {
	name:     '70%',
	email:    '85%',
	text:     '60%',
	textarea: '90%',
	select:   '50%',
	radio:    '45%',
	checkbox: '55%',
	address:  '80%',
}

/* ─── Mini wireframe preview of a template's fields ──────────────── */
const FieldPreview = ( { fields } ) => {
	const bars = fields.filter( ( f ) => f !== 'submit' ).slice( 0, 4 )
	return (
		<div style={ {
			background:     T.surface,
			borderBottom:   `1px solid ${ T.border }`,
			padding:        '14px 14px 12px',
			display:        'flex',
			flexDirection:  'column',
			gap:            '7px',
			minHeight:      '78px',
		} }>
			{ bars.map( ( f, i ) => (
				<div key={ i } style={ { display: 'flex', flexDirection: 'column', gap: '4px' } }>
					<span style={ { height: '4px', width: '34%', background: '#d1d5db', borderRadius: '3px' } } />
					<span style={ { height: '9px', width: FIELD_BAR[ f ] || '65%', background: '#e5e7eb', borderRadius: '4px' } } />
				</div>
			) ) }
		</div>
	)
}

/* ─── Template card ──────────────────────────────────────────────── */
const TemplateCard = ( { template, onUse } ) => {
	const [ hov, setHov ] = useState( false )
	const fieldCount      = template.fields.filter( ( f ) => f !== 'submit' ).length

	return (
		<div
			onMouseEnter={ () => setHov( true ) }
			onMouseLeave={ () => setHov( false ) }
			onClick={ () => onUse( template ) }
			role="button"
			tabIndex={ 0 }
			onKeyDown={ ( e ) => { if ( e.key === 'Enter' || e.key === ' ' ) onUse( template ) } }
			style={ {
				border:        hov ? `1px solid ${ T.accent }` : `1px solid ${ T.border }`,
				borderRadius:  '10px',
				background:    T.bg,
				overflow:      'hidden',
				display:       'flex',
				flexDirection: 'column',
				cursor:        'pointer',
				boxShadow:     hov ? '0 6px 18px rgba(99,102,241,0.14)' : '0 1px 2px rgba(0,0,0,0.04)',
				transform:     hov ? 'translateY(-2px)' : 'none',
				transition:    'border-color .15s, box-shadow .15s, transform .15s',
			} }
		>
			<FieldPreview fields={ template.fields } />

			<div style={ { padding: '12px 14px', flex: 1, display: 'flex', flexDirection: 'column', gap: '6px' } }>
				<div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between' } }>
					<span style={ {
						fontSize:      '10px',
						fontWeight:    600,
						color:         T.accentDark,
						background:    T.accentSoft,
						borderRadius:  '9999px',
						padding:       '2px 9px',
						textTransform: 'capitalize',
					} }>
						{ template.category }
					</span>
					<span style={ { fontSize: '11px', color: T.placeholder } }>
						{ fieldCount } { __( 'fields', 'smartpay' ) }
					</span>
				</div>

				<p style={ { margin: 0, fontSize: '13px', fontWeight: 700, color: T.text, lineHeight: 1.3 } }>
					{ template.name }
				</p>

				{ template.description && (
					<p style={ {
						margin:          0,
						fontSize:        '11px',
						color:           T.textMuted,
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
				<span style={ {
					display:        'flex',
					alignItems:     'center',
					justifyContent: 'center',
					gap:            '5px',
					width:          '100%',
					background:     hov ? T.accent : T.surface,
					color:          hov ? '#fff' : T.textBody,
					border:         `1px solid ${ hov ? T.accent : T.border }`,
					borderRadius:   '6px',
					padding:        '7px 0',
					fontSize:       '12px',
					fontWeight:     600,
					transition:     'background .15s, color .15s, border-color .15s',
				} }>
					{ __( 'Use Template', 'smartpay' ) }
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
						<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
					</svg>
				</span>
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
				border:        hov ? `2px solid ${ T.accent }` : `2px solid ${ T.border }`,
				borderRadius:  '12px',
				padding:       '32px 24px',
				background:    T.bg,
				cursor:        'pointer',
				textAlign:     'center',
				display:       'flex',
				flexDirection: 'column',
				alignItems:    'center',
				gap:           '14px',
				boxShadow:     hov ? '0 8px 24px rgba(99,102,241,0.15)' : 'none',
				transform:     hov ? 'translateY(-2px)' : 'none',
				transition:    'border-color .15s, box-shadow .15s, transform .15s',
				width:         '100%',
			} }
		>
			<div style={ {
				width:          '56px',
				height:         '56px',
				borderRadius:   '14px',
				background:     hov ? T.accent : T.accentSoft,
				display:        'flex',
				alignItems:     'center',
				justifyContent: 'center',
				transition:     'background .15s',
			} }>
				{ icon( hov ? '#ffffff' : T.accent ) }
			</div>
			<div>
				<p style={ { margin: '0 0 4px', fontSize: '15px', fontWeight: 700, color: T.text } }>
					{ title }
				</p>
				<p style={ { margin: 0, fontSize: '12px', color: T.textMuted, lineHeight: 1.5 } }>
					{ description }
				</p>
			</div>
		</button>
	)
}

/* ─── Picker view ────────────────────────────────────────────────── */
const PickerView = ( { onBlank, onTemplate } ) => (
	<div style={ { padding: '4px 0 8px' } }>
		<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } }>
			<PickerCard
				onClick={ onBlank }
				title={ __( 'Start from Blank', 'smartpay' ) }
				description={ __( 'Build your form field by field', 'smartpay' ) }
				icon={ ( c ) => (
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke={ c } strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
						<rect x="3" y="3" width="18" height="18" rx="2"/>
						<line x1="12" y1="8" x2="12" y2="16"/>
						<line x1="8" y1="12" x2="16" y2="12"/>
					</svg>
				) }
			/>
			<PickerCard
				onClick={ onTemplate }
				title={ __( 'From Template', 'smartpay' ) }
				description={ __( 'Start from a ready-made form', 'smartpay' ) }
				icon={ ( c ) => (
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke={ c } strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
						<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
						<rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
					</svg>
				) }
			/>
		</div>
	</div>
)

/* ─── Template browser ───────────────────────────────────────────── */
const TemplateBrowser = ( { onUse } ) => {
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
		<div style={ { display: 'flex', height: 'min(620px, 68vh)', overflow: 'hidden', margin: '0 -24px -24px', borderTop: `1px solid ${ T.border }` } }>
			{/* Sidebar */}
			<aside style={ {
				width:      '210px',
				flexShrink: 0,
				borderRight:`1px solid ${ T.border }`,
				background: T.surface,
				overflowY:  'auto',
				padding:    '14px 10px',
			} }>
				<p style={ { fontSize: '10px', fontWeight: 700, color: T.placeholder, textTransform: 'uppercase', letterSpacing: '0.07em', padding: '0 8px', margin: '0 0 8px' } }>
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
								padding:        '8px 10px',
								marginBottom:   '2px',
								border:         'none',
								borderRadius:   '6px',
								background:     active ? T.bg : 'transparent',
								color:          active ? T.accentDark : T.textBody,
								fontWeight:     active ? 600 : 500,
								fontSize:       '12.5px',
								cursor:         'pointer',
								textAlign:      'left',
								boxShadow:      active ? '0 1px 2px rgba(0,0,0,0.06)' : 'none',
								transition:     'background .12s, color .12s',
							} }
						>
							<span>{ cat.label }</span>
							<span style={ {
								fontSize:     '10px',
								background:   active ? T.accentSoft : '#eceef1',
								color:        active ? T.accentDark : T.textMuted,
								borderRadius: '9999px',
								padding:      '1px 7px',
								fontWeight:   600,
							} }>{ counts[ cat.slug ] || 0 }</span>
						</button>
					)
				} ) }
			</aside>

			{/* Main */}
			<div style={ { flex: 1, display: 'flex', flexDirection: 'column', minWidth: 0 } }>
				<div style={ { padding: '12px 16px', borderBottom: `1px solid ${ T.border }`, flexShrink: 0 } }>
					<div style={ {
						display:      'flex',
						alignItems:   'center',
						gap:          '8px',
						background:   T.bg,
						border:       `1px solid ${ searchFocus ? T.accent : T.border }`,
						borderRadius: '6px',
						padding:      '7px 12px',
						boxShadow:    searchFocus ? T.accentRing : 'none',
						transition:   'border-color .12s, box-shadow .12s',
					} }>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke={ T.placeholder } strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
							<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
						</svg>
						<input
							type="text"
							placeholder={ __( 'Search templates…', 'smartpay' ) }
							value={ searchQuery }
							onChange={ ( e ) => setSearch( e.target.value ) }
							onFocus={ () => setSearchFocus( true ) }
							onBlur={ () => setSearchFocus( false ) }
							style={ { flex: 1, border: 'none', outline: 'none', fontSize: '13px', background: 'transparent', color: T.text } }
						/>
						{ searchQuery && (
							<button
								onClick={ () => setSearch( '' ) }
								style={ { border: 'none', background: 'none', cursor: 'pointer', color: T.placeholder, lineHeight: 1, padding: 0, fontSize: '16px' } }
							>×</button>
						) }
					</div>
				</div>

				<div style={ { flex: 1, overflowY: 'auto', padding: '16px', background: T.surface } }>
					{ filtered.length === 0 ? (
						<div style={ { textAlign: 'center', padding: '48px 20px', color: T.placeholder } }>
							<p style={ { margin: 0, fontSize: '14px' } }>{ __( 'No templates found', 'smartpay' ) }</p>
							<p style={ { margin: '4px 0 0', fontSize: '12px' } }>{ __( 'Try a different category or search term', 'smartpay' ) }</p>
						</div>
					) : (
						<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(210px, 1fr))', gap: '16px' } }>
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

	const isTemplates = view === 'templates'

	return (
		<Dialog open={ open } onOpenChange={ handleOpenChange }>
			<DialogContent className={ isTemplates ? 'sm:max-w-5xl' : 'sm:max-w-2xl' }>
				<DialogHeader>
					{ isTemplates ? (
						<div style={ { display: 'flex', alignItems: 'center', gap: '12px' } }>
							<button
								onClick={ () => setView( 'picker' ) }
								style={ {
									display:        'inline-flex',
									alignItems:     'center',
									justifyContent: 'center',
									gap:            '5px',
									border:         `1px solid ${ T.border }`,
									borderRadius:   '6px',
									padding:        '6px 12px',
									background:     T.bg,
									cursor:         'pointer',
									fontSize:       '13px',
									fontWeight:     500,
									color:          T.textBody,
									flexShrink:     0,
								} }
							>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
									<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
								</svg>
								{ __( 'Back', 'smartpay' ) }
							</button>
							<div style={ { display: 'flex', flexDirection: 'column', gap: '2px', minWidth: 0 } }>
								<DialogTitle style={ { margin: 0 } }>
									{ __( 'Choose a Template', 'smartpay' ) }
								</DialogTitle>
								<DialogDescription style={ { margin: 0 } }>
									{ __( 'Pick a ready-made form to start from.', 'smartpay' ) }
								</DialogDescription>
							</div>
						</div>
					) : (
						<>
							<DialogTitle>{ __( 'Create a New Form', 'smartpay' ) }</DialogTitle>
							<DialogDescription>
								{ __( 'How would you like to start?', 'smartpay' ) }
							</DialogDescription>
						</>
					) }
				</DialogHeader>

				{ isTemplates ? (
					<TemplateBrowser onUse={ handleUseTemplate } />
				) : (
					<PickerView
						onBlank={ onBlank }
						onTemplate={ () => setView( 'templates' ) }
					/>
				) }
			</DialogContent>
		</Dialog>
	)
}
