import * as Feather from 'react-feather'
import { __ } from '@wordpress/i18n'
import { Tabs, Tab, Form, Button, Row, Col, Card } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteProduct } from '../../../http/product'
import { variationDefaultData } from '../../../utils/constant'
import {OptionComponent} from "./option";
const { useEffect, useState } = wp.element

export const ProductForm = ({ product, setProductData }) => {
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
        setVariationData(variation, {
            [event.target.name]: event.target.value,
        })
    }

    const setVariationData = (variation, data) => {
        const variations = product.variations.map((v) =>
            v.key === variation.key ? { ...v, ...data } : v
        )
        setProductData({ variations })
    }

    const selectCover = () => {
        const mediaWindow = wp.media({ multiple: true })

        mediaWindow.open()
        mediaWindow.on('select', function () {
            const selection = mediaWindow.state().get('selection')

            const covers = selection.toJSON().map((cover) => {
                return {
                    attachment_id: cover.id,
                    icon: cover.sizes?.thumbnail?.url || cover.icon,
                    url: cover.url,
                }
            })

            setProductData({ covers: [covers[0]] })
            // setProductData({ covers: [...product.covers, ...covers] })
        })
    }

    const addProductFile = (variation = false) => {
        const mediaWindow = wp.media({ multiple: true })

        mediaWindow.open()
        mediaWindow.on('select', function () {
            const selection = mediaWindow.state().get('selection')

            const files = selection.toJSON().map((file) => {
                return {
                    id: file.id,
                    name: file.filename,
                    icon: file.sizes?.thumbnail?.url || file.icon,
                    mime: file.mime,
                    size: file.filesizeHumanReadable,
                    url: file.url,
                }
            })

            setProductData({ files: [...product.files, ...files] })

            if (!!variation) {
                setVariationData(variation, {
                    files: [...variation.files, ...files],
                })
            }
        })
    }

    const removeProductFile = (file, variation = false) => {
        if (!!variation) {
            variation.files = [
                ...variation.files.filter((variationFile) => {
                    return variationFile.id !== file.id
                }),
            ]

            return
        }

        setProductData({
            files: [
                ...product.files.filter((productFile) => {
                    return productFile.id !== file.id
                }),
            ],
        })
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
                    variations: product.variations.filter((item) => {
                        return item.key !== variation.key
                    }),
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
                            showClass: {
                                popup: 'swal2-noanimation',
                            },
                            hideClass: {
                                popup: '',
                            },
                        })
                    })
                }
            }
        })
    }

    const toggleVariationFile = (variation, file, shouldInclude = true) => {
        let files = {}
        if (shouldInclude) {
            variation.files.push(file)
            files = variation.files
        } else {
            files = variation.files.filter((vFile) => vFile.id != file.id)
        }

        setVariationData(variation, { files: files })
    }

    return (
        <Form className="my-3">
            <Card className="mb-4">
                <Card.Body className="bg-white">
                    <Row>
                        <Col md={8}>
                            <Form.Group controlId="title">
                                <Form.Control
                                    type="text"
                                    name="title"
                                    value={product?.title || ''}
                                    placeholder={__(
                                        'Your awesome product title here',
                                        'smartpay'
                                    )}
                                    onChange={_setProductData}
                                />
                            </Form.Group>
                            <Form.Control
                                as="textarea"
                                name="description"
                                id="description"
                                value={product.description}
                                onChange={_setProductData}
                            />
                        </Col>
                        <Col md={4}>
                            <div className="border rounded bg-light text-center p-4 select-image-box d-flex flex-column align-items-center">
                                {product.covers.length > 0 && (
                                    <div className="mb-3 preview text-center">
                                        <div>
                                            <img
                                                src={product.covers[0].url}
                                                className="img-fluid"
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            onClick={() => selectCover()}
                                            className="btn btn-light border mt-3 px-3 select-image"
                                        >
                                            {__('Choose File', 'smartpay')}
                                        </Button>
                                    </div>
                                )}
                                {product.covers.length === 0 && (
                                    <div className="no-image">
                                        <Feather.Image size={40} />
                                        <h3 className="mt-1">
                                            {__('Cover Image', 'smartpay')}
                                        </h3>
                                        <p className="text-muted">
                                            {__(
                                                'Select a featured image for this product',
                                                'smartpay'
                                            )}
                                        </p>
                                        <Button
                                            type="button"
                                            onClick={() => selectCover()}
                                            className="btn btn-light border px-3 select-image"
                                        >
                                            {__('Choose File', 'smartpay')}
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </Col>
                    </Row>
                </Card.Body>
            </Card>

            <Tabs fill defaultActiveKey="files">
                <Tab
                    eventKey="files"
                    className="text-decoration-none"
                    title={__('Files', 'smartpay')}
                >
                    <div className="product-files-section">
                        {product?.files?.length ? (
                            <>
                                <ul
                                    className="list-group product-files"
                                    id="product-files"
                                >
                                    {product?.files?.map((file, index) => {
                                        return (
                                            <li
                                                className="list-group-item list-group-item-action mb-0 files-item"
                                                key={index}
                                            >
                                                <div className="d-flex">
                                                    <div className="file-type">
                                                        <img
                                                            src={
                                                                file.icon || ''
                                                            }
                                                            alt={
                                                                file.name || ''
                                                            }
                                                            width="28"
                                                            height="28"
                                                        />
                                                    </div>
                                                    <div className="d-flex justify-content-between w-100">
                                                        <div className="d-flex flex-column ml-3">
                                                            <h5 className="file-name m-0">
                                                                {file.name}
                                                            </h5>
                                                            <p className="file-size text-muted m-0">
                                                                {file.size}
                                                            </p>
                                                        </div>
                                                        <div className="">
                                                            <Button
                                                                type="button"
                                                                className="btn btn-light btn-sm pb-0 border remove-file"
                                                                onClick={() =>
                                                                    removeProductFile(
                                                                        file
                                                                    )
                                                                }
                                                            >
                                                                <Feather.Trash />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        )
                                    })}
                                </ul>

                                <div className="border rounded bg-light text-center p-3 mt-3">
                                    <button
                                        type="button"
                                        className="btn btn-secondary upload-product-file"
                                        onClick={() => addProductFile()}
                                    >
                                        {__('Upload your files', 'smartpay')}
                                    </button>
                                </div>
                            </>
                        ) : (
                            <div className="border border-top-0 rounded bg-light text-center p-5 no-product-file-box">
                                <Feather.HardDrive size={42} />
                                <h3 className="text-muted">
                                    {__(
                                        'Upload or select files for this product',
                                        'smartpay'
                                    )}
                                </h3>
                                <Button
                                    type="button"
                                    className="btn btn-light border shadow-sm upload-product-file"
                                    onClick={() => addProductFile()}
                                >
                                    {__('Upload files', 'smartpay')}
                                </Button>
                            </div>
                        )}
                    </div>
                </Tab>
                <Tab
                    eventKey="pricing"
                    className="text-decoration-none"
                    title={__('Pricing & Variation', 'smartpay')}
                >
                    {!product.variations.length && (
                        <div className="form-row">
                            {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                                'smartpay.product.price.section',
                                [],
                                product,
                                setProductData
                            )}
                            <div className="col-6">
                                <div className="form-group">
                                    <label
                                        htmlFor="base_price"
                                        className="text-muted my-2 d-block"
                                    >
                                        {__('Base price', 'smartpay')}
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="base_price"
                                        name="base_price"
                                        value={product.base_price || ''}
                                        placeholder={__('eg. 100', 'smartpay')}
                                        onChange={_setProductData}
                                    />
                                </div>
                            </div>
                            <div className="col-6">
                                <div className="form-group">
                                    <label
                                        htmlFor="sale_price"
                                        className="text-muted my-2 d-block"
                                    >
                                        {__('Sales price', 'smartpay')}
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="sale_price"
                                        name="sale_price"
                                        value={product?.sale_price || ''}
                                        placeholder={__('eg. 90', 'smartpay')}
                                        onChange={_setProductData}
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="smartpay-variations">
                        {/* Has no variation */}
                        {!product.variations.length && (
                            <div className="border rounded bg-light text-center p-5 no-variations-box">
                                <Feather.Layers size={42} />
                                <h3>
                                    {__(
                                        'Offer variations of this product',
                                        'smartpay'
                                    )}
                                </h3>
                                <p className="text-muted">
                                    {__(
                                        'Sweeten the deal for your customers with different options for format, version, etc',
                                        'smartpay'
                                    )}
                                </p>
                                <Button
                                    type="button"
                                    className="btn btn-light border shadow-sm add-variation"
                                    onClick={addNewVariation}
                                >
                                    {__('Add Variations', 'smartpay')}
                                </Button>
                            </div>
                        )}

                        {/* Show variation */}
                        {product.variations.length > 0 && (
                            <div className="card p-0 mt-0 variations-secion">
                                <div className="card-header bg-white p-0">
                                    <div className="d-flex px-3 py-2 align-items-center">
                                        <h3 className="m-0 pt-1 d-flex">
                                            {__('Variations', 'smartpay')}
                                        </h3>
                                        {/* <button
                                            type="button"
                                            className="btn btn-light border btn-sm my-1 ml-auto pb-0 shadow-sm remove-variation"
                                        >
                                            <Feather.Trash />
                                        </button> */}
                                    </div>
                                </div>
                                {product.variations.map((variation) => {
                                    return (
                                        <div
                                            className="variation-option bg-light"
                                            key={variation.key}
                                        >
                                            <div className="variation-option__header p-3">
                                                <div className="form-row">
                                                    <div className="col-11">
                                                        <div className="form-group m-0">
                                                            <label
                                                                htmlFor={`variation-${variation.key}`}
                                                                className="text-muted my-2 d-block"
                                                            >
                                                                {__(
                                                                    'Option name',
                                                                    'smartpay'
                                                                )}
                                                            </label>
                                                            <input
                                                                type="text"
                                                                className="form-control"
                                                                id={`variation-${variation.key}`}
                                                                name="title"
                                                                value={
                                                                    variation.title
                                                                }
                                                                placeholder="Option name"
                                                                onChange={(
                                                                    event
                                                                ) =>
                                                                    _setVariationData(
                                                                        variation,
                                                                        event
                                                                    )
                                                                }
                                                            />
                                                        </div>
                                                    </div>
                                                    <div className="col d-flex align-items-center">
                                                        <div className="mt-4">
                                                            <button
                                                                type="button"
                                                                className="btn btn-light btn-sm border shadow-sm pb-0 ml-2 remove-variation-option"
                                                                onClick={() =>
                                                                    removeVariation(
                                                                        variation
                                                                    )
                                                                }
                                                            >
                                                                <Feather.Trash />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {window.SMARTPAY_PRODUCT_HOOKS.applyFilters(
                                                    'smartpay.product.variation.price.section',
                                                    [],
                                                    variation,
                                                    setVariationData
                                                )}
                                                <div className="form-row">
                                                    <div className="col-6">
                                                        <div className="form-group">
                                                            <label
                                                                htmlFor={`variation-${variation.key}-base_price`}
                                                                className="text-muted my-2 d-block"
                                                            >
                                                                {__(
                                                                    'Base price',
                                                                    'smartpay'
                                                                )}
                                                            </label>
                                                            <input
                                                                type="text"
                                                                className="form-control"
                                                                name="base_price"
                                                                id={`variation-${variation.key}-base_price`}
                                                                value={
                                                                    variation.base_price
                                                                }
                                                                placeholder={__(
                                                                    'eg. 100',
                                                                    'smartpay'
                                                                )}
                                                                onChange={(
                                                                    event
                                                                ) =>
                                                                    _setVariationData(
                                                                        variation,
                                                                        event
                                                                    )
                                                                }
                                                            />
                                                        </div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="form-group">
                                                            <label
                                                                htmlFor={`variation-${variation.key}-sale_price`}
                                                                className="text-muted my-2 d-block"
                                                            >
                                                                {__(
                                                                    'Sales price',
                                                                    'smartpay'
                                                                )}
                                                            </label>
                                                            <input
                                                                type="text"
                                                                className="form-control"
                                                                name="sale_price"
                                                                id={`variation-${variation.key}-sale_price`}
                                                                value={
                                                                    variation.sale_price
                                                                }
                                                                placeholder={__(
                                                                    'eg. 90',
                                                                    'smartpay'
                                                                )}
                                                                onChange={(
                                                                    event
                                                                ) =>
                                                                    _setVariationData(
                                                                        variation,
                                                                        event
                                                                    )
                                                                }
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="variation-option-body bg-light px-3">
                                                <div className="form-group">
                                                    <label
                                                        htmlFor={`variation-${variation.key}-description`}
                                                        className="text-muted my-2 d-block"
                                                    >
                                                        {__(
                                                            'Description',
                                                            'smartpay'
                                                        )}
                                                    </label>
                                                    <textarea
                                                        className="form-control"
                                                        name="description"
                                                        id={`variation-${variation.key}-description`}
                                                        value={
                                                            variation.description
                                                        }
                                                        onChange={(event) =>
                                                            _setVariationData(
                                                                variation,
                                                                event
                                                            )
                                                        }
                                                        rows="3"
                                                    ></textarea>
                                                    <small className="form-text text-muted">
                                                        {__(
                                                            'Do not write HTML code here.',
                                                            'smartpay'
                                                        )}
                                                    </small>
                                                </div>

                                                {/* Variation Files */}
                                                <label className="text-muted my-2 d-block">
                                                    <strong>
                                                        {__(
                                                            'Files',
                                                            'smartpay'
                                                        )}
                                                    </strong>
                                                </label>

                                                {product.files.length > 0 && (
                                                    <div>
                                                        <ul className="list-group variation-files">
                                                            {product?.files?.map(
                                                                (
                                                                    file,
                                                                    index
                                                                ) => {
                                                                    const isFileExist =
                                                                        variation.files.findIndex(
                                                                            (
                                                                                vFile
                                                                            ) => {
                                                                                return file.id ===
                                                                                vFile.id
                                                                                    ? true
                                                                                    : false
                                                                            }
                                                                        ) >= 0
                                                                            ? true
                                                                            : false

                                                                    return (
                                                                        <li
                                                                            className="list-group-item m-0 d-flex justify-content-between files-item"
                                                                            key={
                                                                                index
                                                                            }
                                                                        >
                                                                            <div className="custom-checkbox custom-checkbox-round">
                                                                                <input
                                                                                    type="checkbox"
                                                                                    className="custom-control-input variation-file"
                                                                                    id={`variation-${variation.key}-file-${file.id}`}
                                                                                    name="file"
                                                                                    onChange={(
                                                                                        event
                                                                                    ) =>
                                                                                        toggleVariationFile(
                                                                                            variation,
                                                                                            file,
                                                                                            event
                                                                                                .target
                                                                                                .checked
                                                                                        )
                                                                                    }
                                                                                    value={
                                                                                        file.id
                                                                                    }
                                                                                    checked={
                                                                                        isFileExist
                                                                                    }
                                                                                />
                                                                                <label
                                                                                    className="custom-control-label"
                                                                                    htmlFor={`variation-${variation.key}-file-${file.id}`}
                                                                                >
                                                                                    {
                                                                                        file.name
                                                                                    }
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                    )
                                                                }
                                                            )}
                                                        </ul>

                                                        <div className="border rounded bg-light text-center p-3 mt-3">
                                                            <Button
                                                                type="button"
                                                                className="btn btn-sm btn-light border upload-product-file"
                                                                onClick={() =>
                                                                    addProductFile(
                                                                        variation
                                                                    )
                                                                }
                                                            >
                                                                {__(
                                                                    'Upload more file',
                                                                    'smartpay'
                                                                )}
                                                            </Button>
                                                        </div>
                                                    </div>
                                                )}

                                                {!product.files.length && (
                                                    <div className="form-group no-variation-file-box">
                                                        <div className="border rounded text-center p-5">
                                                            <Feather.Package
                                                                size={42}
                                                            />
                                                            <h3 className="text-muted">
                                                                {__(
                                                                    'Associate files with this variant',
                                                                    'smartpay'
                                                                )}
                                                            </h3>
                                                            <Button
                                                                type="button"
                                                                className="btn btn-light border shadow-sm select-variation-files"
                                                                onClick={() =>
                                                                    addProductFile(
                                                                        variation
                                                                    )
                                                                }
                                                            >
                                                                {__(
                                                                    'Select files',
                                                                    'smartpay'
                                                                )}
                                                            </Button>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )
                                })}
                                <div className="card-footer bg-white p3 mt-3">
                                    <Button
                                        type="button"
                                        className="btn btn-secondary add-variation-option"
                                        onClick={addNewVariation}
                                    >
                                        {__('Add Option', 'smartpay')}
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                </Tab>
                <Tab
                    eventKey="options"
                    className="text-decoration-none"
                    title={__('Options', 'smartpay')}
                >
                    <OptionComponent
                        product={product}
                        setProductData={setProductData}
                    />
                </Tab>
            </Tabs>
        </Form>
    )
}
