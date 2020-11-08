import { __ } from '@wordpress/i18n'

export const CreateProduct = () => {
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <div className="container">
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <button
                                type="button"
                                className="btn btn-primary px-3"
                            >
                                {__('Publish', 'smartpay')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div className="container">
                <div className="mt-3"></div>
            </div>
        </>
    )
}
