import { Trash2, HardDrive, Package, Image } from 'react-feather'
import { __ } from '@wordpress/i18n'
import Swal from 'sweetalert2/dist/sweetalert2'
import { DeleteProduct } from '../../../http/product'
import { variationDefaultData } from '../../../utils/constant'
import { OptionComponent } from './option'

const { useEffect } = wp.element

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

const DetailCard = ({ title, children }) => (
    <div className="sp-detail-card" style={{ marginBottom: 16 }}>
        <div className="sp-detail-card__header">
            <span className="sp-detail-card__title">{title}</span>
        </div>
        <div className="sp-detail-card__body">{children}</div>
    </div>
)

/* ── Cover Image — exported for sidebar use ───────────────── */

export const CoverImageCard = ({ product, setProductData }) => {
    const selectCover = () => {
        const mediaWindow = wp.media({ multiple: false })
        mediaWindow.open()
        mediaWindow.on('select', function () {
            const selection = mediaWindow.state().get('selection')
            const covers = selection.toJSON().map((cover) => ({
                attachment_id: cover.id,
                icon: cover.sizes?.thumbnail?.url || cover.icon,
                url: cover.url,
            }))
            setProductData({ covers: [covers[0]] })
        })
    }

    return (
        <div className="sp-detail-card" style={{ marginBottom: 16 }}>
            <div className="sp-detail-card__header">
                <span className="sp-detail-card__title">{__('Cover Image', 'smartpay')}</span>
            </div>
            <div className="sp-detail-card__body">
                {product.covers.length > 0 ? (
                    <div style={{ textAlign: 'center' }}>
                        <img
                            src={product.covers[0].url}
                            alt=""
                            style={{
                                width: '100%',
                                maxHeight: 160,
                                objectFit: 'cover',
                                borderRadius: 'var(--sp-radius-sm)',
                                border: '1px solid var(--sp-border)',
                                display: 'block',
                                marginBottom: 10,
                            }}
                        />
                        <button
                            type="button"
                            className="sp-btn sp-btn--outline"
                            onClick={selectCover}
                            style={{ width: '100%', justifyContent: 'center' }}
                        >
                            {__('Change Image', 'smartpay')}
                        </button>
                    </div>
                ) : (
                    <div
                        style={{
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            padding: '24px 12px',
                            border: '2px dashed var(--sp-border)',
                            borderRadius: 'var(--sp-radius)',
                            background: 'var(--sp-surface-muted)',
                            textAlign: 'center',
                        }}
                    >
                        <Image size={28} color="var(--sp-text-subtle)" style={{ marginBottom: 8 }} />
                        <p style={{ margin: '0 0 10px', color: 'var(--sp-text-muted)', fontSize: 12.5 }}>
                            {__('Featured image', 'smartpay')}
                        </p>
                        <button
                            type="button"
                            className="sp-btn sp-btn--outline"
                            onClick={selectCover}
                            style={{ width: '100%', justifyContent: 'center' }}
                        >
                            {__('Choose Image', 'smartpay')}
                        </button>
                    </div>
                )}
            </div>
        </div>
    )
}

/* ── Main product form ────────────────────────────────────── */

