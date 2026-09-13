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
    useBlockProps,
    useInnerBlocksProps,
    __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
    __experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { gridJustifyStyle } from './layout'

const DEFAULT_OPTION = {
    name: 'smartpay-form/pricing-option',
    attributesToCopy: ['billing_type', 'billing_period', 'style', 'className'],
}

export const edit = ({ attributes, setAttributes, clientId }) => {
    const {
        preset,
        labelAlign,
        selectedBorderColor,
        tickerColor,
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
    if (selectedBorderColor) wrapperStyle['--sp-selected-border'] = selectedBorderColor
    if (tickerColor) wrapperStyle['--sp-ticker'] = tickerColor
    if (customInputBackground) wrapperStyle['--sp-input-bg'] = customInputBackground
    if (customInputBorder) wrapperStyle['--sp-input-border'] = customInputBorder

    // Theme palettes/gradients, so our swatches match the native Color panel.
    const colorGradientSettings = useMultipleOriginColorsAndGradients()

    // Filled into the `color` group so these sit *inside* the native Color
    // panel rather than as a second, separate colour panel next to it.
    const colorSettings = [
        {
            label: __('Selected border', 'smartpay'),
            colorValue: selectedBorderColor,
            onColorChange: (v) => setAttributes({ selectedBorderColor: v || '' }),
            resetAllFilter: () => ({ selectedBorderColor: '' }),
        },
        {
            label: __('Ticker', 'smartpay'),
            colorValue: tickerColor,
            onColorChange: (v) => setAttributes({ tickerColor: v || '' }),
            resetAllFilter: () => ({ tickerColor: '' }),
        },
        ...(allowCustomAmount
            ? [
                  {
                      label: __('Custom amount background', 'smartpay'),
                      colorValue: customInputBackground,
                      onColorChange: (v) =>
                          setAttributes({ customInputBackground: v || '' }),
                      resetAllFilter: () => ({ customInputBackground: '' }),
                  },
                  {
                      label: __('Custom amount border', 'smartpay'),
                      colorValue: customInputBorder,
                      onColorChange: (v) =>
                          setAttributes({ customInputBorder: v || '' }),
                      resetAllFilter: () => ({ customInputBorder: '' }),
                  },
              ]
            : []),
    ]

    const blockProps = useBlockProps({
        className: `form--amount-section smartpay-pricing is-style-${preset || 'grid'}${
            labelAlign ? ` is-label-${labelAlign}` : ''
        }${showPlanName === false ? ' is-hide-name' : ''}${
            showDescription === false ? ' is-hide-desc' : ''
        }`,
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

            {/*
             * List View tab — sits alongside the repeatable pricing options, so
             * "add another way to pay" lives next to the options themselves
             * rather than in a separate Settings panel.
             */}
            <InspectorControls group="list">
                <PanelBody
                    title={__('Custom Amount', 'smartpay')}
                    initialOpen={true}
                >
                    <ToggleControl
                        label={__('Allow custom amount', 'smartpay')}
                        help={__(
                            'Adds a free-entry amount field below the options.',
                            'smartpay'
                        )}
                        checked={allowCustomAmount}
                        onChange={(v) => setAttributes({ allowCustomAmount: v })}
                        __nextHasNoMarginBottom
                    />
                    {allowCustomAmount && (
                        <TextControl
                            label={__('Custom amount label', 'smartpay')}
                            value={customAmountLabel}
                            onChange={(v) =>
                                setAttributes({ customAmountLabel: v })
                            }
                            __nextHasNoMarginBottom
                        />
                    )}
                </PanelBody>
            </InspectorControls>

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
                    <ToggleGroupControl
                        label={__('Label alignment', 'smartpay')}
                        value={labelAlign || ''}
                        isBlock
                        onChange={(v) => setAttributes({ labelAlign: v || '' })}
                        help={__(
                            'Aligns each option’s label inside its card. Default follows the preset.',
                            'smartpay'
                        )}
                        __nextHasNoMarginBottom
                    >
                        <ToggleGroupControlOption
                            value=""
                            label={__('Default', 'smartpay')}
                        />
                        <ToggleGroupControlOption
                            value="left"
                            label={__('Left', 'smartpay')}
                        />
                        <ToggleGroupControlOption
                            value="center"
                            label={__('Center', 'smartpay')}
                        />
                        <ToggleGroupControlOption
                            value="right"
                            label={__('Right', 'smartpay')}
                        />
                    </ToggleGroupControl>
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
            </InspectorControls>

            {/*
             * Styles tab — filling the `color` group merges these swatches into
             * the block's native Color panel (which already carries Background),
             * so the Styles tab shows one colour group rather than several.
             */}
            <InspectorControls group="color">
                <ColorGradientSettingsDropdown
                    __experimentalIsRenderedInSidebar
                    settings={colorSettings}
                    panelId={clientId}
                    {...colorGradientSettings}
                />
            </InspectorControls>
        </>
    )
}
