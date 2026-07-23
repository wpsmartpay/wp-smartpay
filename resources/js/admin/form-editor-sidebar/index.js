import { __ } from '@wordpress/i18n';
import { useRef, useEffect, useState, createPortal } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { Button, Modal } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import {
	PluginDocumentSettingPanel,
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
	{ name: 'smartpay-form/address-input',  label: __( 'Address',     'smartpay' ), required: false },
];

// Blocks that may only appear once in the form.
const UNIQUE_BLOCKS = new Set( [ 'smartpay-form/name', 'smartpay-form/email' ] );

/**
 * Quick-add strip — always visible below the canvas.
 * Smart: tracks which unique blocks are already in the form.
 */
const QuickAddStrip = ( { onAddField, usedNames } ) => {
	const hasName  = usedNames.has( 'smartpay-form/name' );
	const hasEmail = usedNames.has( 'smartpay-form/email' );
	const allRequired = hasName && hasEmail;

	const label = ! allRequired
		? __( 'Required fields missing —', 'smartpay' )
		: __( 'Insert field:', 'smartpay' );

	return (
		<div className="sp-quick-add-strip">
			<div className="sp-quick-add-strip__inner">
				<span className={ `sp-quick-add-strip__label${ ! allRequired ? ' sp-quick-add-strip__label--warn' : '' }` }>
					{ label }
				</span>
				{ SP_BLOCKS.map( ( { name, label: blkLabel, required } ) => {
					const isUsed = UNIQUE_BLOCKS.has( name ) && usedNames.has( name );
					return (
						<button
							key={ name }
							className={ `sp-quick-add-btn${ required && ! isUsed ? ' sp-quick-add-btn--required' : '' }${ isUsed ? ' sp-quick-add-btn--used' : '' }` }
							onClick={ () => ! isUsed && onAddField( name ) }
							disabled={ isUsed }
							title={ isUsed ? __( 'Already in form', 'smartpay' ) : blkLabel }
						>
							{ isUsed ? '✓ ' : '+ ' }{ blkLabel }
						</button>
					);
				} ) }
			</div>
		</div>
	);
};

/**
 * Mounts the quick-add strip via a React portal.
 *
 * The strip lives in the MAIN document below the canvas so form-editor-sidebar.css
 * applies without any iframe CSS injection. When the form is empty it expands into
 * the primary empty-state guide; otherwise it collapses to a slim insert toolbar.
 *
 * Selectors tried (WP 7 → WP 6 fallback):
 *   .editor-visual-editor      — WP 7.0
 *   .edit-post-visual-editor   — WP 6.x
 */
const FormBuilderHelper = () => {
	const blocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks(),
		[]
	);
	const usedNames = new Set( blocks.map( ( b ) => b.name ) );

	const { insertBlocks } = useDispatch( 'core/block-editor' );
	const [ tick, setTick ]   = useState( 0 );
	const quickAddRef = useRef( null );

	useEffect( () => {
		let mounted  = true;
		let rafId    = null;
		let mutTimer = null;

		// Quick-add strip target — main document, below the canvas.
		const getStripTarget = () =>
			document.querySelector( '.interface-interface-skeleton__content' ) ||
			document.querySelector( '.editor-visual-editor' ) ||
			document.querySelector( '.edit-post-visual-editor' );

		const setupPortal = () => {
			const stripTarget = getStripTarget();
			if ( ! stripTarget ) return false;

			if ( ! quickAddRef.current || ! quickAddRef.current.isConnected ) {
				quickAddRef.current?.remove();
				const el = stripTarget.ownerDocument.createElement( 'div' );
				el.className = 'sp-quick-add-portal';
				stripTarget.appendChild( el );
				quickAddRef.current = el;
			}

			if ( mounted ) setTick( ( n ) => n + 1 );
			return true;
		};

		const trySetup = () => {
			if ( ! mounted ) return;
			if ( ! setupPortal() ) rafId = requestAnimationFrame( trySetup );
		};

		rafId = requestAnimationFrame( trySetup );

		const observer = new MutationObserver( () => {
			clearTimeout( mutTimer );
			mutTimer = setTimeout( () => {
				if ( ! mounted ) return;
				if ( ! quickAddRef.current || ! quickAddRef.current.isConnected ) setupPortal();
			}, 200 );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );

		return () => {
			mounted = false;
			cancelAnimationFrame( rafId );
			clearTimeout( mutTimer );
			observer.disconnect();
			quickAddRef.current?.remove();
			quickAddRef.current = null;
		};
	}, [] );

	const addField = ( name ) => insertBlocks( wp.blocks.createBlock( name ) );

	if ( tick === 0 || ! quickAddRef.current ) return null;

	return createPortal(
		<QuickAddStrip onAddField={ addField } usedNames={ usedNames } />,
		quickAddRef.current
	);
};

/**
 * Form guide — a native Modal that walks the user through adding fields.
 *
 * - Auto-opens for a brand-new, empty form (isCleanNewPost).
 * - Re-openable any time via the "Guide" button portaled into the editor header.
 * - Lists required fields (Name, Email) and optional fields, each a one-click add.
 *   Already-added unique fields render disabled with a check.
 * - Native <Modal> provides the X / ESC / overlay close; a Close button is also
 *   offered in the footer.
 */
