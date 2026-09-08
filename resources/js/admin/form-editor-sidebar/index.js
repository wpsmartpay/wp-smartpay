import { __ } from '@wordpress/i18n';
import { useRef, useEffect, useState, createPortal } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { Button, Modal } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import {
	__experimentalMainDashboardButton as MainDashboardButton,
} from '@wordpress/edit-post';
import AmountCard from '../form-editor/components/sidebar/AmountCard';

const GearIcon = () => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="20"
		height="20"
		fill="none"
		stroke="currentColor"
		strokeWidth="2"
		strokeLinecap="round"
		strokeLinejoin="round"
		aria-hidden="true"
		focusable="false"
	>
		<circle cx="12" cy="12" r="3" />
		<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
	</svg>
);

/**
 * Pricing panel backed by `_smartpay_amounts` post meta.
 */
const PricingPanel = () => {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'smartpay_form', 'meta' );

	const didInitAmounts = useRef( false );
	useEffect( () => {
		if ( didInitAmounts.current || meta === undefined ) return;
		didInitAmounts.current = true;
		const raw = meta._smartpay_amounts;
		if ( ! raw || raw === '[]' ) {
			setMeta( {
				...meta,
				_smartpay_amounts: JSON.stringify( [
					{ key: 'default', label: '', amount: '0.00', billing_type: 'One Time' },
				] ),
			} );
		}
	}, [ meta ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const rawAmounts = meta?._smartpay_amounts || '[]';
	const amounts = ( () => {
		try {
			const parsed = JSON.parse( rawAmounts );
			return Array.isArray( parsed ) && parsed.length > 0
				? parsed
				: [ { key: 'default', label: '', amount: '0.00', billing_type: 'One Time' } ];
		} catch {
			return [ { key: 'default', label: '', amount: '0.00', billing_type: 'One Time' } ];
		}
	} )();

	const setAmounts = ( newAmounts ) => {
		setMeta( { ...meta, _smartpay_amounts: JSON.stringify( newAmounts ) } );
	};

	const addAmount = () => {
		setAmounts( [
			...amounts,
			{
				key: Math.random().toString( 36 ).substr( 2, 9 ),
				label: '',
				amount: '0.00',
				billing_type: 'One Time',
			},
		] );
	};

	const removeAmount = ( key ) => {
		if ( amounts.length <= 1 ) return;
		setAmounts( amounts.filter( ( a ) => a.key !== key ) );
	};

	const updateAmount = ( key, data ) => {
		setAmounts( amounts.map( ( a ) => ( a.key === key ? { ...a, ...data } : a ) ) );
	};

	return (
		<div className="sp-pricing-panel">
			{ amounts.map( ( amount ) => (
				<AmountCard
					key={ amount.key }
					amount={ amount }
					onRemove={ () => removeAmount( amount.key ) }
					onUpdate={ ( data ) => updateAmount( amount.key, data ) }
					canRemove={ amounts.length > 1 }
				/>
			) ) }
			<Button variant="secondary" __next40pxDefaultSize onClick={ addAmount }>
				{ __( 'Add Amount', 'smartpay' ) }
			</Button>
		</div>
	);
};

// ── SmartPay block manifest ───────────────────────────────────────────────────

const SP_BLOCKS = [
	{ name: 'smartpay-form/name',           label: __( 'Name Field',  'smartpay' ), required: true  },
	{ name: 'smartpay-form/email',          label: __( 'Email',       'smartpay' ), required: true  },
	{ name: 'smartpay-form/text-input',     label: __( 'Text Input',  'smartpay' ), required: false },
	{ name: 'smartpay-form/textarea-input', label: __( 'Text Area',   'smartpay' ), required: false },
	{ name: 'smartpay-form/radio-input',    label: __( 'Radio',       'smartpay' ), required: false },
	{ name: 'smartpay-form/checkbox-input', label: __( 'Checkbox',    'smartpay' ), required: false },
	{ name: 'smartpay-form/select-input',   label: __( 'Select',      'smartpay' ), required: false },
	{ name: 'smartpay-form/address-input',  label: __( 'Address',       'smartpay' ), required: false },
	{ name: 'smartpay-form/goal-progress', label: __( 'Goal Progress', 'smartpay' ), required: false },
];

// Blocks that may only appear once in the form.
const UNIQUE_BLOCKS = new Set( [ 'smartpay-form/name', 'smartpay-form/email', 'smartpay-form/goal-progress' ] );

const SUBMIT_BLOCK = 'smartpay-form/submit-button';

/**
 * Header portals — "Settings" modal button, "Guide" field picker, and a
 * top-left "Add field" button. All three portal their DOM nodes so they
 * survive Gutenberg re-renders without relying on SlotFill internals.
 */
const FormGuide = () => {
	const blocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks(),
		[]
	);
	const usedNames = new Set( blocks.map( ( b ) => b.name ) );

	const { insertBlocks } = useDispatch( 'core/block-editor' );

	// ── right-header portal (Settings + Guide buttons) ────────────────────
	const [ btnTick, setBtnTick ]           = useState( 0 );
	const btnRef                            = useRef( null );

	// ── top-left portal (Add Field button) ────────────────────────────────
	const [ leftTick, setLeftTick ]         = useState( 0 );
	const leftRef                           = useRef( null );
	const [ fieldPickerOpen, setFieldPickerOpen ] = useState( false );

	// ── Settings full-page modal ───────────────────────────────────────────
	const [ settingsOpen, setSettingsOpen ]       = useState( false );
	const [ settingsTab, setSettingsTab ]          = useState( 'settings' );

	// ── Guide modal (field list) ───────────────────────────────────────────
	const [ guideOpen, setGuideOpen ]             = useState( false );

	// Portal the right-header buttons into .editor-header__settings
	useEffect( () => {
		let mounted  = true;
		let rafId    = null;
		let mutTimer = null;

		const getHeader = () =>
			document.querySelector( '.editor-header__settings' ) ||
			document.querySelector( '.edit-post-header__settings' );

		const setupBtn = () => {
			const header = getHeader();
			if ( ! header ) return false;
			if ( ! btnRef.current || ! btnRef.current.isConnected ) {
				btnRef.current?.remove();
				const el = document.createElement( 'div' );
				el.className = 'sp-guide-btn-portal';
				header.insertBefore( el, header.firstChild );
				btnRef.current = el;
			}
			if ( mounted ) setBtnTick( ( n ) => n + 1 );
			return true;
		};

		const trySetup = () => { if ( ! mounted ) return; if ( ! setupBtn() ) rafId = requestAnimationFrame( trySetup ); };
		rafId = requestAnimationFrame( trySetup );

		const observer = new MutationObserver( () => {
			clearTimeout( mutTimer );
			mutTimer = setTimeout( () => { if ( mounted && ( ! btnRef.current || ! btnRef.current.isConnected ) ) setupBtn(); }, 200 );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );

		return () => {
			mounted = false; cancelAnimationFrame( rafId ); clearTimeout( mutTimer );
			observer.disconnect(); btnRef.current?.remove(); btnRef.current = null;
		};
	}, [] );

	// Portal the top-left "Add field" button into .editor-header__toolbar
	useEffect( () => {
		let mounted  = true;
		let rafId    = null;
		let mutTimer = null;

		const getLeft = () =>
			document.querySelector( '.editor-header__toolbar' ) ||
			document.querySelector( '.editor-header__left' ) ||
			document.querySelector( '.edit-post-header__toolbar' );

		const setupLeft = () => {
			const left = getLeft();
			if ( ! left ) return false;
			if ( ! leftRef.current || ! leftRef.current.isConnected ) {
				leftRef.current?.remove();
				const el = document.createElement( 'div' );
				el.className = 'sp-add-field-portal';
				el.style.cssText = 'display:inline-flex;align-items:center;';
				left.appendChild( el );
				leftRef.current = el;
			}
			if ( mounted ) setLeftTick( ( n ) => n + 1 );
			return true;
		};

		const tryLeft = () => { if ( ! mounted ) return; if ( ! setupLeft() ) rafId = requestAnimationFrame( tryLeft ); };
		rafId = requestAnimationFrame( tryLeft );

		const observer = new MutationObserver( () => {
			clearTimeout( mutTimer );
			mutTimer = setTimeout( () => { if ( mounted && ( ! leftRef.current || ! leftRef.current.isConnected ) ) setupLeft(); }, 200 );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );

		return () => {
			mounted = false; cancelAnimationFrame( rafId ); clearTimeout( mutTimer );
			observer.disconnect(); leftRef.current?.remove(); leftRef.current = null;
		};
	}, [] );

	// Close field picker when clicking outside
	useEffect( () => {
		if ( ! fieldPickerOpen ) return;
		const close = ( e ) => {
			if ( ! e.target.closest( '.sp-add-field-portal' ) ) setFieldPickerOpen( false );
		};
		document.addEventListener( 'mousedown', close );
		return () => document.removeEventListener( 'mousedown', close );
	}, [ fieldPickerOpen ] );

	const addField = ( name ) => insertBlocks( wp.blocks.createBlock( name ) );

	// Insert block before submit-button block if it exists, otherwise at end.
	const addFieldBeforeSubmit = ( name ) => {
		const allBlocks = wp.data.select( 'core/block-editor' ).getBlocks();
		const submitIdx = allBlocks.findIndex( ( b ) => b.name === SUBMIT_BLOCK );
		insertBlocks( wp.blocks.createBlock( name ), submitIdx >= 0 ? submitIdx : undefined );
		setFieldPickerOpen( false );
	};

	const addRequired = () => {
		const toAdd = [ 'smartpay-form/name', 'smartpay-form/email' ]
			.filter( ( name ) => ! usedNames.has( name ) )
			.map( ( name ) => wp.blocks.createBlock( name ) );
		if ( toAdd.length ) insertBlocks( toAdd );
	};

	const allRequiredAdded =
		usedNames.has( 'smartpay-form/name' ) && usedNames.has( 'smartpay-form/email' );

	const FieldButton = ( { name, label } ) => {
		const isUsed = UNIQUE_BLOCKS.has( name ) && usedNames.has( name );
		return (
			<Button
				variant="secondary"
				__next40pxDefaultSize
				className={ `sp-guide-modal__field${ isUsed ? ' is-used' : '' }` }
				style={ { margin: '0px 10px 10px 0' } }
				disabled={ isUsed }
				onClick={ () => ! isUsed && addField( name ) }
			>
				<span className="sp-guide-modal__field-icon" aria-hidden="true">{ isUsed ? '✓' : '+' }</span>
				{ label }
			</Button>
		);
	};

	// ── Wand SVG for the Add Field button ─────────────────────────────────
	const WandIcon = (
		<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
			<path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2 18.36l3.64 3.64L21.64 5.36a1.21 1.21 0 0 0 0-1.72z"/>
			<path d="m14 7 3 3"/>
			<path d="M5 6v4"/><path d="M19 14v4"/>
			<path d="M10 2v2"/><path d="M7 8H3"/>
			<path d="M21 16h-4"/><path d="M11 3H9"/>
		</svg>
	);

	// ── Settings tabs — extensible via wp.hooks filter ────────────────────
	const baseTabs = [
		{ id: 'settings', label: __( 'Form Settings', 'smartpay' ) },
		{ id: 'goal',     label: __( 'Goal',          'smartpay' ) },
	];
	const SETTINGS_TABS = window.wp?.hooks?.applyFilters?.(
		'smartpay_form_settings_tabs',
		baseTabs
	) ?? baseTabs;

	return (
		<>
			{ /* Right-header: "Settings" (opens modal) + "Guide" (opens field guide) */ }
			{ btnTick > 0 && btnRef.current && createPortal(
				<>
					<Button variant="tertiary" className="sp-guide-trigger" onClick={ () => { setSettingsOpen( true ); setSettingsTab( 'settings' ); } }>
						{ __( 'Settings', 'smartpay' ) }
					</Button>
					<Button variant="tertiary" className="sp-guide-trigger" onClick={ () => setGuideOpen( true ) }>
						{ __( 'Guide', 'smartpay' ) }
					</Button>
				</>,
				btnRef.current
			) }

			{ /* Top-left: "Add Field" button with SmartPay block picker dropdown */ }
			{ leftTick > 0 && leftRef.current && createPortal(
				<div className="sp-add-field-wrap" style={ { position: 'relative', display: 'inline-flex', alignItems: 'center' } }>
					<button
						type="button"
						className="sp-add-field-btn"
						onClick={ () => setFieldPickerOpen( ( o ) => ! o ) }
						aria-label={ __( 'Add field', 'smartpay' ) }
						title={ __( 'Add field', 'smartpay' ) }
					>
						{ WandIcon }
						<span className="sp-add-field-btn__label">{ __( '+ Field', 'smartpay' ) }</span>
					</button>
					{ fieldPickerOpen && (
						<div className="sp-field-picker" role="menu">
							{ SP_BLOCKS.map( ( { name, label, required } ) => {
								const isUsed = UNIQUE_BLOCKS.has( name ) && usedNames.has( name );
								return (
									<button
										key={ name }
										type="button"
										className={ `sp-field-picker__item${ isUsed ? ' is-used' : '' }${ required ? ' is-required' : '' }` }
										disabled={ isUsed }
										role="menuitem"
										onClick={ () => ! isUsed && addFieldBeforeSubmit( name ) }
									>
										<span className="sp-field-picker__check" aria-hidden="true">{ isUsed ? '✓' : '+' }</span>
										{ label }
										{ required && <span className="sp-field-picker__req" title={ __( 'Required', 'smartpay' ) }>✦</span> }
									</button>
								);
							} ) }
						</div>
					) }
				</div>,
				leftRef.current
			) }

			{ /* Full-page Settings modal with sidebar tabs */ }
			{ settingsOpen && (
				<Modal
					title={ __( 'Form Settings', 'smartpay' ) }
					onRequestClose={ () => setSettingsOpen( false ) }
					className="sp-form-settings-modal"
					size="large"
				>
					<div className="sp-form-settings-modal__layout">
						<nav className="sp-form-settings-modal__nav" aria-label={ __( 'Settings sections', 'smartpay' ) }>
							{ SETTINGS_TABS.map( ( tab ) => (
								<button
									key={ tab.id }
									type="button"
									className={ `sp-form-settings-modal__nav-item${ settingsTab === tab.id ? ' is-active' : '' }` }
									onClick={ () => setSettingsTab( tab.id ) }
								>
									{ tab.label }
								</button>
							) ) }
						</nav>
						<div className="sp-form-settings-modal__content">
							{ settingsTab === 'settings' && <OptionsPanel /> }
							{ settingsTab === 'goal'     && <GoalPanel /> }
							{ settingsTab !== 'settings' && settingsTab !== 'goal' && ( () => {
								const ExtPanel = window.wp?.hooks?.applyFilters?.(
									'smartpay_form_settings_panel', null, settingsTab
								);
								return ExtPanel ? <ExtPanel /> : null;
							} )() }
						</div>
					</div>
				</Modal>
			) }

			{ /* Guide modal — block field list */ }
			{ guideOpen && (
				<Modal
					title={ __( 'WPSmartPay Help Guide', 'smartpay' ) }
					onRequestClose={ () => setGuideOpen( false ) }
					className="sp-guide-modal"
				>
					<p className="sp-guide-modal__desc">
						{ __( 'Build your payment form — click a field below to add it.', 'smartpay' ) }
					</p>
					<h3 className="sp-guide-modal__heading">{ __( 'Required fields', 'smartpay' ) }</h3>
					<div className="sp-guide-modal__grid">
						{ SP_BLOCKS.filter( ( b ) => b.required ).map( ( b ) => (
							<FieldButton key={ b.name } name={ b.name } label={ b.label } />
						) ) }
					</div>
					<h3 className="sp-guide-modal__heading">{ __( 'Add more fields', 'smartpay' ) }</h3>
					<div className="sp-guide-modal__grid">
						{ SP_BLOCKS.filter( ( b ) => ! b.required ).map( ( b ) => (
							<FieldButton key={ b.name } name={ b.name } label={ b.label } />
						) ) }
					</div>
					<div className="sp-guide-modal__footer">
						<Button variant="primary" __next40pxDefaultSize disabled={ allRequiredAdded } onClick={ addRequired }>
							{ allRequiredAdded ? __( 'Required fields added ✓', 'smartpay' ) : __( 'Add required fields', 'smartpay' ) }
						</Button>
						<Button variant="tertiary" __next40pxDefaultSize onClick={ () => setGuideOpen( false ) }>
							{ __( 'Close', 'smartpay' ) }
						</Button>
					</div>
				</Modal>
			) }
		</>
	);
};

/**
 * Goal panel backed by `_smartpay_settings.goal` post meta.
 */
const GoalPanel = () => {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'smartpay_form', 'meta' );
	const { ToggleControl, SelectControl, TextControl } = wp.components;

	const rawSettings = meta?._smartpay_settings || '{}';
	const settings = ( () => {
		try {
			const parsed = JSON.parse( rawSettings );
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch {
			return {};
		}
	} )();

	const rawGoal = settings.goal || '{}';
	const goal = ( () => {
		try {
			const parsed = typeof rawGoal === 'string' ? JSON.parse( rawGoal ) : rawGoal;
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch {
			return {};
		}
	} )();

	const updateGoal = ( data ) => {
		const nextGoal     = { ...goal, ...data };
		const nextSettings = { ...settings, goal: nextGoal };
		setMeta( { ...meta, _smartpay_settings: JSON.stringify( nextSettings ) } );
	};

	return (
		<div className="sp-goal-panel">
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Enable Goal', 'smartpay' ) }
				checked={ !! goal.enabled }
				onChange={ ( val ) => updateGoal( { enabled: val } ) }
			/>

			{ goal.enabled && (
				<>
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Goal Type', 'smartpay' ) }
						value={ goal.type || 'quantity' }
						options={ [
							{ value: 'quantity', label: __( 'Quantity — track number of sales', 'smartpay' ) },
							{ value: 'amount',   label: __( 'Amount — track total revenue', 'smartpay' ) },
						] }
						onChange={ ( val ) => updateGoal( { type: val } ) }
					/>

					<div className="sp-sidebar-field">
						<span className="sp-label">{ __( 'Target', 'smartpay' ) }</span>
						<TextControl
							__nextHasNoMarginBottom
							className="sp-input"
							type="number"
							value={ goal.target ?? 100 }
							onChange={ ( val ) => updateGoal( { target: parseFloat( val ) || 0 } ) }
							min={ 0 }
							step="any"
						/>
					</div>

					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show progress bar on frontend', 'smartpay' ) }
						checked={ goal.showToPublic !== false }
						onChange={ ( val ) => updateGoal( { showToPublic: val } ) }
					/>

					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'When goal is met', 'smartpay' ) }
						value={ goal.behaviorWhenGoalMet || 'allow_orders' }
						options={ [
							{ value: 'allow_orders', label: __( 'Continue accepting orders', 'smartpay' ) },
							{ value: 'stop_orders',  label: __( 'Stop accepting new orders', 'smartpay' ) },
						] }
						onChange={ ( val ) => updateGoal( { behaviorWhenGoalMet: val } ) }
					/>

					<div className="sp-sidebar-field">
						<span className="sp-label">{ __( 'Last date of the payment', 'smartpay' ) }</span>
						<input
							type="date"
							className="components-text-control__input sp-input"
							value={ goal.stopCollectionDate || '' }
							onChange={ ( e ) => updateGoal( { stopCollectionDate: e.target.value } ) }
						/>
					</div>

					{ ( goal.behaviorWhenGoalMet === 'stop_orders' || goal.stopCollectionDate ) && (
						<div className="sp-sidebar-field">
							<span className="sp-label">{ __( 'Goal Met Message', 'smartpay' ) }</span>
							<TextControl
								__nextHasNoMarginBottom
								className="sp-input"
								value={ goal.goalMetMessage || '' }
								onChange={ ( val ) => updateGoal( { goalMetMessage: val } ) }
								placeholder={ __( 'Goal reached! Orders are closed.', 'smartpay' ) }
							/>
						</div>
					) }
				</>
			) }
		</div>
	);
};

/**
 * Options panel backed by `_smartpay_settings` post meta.
 */
const OptionsPanel = () => {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'smartpay_form', 'meta' );
	const { TextControl, ToggleControl, SelectControl } = wp.components;

	const rawSettings = meta?._smartpay_settings || '{}';
	const settings = ( () => {
		try {
			const parsed = JSON.parse( rawSettings );
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch {
			return {};
		}
	} )();

	const updateSettings = ( data ) => {
		setMeta( { ...meta, _smartpay_settings: JSON.stringify( { ...settings, ...data } ) } );
	};

	return (
		<div className="sp-options-panel sp-options-panel--sidebar">
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Show Form Title', 'smartpay' ) }
				checked={ settings.show_title !== false }
				onChange={ ( val ) => updateSettings( { show_title: val } ) }
			/>


			<div className="sp-sidebar-field">
				<SelectControl
					__nextHasNoMarginBottom
					label={ __( 'Form Max Width', 'smartpay' ) }
					value={ settings.form_max_width || '' }
					options={ [
						{ value: '',       label: __( 'Auto (default)', 'smartpay' ) },
						{ value: 'narrow', label: __( 'Narrow — 480px', 'smartpay' ) },
						{ value: 'medium', label: __( 'Medium — 680px', 'smartpay' ) },
						{ value: 'wide',   label: __( 'Wide — 860px', 'smartpay' ) },
						{ value: 'full',   label: __( 'Full width', 'smartpay' ) },
					] }
					onChange={ ( val ) => updateSettings( { form_max_width: val } ) }
				/>
			</div>

			<div className="sp-sidebar-field">
				<SelectControl
					__nextHasNoMarginBottom
					label={ __( 'Checkout Layout', 'smartpay' ) }
					help={ __( 'Split places the payment method next to the form fields on wide screens.', 'smartpay' ) }
					value={ settings.checkout_layout || 'stacked' }
					options={ [
						{ value: 'stacked', label: __( 'Stacked (default)', 'smartpay' ) },
						{ value: 'split',   label: __( 'Split — fields left, payment right', 'smartpay' ) },
					] }
					onChange={ ( val ) => updateSettings( { checkout_layout: val } ) }
				/>
			</div>

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Require Login to Checkout', 'smartpay' ) }
				help={ __( 'Visitors must log in before they can see and submit this payment form.', 'smartpay' ) }
				checked={ !! settings.require_login }
				onChange={ ( val ) => updateSettings( { require_login: val } ) }
			/>
		</div>
	);
};

/**
 * Template injection — runs when the editor loads from a template import.
 * PHP passes `window.spTemplateBlocks` via inline script when `?sp_template=ID` is present.
 */
wp.domReady( () => {
	const defs = window.spTemplateBlocks;
	if ( ! defs || ! defs.length ) return;

	let attempts = 0;
	const tryInject = () => {
		attempts++;
		try {
			const editorStore = wp.data.select( 'core/block-editor' );
			if ( typeof editorStore?.getBlocks !== 'function' ) {
				if ( attempts < 30 ) setTimeout( tryInject, 300 );
				return;
			}
			if ( ! wp.blocks.getBlockType( 'smartpay-form/name' ) ) {
				if ( attempts < 30 ) setTimeout( tryInject, 300 );
				return;
			}

			// Recursively build nested block trees so composite fields
			// (parent → label + input/options children) carry their attrs.
			const buildBlock = ( { name, attrs, innerBlocks } ) =>
				wp.blocks.createBlock(
					name,
					attrs || {},
					Array.isArray( innerBlocks ) ? innerBlocks.map( buildBlock ) : []
				);

			const blocks = defs.map( buildBlock );

			wp.data.dispatch( 'core/block-editor' ).resetBlocks( blocks );
			delete window.spTemplateBlocks;

			const tmplMeta = window.spTemplateMeta;
			if ( tmplMeta ) {
				const metaUpdate = {};
				if ( tmplMeta.amounts ) {
					metaUpdate._smartpay_amounts = JSON.stringify( tmplMeta.amounts );
				}
				if ( tmplMeta.settings ) {
					metaUpdate._smartpay_settings = JSON.stringify( tmplMeta.settings );
				}
				if ( Object.keys( metaUpdate ).length ) {
					wp.data.dispatch( 'core/editor' ).editPost( { meta: metaUpdate } );
				}
				delete window.spTemplateMeta;
			}
		} catch ( err ) {
			if ( attempts < 30 ) setTimeout( tryInject, 300 );
		}
	};

	setTimeout( tryInject, 1000 );
} );

registerPlugin( 'smartpay-form-sidebar', {
	render: () => {
		const postType = useSelect(
			( select ) => select( 'core/editor' ).getCurrentPostType(),
			[]
		);

		if ( postType !== 'smartpay_form' ) {
			return null;
		}

		const { logoUrl, formsListUrl } = window.smartpayFormEditor || {};

		return (
			<>
				{ /* Guide + Settings modals, header buttons, and top-left Add Field button */ }
				<FormGuide />

				{ MainDashboardButton && (
					<MainDashboardButton>
						<a
							href={ formsListUrl || '#' }
							className="sp-dashboard-btn"
							aria-label={ __( 'Back to Forms', 'smartpay' ) }
						>
							{ logoUrl
								? <img src={ logoUrl } alt="SmartPay" className="sp-dashboard-logo" />
								: <GearIcon />
							}
						</a>
					</MainDashboardButton>
				) }
			</>
		);
	},
} );
