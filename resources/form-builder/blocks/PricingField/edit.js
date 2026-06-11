import {
    PanelBody,
    TextControl,
    ToggleControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
    __experimentalUnitControl as UnitControl,
} from '@wordpress/components'
import {
    InspectorControls,
    PanelColorSettings,
    useBlockProps,
    useInnerBlocksProps,
} from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { gridJustifyStyle } from './layout'

const DEFAULT_OPTION = {
    name: 'smartpay-form/pricing-option',
    attributesToCopy: ['billing_type', 'billing_period', 'style', 'className'],
}

export const edit = ({ attributes, setAttributes }) => {
    const {
        preset,
        showPlanName,
        showDescription,
        allowCustomAmount,
        customAmountLabel,
        currencySymbol,
        gap,
        layout,
        customInputBackground,
        customInputBorder,
    } = attributes

    const wrapperStyle = {
        '--sp-currency': `'${currencySymbol}'`,
    }
    if (gap) wrapperStyle['--sp-plan-gap'] = gap
    if (customInputBackground) wrapperStyle['--sp-input-bg'] = customInputBackground
    if (customInputBorder) wrapperStyle['--sp-input-border'] = customInputBorder

    const blockProps = useBlockProps({
        className: `form--amount-section smartpay-pricing is-style-${preset || 'grid'}${
            showPlanName === false ? ' is-hide-name' : ''
        }${showDescription === false ? ' is-hide-desc' : ''}`,
        style: wrapperStyle,
    })

    // The native Layout "justification" lands on the block root (.smartpay-pricing),
    // but the cards live in the nested .form-plan-grid — so forward it there.
    const gridStyle = gridJustifyStyle(layout)

    const innerBlocksProps = useInnerBlocksProps(
        { className: 'form-plan-grid', style: gridStyle },
        {
            allowedBlocks: ['smartpay-form/pricing-option'],
            template: [
                ['smartpay-form/pricing-option', { label: 'Basic', amount: '0' }],
            ],
            templateInsertUpdatesSelection: true,
            defaultBlock: DEFAULT_OPTION,
            directInsert: true,
            orientation: layout?.orientation ?? 'horizontal',
        }
    )

    return (
        <>
            <div {...blockProps}>
                <div className="form-amounts">
                    <div {...innerBlocksProps} />

                    {allowCustomAmount && (
                        <div className="form-group custom-amount-wrapper m-0">
                            <label className="form-amounts--label d-block m-0 mb-2">
                                {customAmountLabel}
                            </label>
                            <div className="input-group mb-3">
                                <div className="input-group-prepend">
                                    <span className="input-group-text px-3">
                                        {currencySymbol}
                                    </span>
                                </div>
                                <input
                                    type="text"
                                    className="form-control form--custom-amount amount"
                                    disabled
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <InspectorControls>
                <PanelBody title={__('Pricing', 'smartpay')} initialOpen={true}>
                    <ToggleGroupControl
                        label={__('Layout preset', 'smartpay')}
                        value={preset || 'grid'}
                        isBlock
                        onChange={(v) => setAttributes({ preset: v })}
                        __nextHasNoMarginBottom
                    >
                        <ToggleGroupControlOption
                            value="grid"
                            label={__('Grid', 'smartpay')}
                        />
                        <ToggleGroupControlOption
                            value="list"
                            label={__('List', 'smartpay')}
                        />
                        <ToggleGroupControlOption
                            value="compact"
                            label={__('Compact', 'smartpay')}
                        />
                    </ToggleGroupControl>
                    <p className="components-base-control__help">
                        {__(
                            'Custom styles (Settings → Layout, Styles → Color/Typography/Dimensions, and per-option styles) layer on top of the preset.',
                            'smartpay'
                        )}
                    </p>
                    <ToggleControl
                        label={__('Show Plan name', 'smartpay')}
                        help={__(
                            'Display each option’s plan name (label).',
                            'smartpay'
                        )}
                        checked={showPlanName !== false}
                        onChange={(v) => setAttributes({ showPlanName: v })}
                        __nextHasNoMarginBottom
                    />
                    <ToggleControl
                        label={__('Show option descriptions', 'smartpay')}
                        help={__(
                            'Display each option’s description (List preset only).',
                            'smartpay'
                        )}
                        checked={showDescription !== false}
                        onChange={(v) => setAttributes({ showDescription: v })}
                        __nextHasNoMarginBottom
                    />
                    <UnitControl
                        label={__('Gap between options', 'smartpay')}
                        value={gap}
                        onChange={(v) => setAttributes({ gap: v ?? '' })}
                        units={[
                            { value: 'px', label: 'px', default: 12 },
                            { value: 'em', label: 'em', default: 1 },
                            { value: 'rem', label: 'rem', default: 1 },
                        ]}
                        min={0}
                        placeholder={__('Default', 'smartpay')}
                        __next40pxDefaultSize
                    />
                    <TextControl
                        label={__('Currency symbol', 'smartpay')}
                        value={currencySymbol}
                        onChange={(v) => setAttributes({ currencySymbol: v })}
                        __nextHasNoMarginBottom
                    />
                </PanelBody>

                <PanelBody title={__('Custom Amount', 'smartpay')} initialOpen={false}>
                    <ToggleControl
                        label={__('Allow custom amount', 'smartpay')}
                        checked={allowCustomAmount}
                        onChange={(v) => setAttributes({ allowCustomAmount: v })}
                        __nextHasNoMarginBottom
                    />
                    {allowCustomAmount && (
                        <TextControl
                            label={__('Custom amount label', 'smartpay')}
                            value={customAmountLabel}
                            onChange={(v) => setAttributes({ customAmountLabel: v })}
                            __nextHasNoMarginBottom
                        />
                    )}
                </PanelBody>

                {allowCustomAmount && (
                    <PanelColorSettings
                        title={__('Custom Amount Input', 'smartpay')}
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: customInputBackground,
                                onChange: (v) =>
                                    setAttributes({ customInputBackground: v || '' }),
                                label: __('Background', 'smartpay'),
                            },
                            {
                                value: customInputBorder,
                                onChange: (v) =>
                                    setAttributes({ customInputBorder: v || '' }),
                                label: __('Border', 'smartpay'),
                            },
                        ]}
                    />
                )}
            </InspectorControls>
        </>
    )
}
