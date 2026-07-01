import { __ } from '@wordpress/i18n'

const labelStyle = {
    display: 'block',
    fontSize: 11,
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.07em',
    color: 'var(--sp-text-muted)',
    marginBottom: 6,
}

const inputStyle = {
    width: '100%',
    height: 36,
    padding: '0 12px',
    border: '1px solid var(--sp-border)',
    borderRadius: 'var(--sp-radius-sm)',
    background: 'var(--sp-surface)',
    fontSize: 13,
    color: 'var(--sp-text)',
    boxSizing: 'border-box',
    outline: 'none',
}

/* unwrapped=true → render bare content (for use inside a tab body) */
export const OptionComponent = ({ product, setProductData, unwrapped = false }) => {
    const _setSettingsData = (settings) => {
        setProductData({ ...product, settings })
    }

    const content = (
        <>
            {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                'smartpay.product.options.before',
                null,
                product,
                setProductData
            )}

            <div style={{ marginBottom: 20 }}>
                <label style={labelStyle} htmlFor="payButtonLabel">
                    {__('Checkout Label', 'smartpay')}
                </label>
                <input
                    type="text"
                    id="payButtonLabel"
                    value={product?.settings?.payButtonLabel || ''}
                    placeholder={__('ex. Get it now', 'smartpay')}
                    onChange={(e) =>
                        _setSettingsData({
                            ...product.settings,
                            payButtonLabel: e.target.value,
                        })
                    }
                    style={inputStyle}
                />
            </div>

            <div
                style={{
                    padding: '14px 16px',
                    border: '1px solid var(--sp-border)',
                    borderRadius: 'var(--sp-radius)',
                    background: 'var(--sp-surface-muted)',
                }}
            >
                <label
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 8,
                        cursor: 'pointer',
                        fontSize: 13,
                        fontWeight: 500,
                        color: 'var(--sp-text)',
                        marginBottom: 0,
                    }}
                >
                    <input
                        type="checkbox"
                        checked={product.settings?.externalLink?.allowExternalLink || false}
                        onChange={(e) =>
                            _setSettingsData({
                                ...product?.settings,
                                externalLink: {
                                    ...product.settings?.externalLink,
                                    allowExternalLink: e.target.checked,
                                },
                            })
                        }
                        style={{ accentColor: 'var(--sp-brand)' }}
                    />
                    {__('Add resource link on Payment Success Page', 'smartpay')}
                </label>

                {product.settings?.externalLink?.allowExternalLink && (
                    <div
                        style={{
                            marginTop: 16,
                            display: 'grid',
                            gridTemplateColumns: '2fr 1fr',
                            gap: 12,
                        }}
                    >
                        <div>
                            <label style={labelStyle}>
                                {__('Resource URL', 'smartpay')}
                            </label>
                            <input
                                type="text"
                                defaultValue={product.settings?.externalLink?.link}
                                placeholder={__('ex. https://resourcelink.com', 'smartpay')}
                                onChange={(e) =>
                                    _setSettingsData({
                                        ...product.settings,
                                        externalLink: {
                                            ...product.settings?.externalLink,
                                            link: e.target.value,
                                        },
                                    })
                                }
                                style={inputStyle}
                            />
                        </div>
                        <div>
                            <label style={labelStyle}>
                                {__('Link Label', 'smartpay')}
                            </label>
                            <input
                                type="text"
                                defaultValue={product.settings?.externalLink?.label}
                                placeholder={__('ex. Show link', 'smartpay')}
                                onChange={(e) =>
                                    _setSettingsData({
                                        ...product?.settings,
                                        externalLink: {
                                            ...product?.settings?.externalLink,
                                            label: e.target.value,
                                        },
                                    })
                                }
                                style={inputStyle}
                            />
                        </div>
                    </div>
                )}
            </div>

            {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                'smartpay.product.options.after',
                null,
                product,
                setProductData
            )}
        </>
    )

    if (unwrapped) return content

    return (
        <div className="sp-detail-card" style={{ marginBottom: 16 }}>
            <div className="sp-detail-card__header">
                <span className="sp-detail-card__title">{__('Checkout Options', 'smartpay')}</span>
            </div>
            <div className="sp-detail-card__body">{content}</div>
        </div>
    )
}