export const ProductForm = ({ product, setProductData, activeTab }) => {
    useEffect(() => {
        tinymce.execCommand('mceRemoveEditor', true, 'description')
        wp.oldEditor.initialize('description', {
            tinymce: {
                setup: function (editor) {
                    editor.on('change', function (e) {
                        setProductData({ description: e.target.getContent() })
                    })
                },
            },
        })
    }, [])

    useEffect(() => {
        const editor = tinymce.get('description')
        if (editor && !editor.getContent()) {
            editor.setContent(product.description)
        }
    }, [product])

    const _setProductData = (event) => {
        setProductData({ [event.target.name]: event.target.value })
    }

    const _setVariationData = (variation, event) => {
        setVariationData(variation, { [event.target.name]: event.target.value })
    }

    const setVariationData = (variation, data) => {
        const variations = product.variations.map((v) =>
            v.key === variation.key ? { ...v, ...data } : v
        )
        setProductData({ variations })
    }

    const addProductFile = (variation = false) => {
        const mediaWindow = wp.media({ multiple: true })
        mediaWindow.open()
        mediaWindow.on('select', function () {
            const selection = mediaWindow.state().get('selection')
            const files = selection.toJSON().map((file) => ({
                id: file.id,
                name: file.filename,
                icon: file.sizes?.thumbnail?.url || file.icon,
                mime: file.mime,
                size: file.filesizeHumanReadable,
                url: file.url,
            }))
            setProductData({ files: [...product.files, ...files] })
            if (!!variation) {
                setVariationData(variation, { files: [...variation.files, ...files] })
            }
        })
    }

    const removeProductFile = (file) => {
        setProductData({ files: product.files.filter((f) => f.id !== file.id) })
    }

    const addNewVariation = () => {
        setProductData({
            variations: [
                ...product.variations,
                { ...variationDefaultData, key: `new-${Date.now()}` },
            ],
        })
    }

    const removeVariation = (variation) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                setProductData({
                    variations: product.variations.filter((item) => item.key !== variation.key),
                })
                if (variation?.id) {
                    DeleteProduct(variation.id).then((response) => {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: __(response.message, 'smartpay'),
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            showClass: { popup: 'swal2-noanimation' },
                            hideClass: { popup: '' },
                        })
                    })
                }
            }
        })
    }

    const toggleVariationFile = (variation, file, shouldInclude = true) => {
        let files
        if (shouldInclude) {
            variation.files.push(file)
            files = variation.files
        } else {
            files = variation.files.filter((vFile) => vFile.id != file.id)
        }
        setVariationData(variation, { files })
    }

    return (
        <>
            {/* ── Details tab ─────────────────────────────────── */}
            <div style={{ display: activeTab === 'details' ? 'block' : 'none' }}>
                <DetailCard title={__('Product Details', 'smartpay')}>
                    <div style={{ marginBottom: 16 }}>
                        <label style={labelStyle} htmlFor="title">
                            {__('Title', 'smartpay')}
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value={product?.title || ''}
                            placeholder={__('Your awesome product title here', 'smartpay')}
                            onChange={_setProductData}
                            style={inputStyle}
                        />
                    </div>
                    <div>
                        <label style={labelStyle} htmlFor="description">
                            {__('Description', 'smartpay')}
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            value={product.description}
                            onChange={_setProductData}
                        />
                    </div>
                </DetailCard>

                <DetailCard title={__('Files', 'smartpay')}>
                    {product?.files?.length > 0 ? (
                        <>
                            <div style={{ marginBottom: 14 }}>
                                {product.files.map((file, index) => (
                                    <div
                                        key={index}
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 10,
                                            padding: '9px 0',
                                            borderBottom:
                                                index < product.files.length - 1
                                                    ? '1px solid var(--sp-border)'
                                                    : 'none',
                                        }}
                                    >
                                        <img src={file.icon || ''} alt="" width={24} height={24} style={{ flexShrink: 0 }} />
                                        <div style={{ flex: 1, minWidth: 0 }}>
                                            <div
                                                style={{
                                                    fontSize: 13,
                                                    fontWeight: 500,
                                                    color: 'var(--sp-text)',
                                                    overflow: 'hidden',
                                                    textOverflow: 'ellipsis',
                                                    whiteSpace: 'nowrap',
                                                }}
                                            >
                                                {file.name}
                                            </div>
                                            <div style={{ fontSize: 11.5, color: 'var(--sp-text-muted)' }}>
                                                {file.size}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => removeProductFile(file)}
                                            style={{
                                                background: 'none',
                                                border: 'none',
                                                cursor: 'pointer',
                                                color: 'var(--sp-text-subtle)',
                                                padding: 4,
                                                display: 'flex',
                                                flexShrink: 0,
                                            }}
                                        >
                                            <Trash2 size={14} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                            <button
                                type="button"
                                className="sp-btn sp-btn--outline"
                                onClick={() => addProductFile()}
                            >
                                {__('Upload More Files', 'smartpay')}
                            </button>
                        </>
                    ) : (
                        <div
                            style={{
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                justifyContent: 'center',
                                padding: '32px 16px',
                                border: '2px dashed var(--sp-border)',
                                borderRadius: 'var(--sp-radius)',
                                background: 'var(--sp-surface-muted)',
                                textAlign: 'center',
                            }}
                        >
                            <HardDrive size={32} color="var(--sp-text-subtle)" style={{ marginBottom: 10 }} />
                            <p style={{ margin: '0 0 12px', color: 'var(--sp-text-muted)', fontSize: 13 }}>
                                {__('Upload or select files for this product', 'smartpay')}
                            </p>
                            <button
                                type="button"
                                className="sp-btn sp-btn--outline"
                                onClick={() => addProductFile()}
                            >
                                {__('Upload Files', 'smartpay')}
                            </button>
                        </div>
                    )}
                </DetailCard>
            </div>

            {/* ── Pricing tab ─────────────────────────────────── */}
            <div style={{ display: activeTab === 'pricing' ? 'block' : 'none' }}>

                {/* Base price / sale price — hidden once variations exist */}
                {!product.variations.length && (
                    <DetailCard title={__('Pricing', 'smartpay')}>
                        {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                            'smartpay.product.price.section',
                            [],
                            product,
                            setProductData
                        )}
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div>
                                <label style={labelStyle} htmlFor="base_price">
                                    {__('Base Price', 'smartpay')}
                                </label>
                                <input
                                    type="text"
                                    id="base_price"
                                    name="base_price"
                                    value={product.base_price || ''}
                                    placeholder={__('eg. 100', 'smartpay')}
                                    onChange={_setProductData}
                                    style={inputStyle}
                                />
                            </div>
                            <div>
                                <label style={labelStyle} htmlFor="sale_price">
                                    {__('Sale Price', 'smartpay')}
                                </label>
                                <input
                                    type="text"
                                    id="sale_price"
                                    name="sale_price"
                                    value={product?.sale_price || ''}
                                    placeholder={__('eg. 90', 'smartpay')}
                                    onChange={_setProductData}
                                    style={inputStyle}
                                />
                            </div>
                        </div>
                    </DetailCard>
                )}

                {/* One card per variation */}
                {product.variations.map((variation, vIdx) => (
                    <div
                        key={variation.key}
                        className="sp-detail-card"
                        style={{ marginBottom: 16 }}
                    >
                        <div className="sp-detail-card__header">
                            <span className="sp-detail-card__title">
                                {variation.title || `${__('Option', 'smartpay')} ${vIdx + 1}`}
                            </span>
                            <button
                                type="button"
                                onClick={() => removeVariation(variation)}
                                title={__('Remove', 'smartpay')}
                                style={{
                                    background: 'none',
                                    border: 'none',
                                    cursor: 'pointer',
                                    color: 'var(--sp-text-subtle)',
                                    padding: 0,
                                    display: 'flex',
                                    lineHeight: 1,
                                }}
                            >
                                <Trash2 size={14} />
                            </button>
                        </div>

                        <div className="sp-detail-card__body">
                            <div style={{ marginBottom: 16 }}>
                                <label style={labelStyle}>{__('Option Name', 'smartpay')}</label>
                                <input
                                    type="text"
                                    name="title"
                                    value={variation.title}
                                    placeholder={__('eg. Standard, Premium…', 'smartpay')}
                                    onChange={(e) => _setVariationData(variation, e)}
                                    style={inputStyle}
                                />
                            </div>

                            {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                                'smartpay.product.variation.price.section',
                                [],
                                variation,
                                setVariationData
                            )}

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 16 }}>
                                <div>
                                    <label style={labelStyle}>{__('Base Price', 'smartpay')}</label>
                                    <input
                                        type="text"
                                        name="base_price"
                                        value={variation.base_price}
                                        placeholder={__('eg. 100', 'smartpay')}
                                        onChange={(e) => _setVariationData(variation, e)}
                                        style={inputStyle}
                                    />
                                </div>
                                <div>
                                    <label style={labelStyle}>{__('Sale Price', 'smartpay')}</label>
                                    <input
                                        type="text"
                                        name="sale_price"
                                        value={variation.sale_price}
                                        placeholder={__('eg. 90', 'smartpay')}
                                        onChange={(e) => _setVariationData(variation, e)}
                                        style={inputStyle}
                                    />
                                </div>
                            </div>

                            <div style={{ marginBottom: 16 }}>
                                <label style={labelStyle}>{__('Description', 'smartpay')}</label>
                                <textarea
                                    name="description"
                                    value={variation.description}
                                    onChange={(e) => _setVariationData(variation, e)}
                                    rows={2}
                                    style={{ ...inputStyle, height: 'auto', padding: '8px 12px', resize: 'vertical' }}
                                />
                            </div>

                            <div>
                                <label style={{ ...labelStyle, marginBottom: 8 }}>
                                    {__('Files', 'smartpay')}
                                </label>
                                {product.files.length > 0 ? (
                                    <div>
                                        {product.files.map((file, fIdx) => {
                                            const isFileExist =
                                                variation.files.findIndex((vFile) => vFile.id === file.id) >= 0
                                            return (
                                                <label
                                                    key={fIdx}
                                                    style={{
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        gap: 8,
                                                        padding: '5px 0',
                                                        cursor: 'pointer',
                                                        fontSize: 13,
                                                        color: 'var(--sp-text)',
                                                        borderBottom:
                                                            fIdx < product.files.length - 1
                                                                ? '1px solid var(--sp-border)'
                                                                : 'none',
                                                    }}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={isFileExist}
                                                        onChange={(e) =>
                                                            toggleVariationFile(variation, file, e.target.checked)
                                                        }
                                                        style={{ accentColor: 'var(--sp-brand)' }}
                                                    />
                                                    {file.name}
                                                </label>
                                            )
                                        })}
                                        <div style={{ marginTop: 10 }}>
                                            <button
                                                type="button"
                                                className="sp-btn sp-btn--outline"
                                                style={{ fontSize: 12, height: 30, padding: '0 10px' }}
                                                onClick={() => addProductFile(variation)}
                                            >
                                                {__('Upload More Files', 'smartpay')}
                                            </button>
                                        </div>
                                    </div>
                                ) : (
                                    <div
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 10,
                                            padding: '10px 14px',
                                            borderRadius: 'var(--sp-radius-sm)',
                                            border: '1px dashed var(--sp-border)',
                                            background: 'var(--sp-surface-muted)',
                                        }}
                                    >
                                        <Package size={16} color="var(--sp-text-subtle)" style={{ flexShrink: 0 }} />
                                        <span style={{ fontSize: 12.5, color: 'var(--sp-text-muted)', flex: 1 }}>
                                            {__('Associate files with this variant', 'smartpay')}
                                        </span>
                                        <button
                                            type="button"
                                            className="sp-btn sp-btn--outline"
                                            style={{ fontSize: 12, padding: '0 10px', flexShrink: 0 }}
                                            onClick={() => addProductFile(variation)}
                                        >
                                            {__('Select Files', 'smartpay')}
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                ))}

                {/* Add Option — always at the bottom */}
                <button
                    type="button"
                    className="sp-btn sp-btn--outline"
                    onClick={addNewVariation}
                    style={{ width: '100%', justifyContent: 'center' }}
                >
                    {__('+ Add Option', 'smartpay')}
                </button>
            </div>

            {/* ── Checkout tab ────────────────────────────────── */}
            <div style={{ display: activeTab === 'checkout' ? 'block' : 'none' }}>
                <OptionComponent product={product} setProductData={setProductData} unwrapped={false} />
            </div>
        </>
    )
}
