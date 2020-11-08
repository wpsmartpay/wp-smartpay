import { __ } from '@wordpress/i18n'
import { useReducer } from '@wordpress/element'

import { Container, Tabs, Tab, Form, Button } from 'react-bootstrap'

const formReducer = (state, data) => {
    return {
        ...state,
        ...data,
    }
}

export const CreateProduct = () => {
    const [product, setProductData] = useReducer(formReducer, () => {
        return {
            title: '',
            description: '',
        }
    })

    const _setProductData = e => {
        setProductData({ [e.target.name]: e.target.value })
    }

    const removeFile = (file, isVariation = false) => {
        console.log(file)
    }

    const addNewVariation = () => {
        // TODO
        console.log('Add new variation')
    }

    const removeVariation = variation => {
        // TODO
        console.log('remove variation')
    }

    const createProduct = data => {
        console.log(tinyMCE.activeEditor.getContent())
    }
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-sm btn-primary px-3"
                                onClick={createProduct}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            <Container>
                <Form className="my-3">
                    <Form.Group controlId="title">
                        <Form.Control
                            type="text"
                            name="title"
                            value={product?.title || ''}
                            placeholder="Product title"
                            onChange={_setProductData}
                        />
                    </Form.Group>
                    <div id="description">
                        {wp.editor.initialize('description', {
                            tinymce: true,
                        })}
                    </div>
                    <div className="my-3">
                        <div className="border rounded bg-light text-center p-5 select-image-box d-flex flex-column align-items-center">
                            <div className="no-image">
                                <i
                                    data-feather="image"
                                    width="40"
                                    height="40"
                                ></i>
                                <h3 className="mt-1">
                                    {__('Cover Image', 'smartpay')}
                                </h3>
                                <p className="text-muted">
                                    {__(
                                        'Select a featured image for this product',
                                        'smartpay'
                                    )}
                                </p>
                            </div>
                            <div className="mb-3 preview text-center d-none">
                                <div>
                                    <img src="#" />
                                </div>
                            </div>
                            <Button
                                type="button"
                                onClick={createProduct}
                                className="btn btn-light border px-3 select-image"
                            >
                                {__('Choose File', 'smartpay')}
                            </Button>
                        </div>
                    </div>

                    <Tabs fill defaultActiveKey="files">
                        <Tab
                            eventKey="files"
                            className="text-decoration-none"
                            title={__('Files', 'smartpay')}
                        >
                            <div className="product-files-secion">
                                {product?.files?.length ? (
                                    <>
                                        <ul
                                            className="list-group product-files"
                                            id="product-files"
                                        >
                                            {product?.files?.map(file => {
                                                return (
                                                    <li className="list-group-item list-group-item-action mb-0 files-item">
                                                        <div className="d-flex">
                                                            <div className="file-type">
                                                                <img
                                                                    src={
                                                                        file.thump ||
                                                                        '#'
                                                                    }
                                                                    alt={
                                                                        file.name ||
                                                                        ''
                                                                    }
                                                                    width="28"
                                                                    height="28"
                                                                />
                                                            </div>
                                                            <div className="d-flex justify-content-between w-100">
                                                                <div className="d-flex flex-column ml-3">
                                                                    <h5
                                                                        className="file-name m-0"
                                                                        dangerouslySetInnerHTML={{
                                                                            __html:
                                                                                file.name,
                                                                        }}
                                                                    ></h5>
                                                                    <p
                                                                        className="file-size text-muted m-0"
                                                                        dangerouslySetInnerHTML={{
                                                                            __html:
                                                                                file.size,
                                                                        }}
                                                                    ></p>
                                                                </div>
                                                                <div className="">
                                                                    <Button
                                                                        type="button"
                                                                        className="btn btn-light btn-sm pb-0 border remove-file"
                                                                        onClick={removeFile(
                                                                            file
                                                                        )}
                                                                    >
                                                                        <i data-feather="trash"></i>
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
                                                onClick={() => {
                                                    console.log(
                                                        // TODO
                                                        'Open media upload modal'
                                                    )
                                                }}
                                            >
                                                {__(
                                                    'Upload your files',
                                                    'smartpay'
                                                )}
                                            </button>
                                        </div>
                                    </>
                                ) : (
                                        <div className="my-3">
                                            <div className="border rounded bg-light text-center p-5 no-product-file-box">
                                                <i
                                                    data-feather="hard-drive"
                                                    width="42"
                                                    height="42"
                                                ></i>
                                                <h3 className="text-muted">
                                                    {__(
                                                        'Upload or select files for this product',
                                                        'smartpay'
                                                    )}
                                                </h3>
                                                <Button
                                                    type="button"
                                                    className="btn btn-light border shadow-sm upload-product-file"
                                                    onClick={() => {
                                                        console.log(
                                                            // TODO
                                                            'Open media upload modal'
                                                        )
                                                    }}
                                                >
                                                    {__('Upload files', 'smartpay')}
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                            </div>
                        </Tab>
                        <Tab
                            eventKey="pricing"
                            className="text-decoration-none"
                            title={__('Pricing', 'smartpay')}
                        >
                            <div className="form-row">
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
                                            value={product?.base_price || ''}
                                            placeholder={__('Base price', 'smartpay')}
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
                                            placeholder={__('Sales price', 'smartpay')}
                                            onChange={_setProductData}
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="smartpay-variations">
                                {product?.variation?.length ? (
                                    <div className="card p-0 variations-secion">
                                        <div className="card-header bg-white p-0">
                                            <div className="d-flex px-3 py-2">
                                                <h3 className="m-0 pt-1 d-flex">
                                                    {__(
                                                        'Variations',
                                                        'smartpay'
                                                    )}
                                                </h3>
                                                <button
                                                    type="button"
                                                    className="btn btn-light border btn-sm my-1 ml-auto pb-0 shadow-sm remove-variation"
                                                >
                                                    <i
                                                        data-feather="trash"
                                                        width="16"
                                                        height="16"
                                                    ></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div className="card-body p-0 variations">
                                            {product?.variations?.map(
                                                variation => {
                                                    return (
                                                        <div className="variation-option">
                                                            <div className="variation-option__header p-3">
                                                                <div className="form-row">
                                                                    <div className="col-11">
                                                                        <div className="form-group m-0">
                                                                            <label
                                                                                htmlFor={`variation-${variation.id}`}
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
                                                                                id={`variation-${variation.id}`}
                                                                                name="name"
                                                                                value={
                                                                                    variation.name
                                                                                }
                                                                                placeholder="Option name"
                                                                                onChange={e =>
                                                                                    setVariationData(
                                                                                        variation,
                                                                                        e
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
                                                                                onClick={removeVariation(
                                                                                    variation
                                                                                )}
                                                                            >
                                                                                <i
                                                                                    data-feather="trash"
                                                                                    width="20"
                                                                                    height="20"
                                                                                ></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div className="form-row mt-4">
                                                                    <div className="col-6">
                                                                        <div className="form-group">
                                                                            <label
                                                                                htmlFor={`variation-${variation.id}-base_price`}
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
                                                                                id={`variation-${variation.id}-base_price`}
                                                                                value={
                                                                                    variation.base_price
                                                                                }
                                                                                placeholder="Option name"
                                                                                onChange={e =>
                                                                                    setVariationData(
                                                                                        variation,
                                                                                        e
                                                                                    )
                                                                                }
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div className="col-6">
                                                                        <div className="form-group">
                                                                            <label
                                                                                htmlFor={`variation-${variation.id}-sale_price`}
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
                                                                                id={`variation-${variation.id}-sale_price`}
                                                                                value={
                                                                                    variation.sale_price
                                                                                }
                                                                                onChange={event =>
                                                                                    setVariationData(
                                                                                        variation,
                                                                                        event
                                                                                    )
                                                                                }
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div className="variation-option-body bg-light p-3">
                                                                <div className="form-group">
                                                                    <label
                                                                        htmlFor={`variation-${variation.id}-description`}
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
                                                                        id={`variation-${variation.id}-description`}
                                                                        value={
                                                                            variation.description
                                                                        }
                                                                        onChange={event =>
                                                                            setVariationData(
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

                                                                <label className="text-muted my-2 d-block">
                                                                    <strong>
                                                                        {__(
                                                                            'Files',
                                                                            'smartpay'
                                                                        )}
                                                                    </strong>
                                                                </label>
                                                                {variation
                                                                    ?.files
                                                                    ?.length ? (
                                                                        <>
                                                                            <ul className="list-group variation-files">
                                                                                {variation?.files?.map(
                                                                                    file => {
                                                                                        return (
                                                                                            <li className="list-group-item m-0 d-flex justify-content-between files-item">
                                                                                                <div className="custom-checkbox custom-checkbox-round">
                                                                                                    <input
                                                                                                        type="checkbox"
                                                                                                        className="custom-control-input variation-file"
                                                                                                        id={`variation-file-${file.id}`}
                                                                                                        name="file"
                                                                                                        onChange={() => {
                                                                                                            console.log(
                                                                                                                'Toggle variation file'
                                                                                                            )
                                                                                                        }}
                                                                                                        value={
                                                                                                            file.id
                                                                                                        }
                                                                                                        checked //FIXME
                                                                                                    />
                                                                                                    <label
                                                                                                        className="custom-control-label"
                                                                                                        htmlFor={`variation-file-${file.id}`}
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
                                                                                    onClick={() => {
                                                                                        //FIXME
                                                                                        console.log(
                                                                                            'Add new variation file'
                                                                                        )
                                                                                    }}
                                                                                >
                                                                                    {__(
                                                                                        'Upload more file',
                                                                                        'smartpay'
                                                                                    )}
                                                                                </Button>
                                                                            </div>
                                                                        </>
                                                                    ) : (
                                                                        <div className="form-group no-variation-file-box">
                                                                            <div className="border rounded text-center p-5">
                                                                                <i
                                                                                    data-feather="package"
                                                                                    width="42"
                                                                                    height="42"
                                                                                ></i>
                                                                                <h3 className="text-muted">
                                                                                    {__(
                                                                                        'Associate files with this variant',
                                                                                        'smartpay'
                                                                                    )}
                                                                                </h3>
                                                                                <Button
                                                                                    type="button"
                                                                                    className="btn btn-light border shadow-sm select-variation-files"
                                                                                    onClick={() => {
                                                                                        //FIXME
                                                                                        console.log(
                                                                                            'Add new variation file'
                                                                                        )
                                                                                    }}
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
                                                }
                                            )}
                                        </div>
                                        <div className="card-footer bg-white p3 mt-3">
                                            <button
                                                className="btn btn-secondary add-variation-option"
                                                onClick={addNewVariation}
                                            >
                                                {__('Add option', 'smartpay')}
                                            </button>
                                        </div>
                                    </div>
                                ) : (
                                        <div className="border rounded bg-light text-center p-5 no-variations-box">
                                            <i
                                                data-feather="layers"
                                                width="42"
                                                height="42"
                                            ></i>
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
                                            <button
                                                type="button"
                                                className="btn btn-light border shadow-sm add-variation"
                                            >
                                                {__('Add Variations', 'smartpay')}
                                            </button>
                                        </div>
                                    )}
                            </div>
                        </Tab>
                    </Tabs>
                </Form>
            </Container>
        </>
    )
}