const FormGuide = () => {
	const blocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks(),
		[]
	);
	const usedNames = new Set( blocks.map( ( b ) => b.name ) );

	const { insertBlocks } = useDispatch( 'core/block-editor' );
	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );
	const [ isOpen, setIsOpen ]     = useState( false );
	const [ btnTick, setBtnTick ]   = useState( 0 );
	const autoOpened = useRef( false );
	const btnRef     = useRef( null );

	// Open the existing form settings panels (Pricing, Form Settings, Goal),
	// which live in the editor's Document settings sidebar.
	const openSettings = () => openGeneralSidebar?.( 'edit-post/document' );

	// Auto-open once when the editor opens — for both empty and populated forms.
	useEffect( () => {
		if ( autoOpened.current ) return;
		autoOpened.current = true;
		setIsOpen( true );
	}, [] );

	// Portal a "Guide" button into the editor header settings area.
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

		const trySetup = () => {
			if ( ! mounted ) return;
			if ( ! setupBtn() ) rafId = requestAnimationFrame( trySetup );
		};

		rafId = requestAnimationFrame( trySetup );

		const observer = new MutationObserver( () => {
			clearTimeout( mutTimer );
			mutTimer = setTimeout( () => {
				if ( ! mounted ) return;
				if ( ! btnRef.current || ! btnRef.current.isConnected ) setupBtn();
			}, 200 );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );

		return () => {
			mounted = false;
			cancelAnimationFrame( rafId );
			clearTimeout( mutTimer );
			observer.disconnect();
			btnRef.current?.remove();
			btnRef.current = null;
		};
	}, [] );

	const addField = ( name ) => insertBlocks( wp.blocks.createBlock( name ) );

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

	return (
		<>
			{ btnTick > 0 && btnRef.current && createPortal(
				<>
					<Button
						variant="tertiary"
						className="sp-guide-trigger"
						onClick={ openSettings }
					>
						{ __( 'Settings', 'smartpay' ) }
					</Button>
					<Button
						variant="tertiary"
						className="sp-guide-trigger"
						onClick={ () => setIsOpen( true ) }
					>
						{ __( 'Guide', 'smartpay' ) }
					</Button>
				</>,
				btnRef.current
			) }

			{ isOpen && (
				<Modal
					title={ __( 'WPSmartPay Help Guide', 'smartpay' ) }
					onRequestClose={ () => setIsOpen( false ) }
					className="sp-guide-modal"
				>
					<p className="sp-guide-modal__desc">
						{ __( 'Build your payment form — click a field below, or type', 'smartpay' ) }
						{ ' ' }<code>/</code>{ ' ' }
						{ __( 'in the editor to add one.', 'smartpay' ) }
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
						<Button
							variant="primary"
							__next40pxDefaultSize
							disabled={ allRequiredAdded }
							onClick={ addRequired }
						>
							{ allRequiredAdded
								? __( 'Required fields added ✓', 'smartpay' )
								: __( 'Add required fields', 'smartpay' ) }
						</Button>
						<Button
							variant="tertiary"
							__next40pxDefaultSize
							onClick={ () => setIsOpen( false ) }
						>
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
				<span className="sp-label">{ __( 'Pay Button Label', 'smartpay' ) }</span>
				<TextControl
					__nextHasNoMarginBottom
					className="sp-input"
					value={ settings.pay_button_label || '' }
					onChange={ ( val ) => updateSettings( { pay_button_label: val } ) }
					placeholder={ __( 'Pay Now', 'smartpay' ) }
				/>
			</div>

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Allow Custom Amount', 'smartpay' ) }
				checked={ !! settings.allow_custom_amount }
				onChange={ ( val ) => updateSettings( { allow_custom_amount: val } ) }
			/>
			{ settings.allow_custom_amount && (
				<div className="sp-sidebar-field sp-sidebar-field--indent">
					<span className="sp-label">{ __( 'Custom Amount Label', 'smartpay' ) }</span>
					<TextControl
						__nextHasNoMarginBottom
						className="sp-input"
						value={ settings.custom_amount_label || '' }
						onChange={ ( val ) => updateSettings( { custom_amount_label: val } ) }
						placeholder={ __( 'Enter custom amount', 'smartpay' ) }
					/>
				</div>
			) }

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Allow External Link', 'smartpay' ) }
				checked={ !! settings.allow_external_link }
				onChange={ ( val ) => updateSettings( { allow_external_link: val } ) }
			/>
			{ settings.allow_external_link && (
				<div className="sp-sidebar-field sp-sidebar-field--indent">
					<span className="sp-label">{ __( 'External Link URL', 'smartpay' ) }</span>
					<TextControl
						__nextHasNoMarginBottom
						className="sp-input"
						value={ settings.external_link_url || '' }
						onChange={ ( val ) => updateSettings( { external_link_url: val } ) }
						placeholder="https://"
					/>
					<span className="sp-label">{ __( 'External Link Label', 'smartpay' ) }</span>
					<TextControl
						__nextHasNoMarginBottom
						className="sp-input"
						value={ settings.external_link_label || '' }
						onChange={ ( val ) => updateSettings( { external_link_label: val } ) }
						placeholder={ __( 'Buy Now', 'smartpay' ) }
					/>
				</div>
			) }

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

			const blocks = defs.map( ( { name, attrs } ) =>
				wp.blocks.createBlock( name, attrs || {} )
			);

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
				{ /* Quick-add strip below the canvas + guide modal / header button */ }
				<FormBuilderHelper />
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

					{ /* Pricing is authored via the Pricing block now; sidebar repeater hidden. */ }

				<PluginDocumentSettingPanel
					name="sp-form-settings"
					title={ __( 'Form Settings', 'smartpay' ) }
					className="sp-sidebar-form-settings"
				>
					<OptionsPanel />
				</PluginDocumentSettingPanel>

				<PluginDocumentSettingPanel
					name="sp-goal"
					title={ __( 'Goal', 'smartpay' ) }
					className="sp-sidebar-goal"
				>
					<GoalPanel />
				</PluginDocumentSettingPanel>
			</>
		);
	},
} );
