import {
    PanelBody,
    Button,
    BaseControl,
    RangeControl,
    ToggleControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components'
import {
    InspectorControls,
    PanelColorSettings,
    RichText,
    MediaUpload,
    MediaUploadCheck,
    useBlockProps,
} from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { SUBMIT_ICONS, SUBMIT_ICON_SLUGS } from '../icons'

/** Whether the button currently shows an icon (preset slug or custom media). */
export const hasSubmitIcon = (a) =>
    'custom' === a.iconType ? !! a.customIconUrl : !! a.icon

/** Resolve the icon node for the editor preview — a custom <img> or a preset SVG. */
const resolveIconNode = (a) => {
    if ('custom' === a.iconType && a.customIconUrl) {
        return (
            <img
                src={a.customIconUrl}
                alt=""
                className="smartpay-submit-icon-img"
                style={{ height: '1.25em', width: 'auto' }}
            />
        )
    }
    if ('custom' !== a.iconType && a.icon && SUBMIT_ICONS[a.icon]) {
        return SUBMIT_ICONS[a.icon]
    }
    return null
}

/**
 * Shared inline style for the rendered button — mirrors what the frontend
 * template (native-form-embed.php) produces so the editor is WYSIWYG.
 */
export const buildButtonStyle = (a) => {
    const style = {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: hasSubmitIcon(a) ? '8px' : 0,
        background: a.bgColor || '#28a745',
        color: a.textColor || '#ffffff',
        borderRadius: `${a.borderRadius ?? 6}px`,
        fontSize: `${a.fontSize ?? 16}px`,
        fontWeight: a.fontWeight || '600',
        padding: `${a.paddingY ?? 14}px ${a.paddingX ?? 24}px`,
        lineHeight: 1.2,
        cursor: 'pointer',
        border:
            a.borderWidth > 0
                ? `${a.borderWidth}px solid ${a.borderColor || 'transparent'}`
                : 'none',
    }
    if (a.fullWidth) {
        style.width = '100%'
    } else if (a.width) {
        style.width = `${a.width}%`
    }
    return style
}

export const edit = ({ attributes, setAttributes }) => {
    const { label, icon, iconType, customIconUrl, iconPosition, align, width, fullWidth } =
        attributes

    const blockProps = useBlockProps({
        className: 'smartpay-submit-button-block',
        style: { textAlign: fullWidth ? 'left' : align },
    })

    const iconNode = resolveIconNode(attributes)

    return (
        <>
            <div {...blockProps}>
                <span
                    className="smartpay-form-pay-now smartpay-submit-preview"
                    style={buildButtonStyle(attributes)}
                >
                    {iconNode && iconPosition === 'left' && iconNode}
                    <RichText
                        tagName="span"
                        value={label}
                        allowedFormats={[]}
                        onChange={(v) => setAttributes({ label: v })}
                        placeholder={__('Pay Now', 'smartpay')}
                    />
                    {iconNode && iconPosition === 'right' && iconNode}
                </span>
            </div>

            <InspectorControls>
                <PanelBody title={__('Button', 'smartpay')} initialOpen={true}>
                    <ToggleGroupControl
                        label={__('Alignment', 'smartpay')}
                        value={align}
                        isBlock
                        disabled={fullWidth}
                        onChange={(v) => setAttributes({ align: v })}
                        __nextHasNoMarginBottom
                    >
                        <ToggleGroupControlOption value="left" label={__('Left', 'smartpay')} />
                        <ToggleGroupControlOption value="center" label={__('Center', 'smartpay')} />
                        <ToggleGroupControlOption value="right" label={__('Right', 'smartpay')} />
                    </ToggleGroupControl>

                    <ToggleControl
                        label={__('Full width', 'smartpay')}
                        checked={fullWidth}
                        onChange={(v) => setAttributes({ fullWidth: v })}
                        __nextHasNoMarginBottom
                    />

                    {!fullWidth && (
                        <ToggleGroupControl
                            label={__('Width', 'smartpay')}
                            value={width || 0}
                            isBlock
                            onChange={(v) => setAttributes({ width: Number(v) })}
                            __nextHasNoMarginBottom
                        >
                            <ToggleGroupControlOption value={0} label={__('Auto', 'smartpay')} />
                            <ToggleGroupControlOption value={25} label="25%" />
                            <ToggleGroupControlOption value={50} label="50%" />
                            <ToggleGroupControlOption value={75} label="75%" />
                            <ToggleGroupControlOption value={100} label="100%" />
                        </ToggleGroupControl>
                    )}
                </PanelBody>

                <PanelBody title={__('Icon', 'smartpay')} initialOpen={false}>
                    <ToggleGroupControl
                        label={__('Icon source', 'smartpay')}
                        value={iconType}
                        isBlock
                        onChange={(v) => setAttributes({ iconType: v })}
                        __nextHasNoMarginBottom
                    >
                        <ToggleGroupControlOption value="preset" label={__('Preset', 'smartpay')} />
                        <ToggleGroupControlOption value="custom" label={__('Custom', 'smartpay')} />
                    </ToggleGroupControl>

                    {'preset' === iconType && (
                        <BaseControl
                            __nextHasNoMarginBottom
                            label={__('Preset icon', 'smartpay')}
                            id="smartpay-submit-icon"
                            style={{ marginTop: '12px' }}
                        >
                            <div className="smartpay-submit-icon-picker">
                                {SUBMIT_ICON_SLUGS.map((slug) => (
                                    <Button
                                        key={slug || 'none'}
                                        className="smartpay-submit-icon-picker__btn"
                                        isPressed={icon === slug}
                                        onClick={() => setAttributes({ icon: slug })}
                                        label={slug || __('None', 'smartpay')}
                                        showTooltip
                                    >
                                        {slug ? SUBMIT_ICONS[slug] : __('None', 'smartpay')}
                                    </Button>
                                ))}
                            </div>
                        </BaseControl>
                    )}

                    {'custom' === iconType && (
                        <BaseControl
                            __nextHasNoMarginBottom
                            label={__('Custom icon', 'smartpay')}
                            id="smartpay-submit-custom-icon"
                            help={__('Select an image or upload an SVG from the Media Library.', 'smartpay')}
                            style={{ marginTop: '12px' }}
                        >
                            <MediaUploadCheck>
                                <MediaUpload
                                    allowedTypes={['image']}
                                    value={attributes.customIconId || undefined}
                                    onSelect={(media) =>
                                        setAttributes({
                                            customIconUrl: media?.url || '',
                                            customIconId: media?.id || 0,
                                        })
                                    }
                                    render={({ open }) => (
                                        <div className="smartpay-submit-custom-icon">
                                            {customIconUrl && (
                                                <img
                                                    src={customIconUrl}
                                                    alt=""
                                                    className="smartpay-submit-custom-icon__preview"
                                                />
                                            )}
                                            <Button variant="secondary" onClick={open} __next40pxDefaultSize>
                                                {customIconUrl
                                                    ? __('Replace icon', 'smartpay')
                                                    : __('Select / Upload icon', 'smartpay')}
                                            </Button>
                                            {customIconUrl && (
                                                <Button
                                                    variant="link"
                                                    isDestructive
                                                    onClick={() =>
                                                        setAttributes({ customIconUrl: '', customIconId: 0 })
                                                    }
                                                >
                                                    {__('Remove', 'smartpay')}
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                />
                            </MediaUploadCheck>
                        </BaseControl>
                    )}

                    {hasSubmitIcon(attributes) && (
                        <ToggleGroupControl
                            label={__('Icon position', 'smartpay')}
                            value={iconPosition}
                            isBlock
                            onChange={(v) => setAttributes({ iconPosition: v })}
                            __nextHasNoMarginBottom
                            style={{ marginTop: '12px' }}
                        >
                            <ToggleGroupControlOption value="left" label={__('Left', 'smartpay')} />
                            <ToggleGroupControlOption value="right" label={__('Right', 'smartpay')} />
                        </ToggleGroupControl>
                    )}
                </PanelBody>

                <PanelColorSettings
                    title={__('Colors', 'smartpay')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: attributes.bgColor,
                            onChange: (v) => setAttributes({ bgColor: v || '' }),
                            label: __('Background', 'smartpay'),
                        },
                        {
                            value: attributes.textColor,
                            onChange: (v) => setAttributes({ textColor: v || '' }),
                            label: __('Text', 'smartpay'),
                        },
                        {
                            value: attributes.borderColor,
                            onChange: (v) => setAttributes({ borderColor: v || '' }),
                            label: __('Border', 'smartpay'),
                        },
                    ]}
                />

                <PanelBody title={__('Typography & Spacing', 'smartpay')} initialOpen={false}>
                    <RangeControl
                        label={__('Font size', 'smartpay')}
                        value={attributes.fontSize}
                        onChange={(v) => setAttributes({ fontSize: v })}
                        min={10}
                        max={40}
                        __nextHasNoMarginBottom
                    />
                    <ToggleGroupControl
                        label={__('Font weight', 'smartpay')}
                        value={attributes.fontWeight}
                        isBlock
                        onChange={(v) => setAttributes({ fontWeight: v })}
                        __nextHasNoMarginBottom
                    >
                        <ToggleGroupControlOption value="400" label={__('Normal', 'smartpay')} />
                        <ToggleGroupControlOption value="600" label={__('Semibold', 'smartpay')} />
                        <ToggleGroupControlOption value="700" label={__('Bold', 'smartpay')} />
                    </ToggleGroupControl>
                    <RangeControl
                        label={__('Vertical padding', 'smartpay')}
                        value={attributes.paddingY}
                        onChange={(v) => setAttributes({ paddingY: v })}
                        min={0}
                        max={40}
                        __nextHasNoMarginBottom
                    />
                    <RangeControl
                        label={__('Horizontal padding', 'smartpay')}
                        value={attributes.paddingX}
                        onChange={(v) => setAttributes({ paddingX: v })}
                        min={0}
                        max={80}
                        __nextHasNoMarginBottom
                    />
                    <RangeControl
                        label={__('Border width', 'smartpay')}
                        value={attributes.borderWidth}
                        onChange={(v) => setAttributes({ borderWidth: v })}
                        min={0}
                        max={8}
                        __nextHasNoMarginBottom
                    />
                    <RangeControl
                        label={__('Border radius', 'smartpay')}
                        value={attributes.borderRadius}
                        onChange={(v) => setAttributes({ borderRadius: v })}
                        min={0}
                        max={50}
                        __nextHasNoMarginBottom
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
