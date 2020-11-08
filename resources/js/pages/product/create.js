import { __ } from '@wordpress/i18n'
import { Container, Form, Button } from 'react-bootstrap'

export const CreateProduct = () => {
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
                                className="btn btn-primary px-3"
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
                        <Form.Control type="text" placeholder="Product title" />
                    </Form.Group>
                    <div id="description">
                        {wp.editor.initialize('description', {
                            tinymce: {},
                        })}
                    </div>
                </Form>

                <div id="featured_image_container" className="my-3">
                    <div className="border rounded bg-light text-center p-5 select-image-box d-flex flex-column align-items-center">
                        <div className="no-image">
                            <i data-feather="image" width="40" height="40"></i>
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

                <Nav fill variant="tabs" defaultActiveKey="home">
                    <Nav.Item>
                        <Nav.Link href="home">
                            {__('Files', 'smartpay')}
                        </Nav.Link>
                    </Nav.Item>
                    <Nav.Item>
                        <Nav.Link href="profile">
                            {__('Pricing', 'smartpay')}
                        </Nav.Link>
                    </Nav.Item>
                </Nav>

                <ul
                    className="nav nav-tabs  nav-fill"
                    id="myTab"
                    role="tablist"
                >
                    <li className="nav-item" role="presentation">
                        <a
                            className="nav-link active"
                            id="home-tab"
                            data-toggle="tab"
                            href="#home"
                            role="tab"
                            aria-controls="home"
                            aria-selected="true"
                        >
                            Files
                        </a>
                    </li>
                    <li className="nav-item" role="presentation">
                        <a
                            className="nav-link"
                            id="profile-tab"
                            data-toggle="tab"
                            href="#profile"
                            role="tab"
                            aria-controls="profile"
                            aria-selected="false"
                        >
                            Pricing
                        </a>
                    </li>
                </ul>
                <div className="tab-content" id="myTabContent">
                    <div
                        className="tab-pane fade show active"
                        id="home"
                        role="tabpanel"
                        aria-labelledby="home-tab"
                    >
                        // TODO: If has file
                        <div className="product-files-secion">
                            <ul
                                className="list-group product-files"
                                id="product-files"
                            >
                                //TODO: Foreach
                                <li className="list-group-item list-group-item-action mb-0 files-item">
                                    <div className="d-flex">
                                        <div className="file-type">
                                            <img
                                                src="#"
                                                alt="#"
                                                width="28"
                                                height="28"
                                            />
                                        </div>
                                        <div className="d-flex justify-content-between w-100">
                                            <div className="d-flex flex-column ml-3">
                                                <h5 className="file-name m-0">
                                                    File
                                                </h5>
                                                <h6 className="file-size text-muted m-0">
                                                    size
                                                </h6>
                                            </div>
                                            <div className="">
                                                <button
                                                    type="button"
                                                    className="btn btn-light btn-sm pb-0 border remove-file"
                                                >
                                                    <i data-feather="trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <div className="border rounded bg-light text-center p-3 mt-3">
                                <button
                                    type="button"
                                    className="btn btn-secondary upload-product-file"
                                >
                                    Upload your files
                                </button>
                            </div>
                        </div>
                        <div className="my-3">
                            // TODO: If no file
                            <div className="border rounded bg-light text-center p-5 no-product-file-box">
                                <i
                                    data-feather="hard-drive"
                                    width="42"
                                    height="42"
                                ></i>
                                <h3 className="text-muted">
                                    Upload or select files for this product
                                </h3>
                                <button
                                    type="button"
                                    className="btn btn-light border shadow-sm upload-product-file"
                                >
                                    Upload files
                                </button>
                            </div>
                        </div>
                    </div>
                    <div className="tab-pane fade" id="profile" role="tabpanel">
                        <div className="form-row">
                            <div className="col-6">
                                <div className="form-group">
                                    <label
                                        htmlFor="base_price"
                                        className="text-muted my-2 d-block"
                                    >
                                        <strong>Base price</strong>
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="base_price"
                                        name="base_price"
                                    />
                                </div>
                            </div>
                            <div className="col-6">
                                <div className="form-group">
                                    <label
                                        htmlFor="sale_price"
                                        className="text-muted my-2 d-block"
                                    >
                                        <strong>Sales price</strong>
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="sale_price"
                                        name="sale_price"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="smartpay-variations">
                            // TODO: If has variation
                            <div className="border rounded bg-light text-center p-5 no-variations-box">
                                <i
                                    data-feather="layers"
                                    width="42"
                                    height="42"
                                ></i>
                                <h3>Offer variations of this product</h3>
                                <p className="text-muted">
                                    Sweeten the deal for your customers with
                                    different options for format, version, etc
                                </p>
                                <button
                                    type="button"
                                    className="btn btn-light border shadow-sm add-variation"
                                >
                                    Add Variations
                                </button>
                            </div>
                            <div className="card p-0 variations-secion">
                                <div className="card-header bg-white p-0">
                                    <div className="d-flex px-3 py-2">
                                        <h3 className="m-0 pt-1 d-flex">
                                            Variations
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
                                    // TODO: variations loop
                                    <div className="variation-option">
                                        <div className="variation-option__header p-3">
                                            <div className="form-row">
                                                <div className="col-11">
                                                    <div className="form-group m-0">
                                                        <label
                                                            htmlFor=""
                                                            className="text-muted my-2 d-block"
                                                        >
                                                            <strong>
                                                                Option name
                                                            </strong>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            className="form-control"
                                                            id=""
                                                            name=""
                                                            placeholder="Option name"
                                                            onChange={''}
                                                            value=""
                                                        />
                                                    </div>
                                                </div>
                                                <div className="col d-flex align-items-center">
                                                    <div className="mt-4">
                                                        <button
                                                            type="button"
                                                            className="btn btn-light btn-sm border shadow-sm pb-0 ml-2 remove-variation-option"
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
                                                            htmlFor="#"
                                                            className=" text-muted my-2 d-block"
                                                        >
                                                            <strong>
                                                                Base price
                                                            </strong>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            className="form-control"
                                                            id="#"
                                                            name=" base_price"
                                                            onChange={''}
                                                            value="#"
                                                        />
                                                    </div>
                                                </div>
                                                <div className="col-6">
                                                    <div className="form-group">
                                                        <label
                                                            htmlFor="#"
                                                            className="text-muted my-2 d-block"
                                                        >
                                                            <strong>
                                                                Sales price
                                                            </strong>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            className="form-control"
                                                            id="#"
                                                            name="sale_price"
                                                            onChange={''}
                                                            value="#"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="variation-option-body bg-light p-3">
                                            <div className="form-group">
                                                <label
                                                    htmlFor="#"
                                                    className="text-muted my-2 d-block"
                                                >
                                                    <strong>Description</strong>
                                                </label>
                                                <textarea
                                                    className="form-control"
                                                    id="#"
                                                    name="#"
                                                    rows="3"
                                                ></textarea>
                                                <small className="form-text text-muted">
                                                    Do not write HTML code here.
                                                </small>
                                            </div>

                                            <label className="text-muted my-2 d-block">
                                                <strong>Files</strong>
                                            </label>
                                            <div className="form-group no-variation-file-box">
                                                <div className="border rounded text-center p-5">
                                                    <i
                                                        data-feather="package"
                                                        width="42"
                                                        height="42"
                                                    ></i>
                                                    <h3 className="text-muted">
                                                        Associate files with
                                                        this variant
                                                    </h3>
                                                    <button
                                                        type="button"
                                                        className="btn btn-light border shadow-sm select-variation-files"
                                                    >
                                                        Select files
                                                    </button>
                                                </div>
                                            </div>
                                            <ul className="list-group variation-files">
                                                // TODO: foreach - variations
                                                file
                                                <li className="list-group-item m-0 d-flex justify-content-between files-item">
                                                    <div className="custom-checkbox custom-checkbox-round">
                                                        <input
                                                            type="checkbox"
                                                            className="custom-control-input variation-file"
                                                            id="#"
                                                            name="#"
                                                            onChange={''}
                                                            value="1"
                                                            checked
                                                        />
                                                        <label
                                                            className="custom-control-label"
                                                            htmlFor="#"
                                                        >
                                                            File name
                                                        </label>
                                                    </div>
                                                </li>
                                            </ul>

                                            <div className="border rounded bg-light text-center p-3 mt-3">
                                                <button
                                                    type="button"
                                                    className="btn btn-sm btn-light border upload-product-file"
                                                >
                                                    Upload more file
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="card-footer bg-white p3 mt-3">
                                <button className="btn btn-secondary add-variation-option">
                                    Add option
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Container>
        </>
    )
}
