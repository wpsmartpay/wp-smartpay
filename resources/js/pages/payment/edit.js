export const EditPayment = () => {
    return (
        <>
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('Edit Coupon', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-primary btn-sm text-decoration-none"
                                onClick={Save}
                            >
                                {__('Save', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
        </>
    )
}
