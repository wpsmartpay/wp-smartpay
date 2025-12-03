import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Update } from '@/http/payment'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import Swal from 'sweetalert2/dist/sweetalert2.js'
const { useSelect, dispatch } = wp.data

export const PaymentDetailsDialog = ({ paymentId, open, onOpenChange }) => {
    const [payment, setPaymentData] = useState({})
    const [paymentStatus, setPaymentStatus] = useState('pending')
    const [isSaving, setIsSaving] = useState(false)

    const paymentData = useSelect(
        (select) => paymentId ? select('smartpay/payments').getPayment(paymentId) : null,
        [paymentId]
    )

    useEffect(() => {
        if (paymentData) {
            setPaymentData(paymentData)
            setPaymentStatus(paymentData?.status)
        }
    }, [paymentData])

    const handleSave = async () => {
        setIsSaving(true)
        try {
            const response = await Update(
                paymentId,
                JSON.stringify({ ...payment, status: paymentStatus })
            )

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

            dispatch('smartpay/payments').updatePayment(response.payment)
            onOpenChange(false)
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: __('Error', 'smartpay'),
                text: __('Failed to update payment', 'smartpay'),
            })
        } finally {
            setIsSaving(false)
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center justify-between">
                        <h3 className='mt-0!'>{__('Payment Details', 'smartpay')}</h3>
                        <div className="flex items-center gap-2">
                            <Select
                                value={paymentStatus?.toLowerCase()}
                                onValueChange={setPaymentStatus}
                            >
                                <SelectTrigger className="w-[150px]">
                                    <SelectValue placeholder={__('Select Status', 'smartpay')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">{__('Pending', 'smartpay')}</SelectItem>
                                    <SelectItem value="completed">{__('Completed', 'smartpay')}</SelectItem>
                                    <SelectItem value="refunded">{__('Refunded', 'smartpay')}</SelectItem>
                                    <SelectItem value="failed">{__('Failed', 'smartpay')}</SelectItem>
                                    <SelectItem value="abandoned">{__('Abandoned', 'smartpay')}</SelectItem>
                                    <SelectItem value="revoked">{__('Revoked', 'smartpay')}</SelectItem>
                                    <SelectItem value="processing">{__('Processing', 'smartpay')}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                onClick={handleSave}
                                disabled={isSaving}
                                size="sm"
                            >
                                {isSaving ? __('Updating...', 'smartpay') : __('Update', 'smartpay')}
                            </Button>
                        </div>
                    </DialogTitle>
                </DialogHeader>

                {payment && (
                    <div className="space-y-4">
                        <Card>
                            <CardContent className="pt-6">
                                <div className="flex pb-3 border-bottom justify-between items-center mb-4">
                                    <div className="flex items-center gap-3">
                                        <h3 className="text-2xl font-bold m-0">
                                            ${payment?.amount || 0}
                                        </h3>
                                        <span
                                            className={
                                                'px-2 py-1 text-white rounded text-sm ' +
                                                (payment.status === 'Completed'
                                                    ? 'bg-green-600'
                                                    : 'bg-red-600')
                                            }
                                        >
                                            {__(payment.status, 'smartpay')}
                                        </span>
                                    </div>
                                    <h3 className="text-primary m-0 text-right">
                                        {payment.type}
                                    </h3>
                                </div>

                                <div className="space-y-2">
                                    <p className="mb-2">
                                        <strong>{__('Date', 'smartpay')}: </strong>
                                        <span>{payment.created_at}</span>
                                    </p>
                                    <p className="mb-2">
                                        <strong>{__('Customer', 'smartpay')}: </strong>
                                        <span>{payment.email}</span>
                                    </p>
                                    <p className="mb-2">
                                        <strong>{__('Payment Method', 'smartpay')}: </strong>
                                        <span>{payment.gateway}</span>
                                    </p>
                                    {payment?.transaction_id && (
                                        <p className="mb-2">
                                            <strong>{__('Transaction ID', 'smartpay')}: </strong>
                                            <span>{payment?.transaction_id || '-'}</span>
                                        </p>
                                    )}
                                </div>

                                {payment.type === 'Product Purchase' && (
                                    <div className="mt-4 pt-4 border-t">
                                        <h3 className="text-lg font-semibold mb-3">
                                            {__('Product Details', 'smartpay')}
                                        </h3>
                                        <div className="space-y-2">
                                            <p className="mb-2">
                                                <strong>{__('Product', 'smartpay')}: </strong>
                                                <span>{`#${payment?.data?.product_id}` || '-'}</span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Product Price', 'smartpay')}: </strong>
                                                <span>
                                                    {`${payment?.currency} ${payment?.data?.product_price}` || '-'}
                                                </span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Total Amount', 'smartpay')}: </strong>
                                                <span>
                                                    {`${payment?.currency} ${payment?.data?.total_amount}` || '-'}
                                                </span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Price Type', 'smartpay')}: </strong>
                                                <span>{payment?.data?.billing_type}</span>
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {payment.type === 'Form Payment' && (
                                    <div className="mt-4 pt-4 border-t">
                                        <h3 className="text-lg font-semibold mb-3">
                                            {__('Form Payment Details', 'smartpay')}
                                        </h3>
                                        <div className="space-y-2">
                                            <p className="mb-2">
                                                <strong>{__('Form', 'smartpay')}: </strong>
                                                <span>#{payment?.data?.form_id}</span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Total Amount', 'smartpay')}: </strong>
                                                <span>{payment?.data?.total_amount}</span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Price Type', 'smartpay')}: </strong>
                                                <span>{payment?.data?.billing_type}</span>
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {payment?.customer && (
                                    <div className="mt-4 pt-4 border-t">
                                        <h3 className="text-lg font-semibold mb-3">
                                            {__('Customer Details', 'smartpay')}
                                        </h3>
                                        <div className="space-y-2">
                                            <p className="mb-2">
                                                <strong>{__('First Name', 'smartpay')}: </strong>
                                                <span>{payment?.customer?.first_name}</span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Last Name', 'smartpay')}: </strong>
                                                <span>{payment?.customer?.last_name}</span>
                                            </p>
                                            <p className="mb-2">
                                                <strong>{__('Email', 'smartpay')}: </strong>
                                                <span>{payment?.customer?.email}</span>
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {payment.extra?.form_data && (
                            <DisplayFormData
                                formData={payment.extra?.form_data}
                                formFields={payment.extra?.form_fields}
                            />
                        )}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    )
}

const DisplayFormData = ({ formData, formFields }) => {
    const build = (fields) => {
        if (!Array.isArray(fields)) {
            return
        }

        let tempFields = {}

        fields.forEach((item) => {
            const data = item[Object.keys(item)[0]]
            if (data.hasOwnProperty('attributes')) {
                item = data
            }

            const key = item['attributes']['name']
            if (item.hasOwnProperty('fields')) {
                tempFields[key] = build(item['fields'])
            } else {
                tempFields[key] = item['settings']['label']
            }
        })

        return tempFields
    }

    const renderFields = (labels, formData) => {
        return (
            <div key={Math.random().toString(36).substr(2, 11)}>
                {Object.keys(labels).map(function (key) {
                    if ('object' === typeof labels[key]) {
                        return renderFields(labels[key], formData[key])
                    } else {
                        return (
                            formData[key] && (
                                <p key={key} className="mb-2">
                                    <strong>{labels[key]}: </strong>
                                    <span>{formData[key]}</span>
                                </p>
                            )
                        )
                    }
                })}
            </div>
        )
    }

    return (
        <Card>
            <CardContent className="pt-6">
                <h3 className="text-lg font-semibold mb-3">
                    {__('Form Data', 'smartpay')}
                </h3>
                {renderFields(build(formFields), formData)}
            </CardContent>
        </Card>
    )
}
