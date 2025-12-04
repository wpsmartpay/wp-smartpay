import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Save, Update } from '@/http/coupon'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { format, set } from 'date-fns'
import { CalendarIcon } from 'lucide-react'
import Swal from 'sweetalert2/dist/sweetalert2.js'
const { useSelect, dispatch } = wp.data

const initialState = {
    title: '',
    description: '',
    discount_type: 'fixed',
    discount_amount: '',
    expiry_date: '',
}

export const CouponDialog = ({ couponId, open, onOpenChange }) => {
    const [coupon, setCoupon] = useState(initialState)
    const [errors, setErrors] = useState({})
    const [isSaving, setIsSaving] = useState(false)
    const [selectedDate, setSelectedDate] = useState(null)

    const isEditMode = !!couponId

    const couponData = useSelect(
        (select) => couponId ? select('smartpay/coupons').getCoupon(couponId) : null,
        [couponId]
    )

    useEffect(() => {
        if (couponData && isEditMode) {
            setCoupon(couponData)
            if (couponData.expiry_date) {
                setSelectedDate(new Date(couponData.expiry_date))
            }
        } else if (!isEditMode) {
            setCoupon(initialState)
            setSelectedDate(null)
            setErrors({})
        }
    }, [couponData, couponId, isEditMode])

    const validateForm = () => {
        const newErrors = {}

        // Title validation
        if (!coupon.title || coupon.title.trim() === '') {
            newErrors.title = __('Coupon code is required', 'smartpay')
        } else if (coupon.title.length < 3) {
            newErrors.title = __('Coupon code must be at least 3 characters', 'smartpay')
        }

        // Discount amount validation
        if (!coupon.discount_amount || coupon.discount_amount.toString().trim() === '') {
            newErrors.discount_amount = __('Discount amount is required', 'smartpay')
        } else if (isNaN(coupon.discount_amount) || parseFloat(coupon.discount_amount) <= 0) {
            newErrors.discount_amount = __('Discount amount must be a positive number', 'smartpay')
        } else if (coupon.discount_type === 'percent' && parseFloat(coupon.discount_amount) > 100) {
            newErrors.discount_amount = __('Percentage discount cannot exceed 100%', 'smartpay')
        }

        setErrors(newErrors)
        return Object.keys(newErrors).length === 0
    }

    const handleSave = async () => {
        setErrors({})

        if (!validateForm()) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: __('Please fix the errors below', 'smartpay'),
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                showClass: {
                    popup: 'swal2-noanimation',
                },
                hideClass: {
                    popup: '',
                },
            })
            return
        }

        setIsSaving(true)

        try {
            const couponPayload = {
                ...coupon,
                expiry_date: selectedDate ? format( set(selectedDate, {
        			hours: 23,
        			minutes: 59,
        			seconds: 59,
    			}), 'yyyy-MM-dd HH:mm:ss'
    			) : ''
            }

            let response
            if (isEditMode) {
                response = await Update(couponId, JSON.stringify(couponPayload))
                dispatch('smartpay/coupons').updateCoupon(response.coupon)
            } else {
                response = await Save(JSON.stringify(couponPayload))
                dispatch('smartpay/coupons').setCoupon(response.coupon)
            }

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
            onOpenChange(false)
        } catch (error) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: error.message || __('Failed to save coupon', 'smartpay'),
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
            })
        } finally {
            setIsSaving(false)
			setCoupon(initialState)
			setSelectedDate(null)
        }
    }

    const handleChange = (field, value) => {
        setCoupon(prev => ({ ...prev, [field]: value }))

        // Clear error for this field
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: undefined }))
        }
    }

    const handleDialogClose = (open) => {
		onOpenChange(open)
        if (!open) {
            setCoupon(initialState)
            setSelectedDate(null)
            setErrors({})
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleDialogClose}>
            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader className="border-b pb-2.5! mb-1!">
                    <DialogTitle className="flex items-center justify-between m-0! text-2xl!">
						{isEditMode
							? __('Edit Coupon', 'smartpay')
							: __('Create Coupon', 'smartpay')
						}
                    </DialogTitle>
					<DialogDescription className="m-0!">
						{isEditMode
							? __('Make changes to the coupon.', 'smartpay')
							: __('Create a new coupon.', 'smartpay')
						}
					</DialogDescription>
                </DialogHeader>

                <div className="space-y-4 pb-4">
					{/* Coupon Code & Expiry Date */}
					<div className="grid grid-cols-2 gap-4">
						<div className="space-y-2">
							<Label htmlFor="title">
								{__('Coupon Code', 'smartpay')} <span className="text-red-500">*</span>
							</Label>
							<Input
								id="title"
								placeholder={__('Enter coupon code here', 'smartpay')}
								value={coupon.title || ''}
								onChange={(e) => handleChange('title', e.target.value)}
								className={errors.title ? 'border-red-500' : ''}
							/>
							{errors.title && (
								<p className="text-sm mb-0! mt-1! text-red-500">{errors.title}</p>
							)}
						</div>

						<div className="space-y-2">
							<Label htmlFor="expiry_date">
								{__('Expiry Date', 'smartpay')}
							</Label>
							<DropdownMenu>
								<DropdownMenuTrigger className="w-full">
									<Button
										variant="outline"
										className="w-full justify-start text-left font-normal"
									>
										<CalendarIcon className="mr-2 h-4 w-4" />
										{selectedDate
											? format(selectedDate, 'PPP')
											: __('Pick a date', 'smartpay')
										}
									</Button>
								</DropdownMenuTrigger>
								<DropdownMenuContent className="w-auto p-0" align="start">
									<Calendar
										mode="single"
										selected={selectedDate}
										onSelect={setSelectedDate}
										className="[--cell-size:--spacing(8)] md:[--cell-size:--spacing(9)]"
									/>
								</DropdownMenuContent>
							</DropdownMenu>
						</div>
					</div>

                    {/* Description */}
                    <div className="space-y-2">
                        <Label htmlFor="description">
                            {__('Description', 'smartpay')}
                        </Label>
                        <Textarea
                            id="description"
                            placeholder={__('Coupon description', 'smartpay')}
                            value={coupon.description || ''}
                            onChange={(e) => handleChange('description', e.target.value)}
                            rows={3}
                        />
                    </div>

                    {/* Discount Type and Amount */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2 w-full">
                            <Label htmlFor="discount_type">
                                {__('Discount Type', 'smartpay')} <span className="text-red-500">*</span>
                            </Label>
                            <Select
                                value={coupon.discount_type || 'fixed'}
                                onValueChange={(value) => handleChange('discount_type', value)}
                            >
                                <SelectTrigger id="discount_type" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="fixed">
                                        {__('Fixed Amount', 'smartpay')}
                                    </SelectItem>
                                    <SelectItem value="percent">
                                        {__('Percentage Amount', 'smartpay')}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="discount_amount">
                                {__('Discount Amount', 'smartpay')} <span className="text-red-500">*</span>
                            </Label>
                            <Input
                                id="discount_amount"
                                placeholder="0"
                                value={coupon.discount_amount || ''}
                                onChange={(e) => handleChange('discount_amount', e.target.value)}
                                className={errors.discount_amount ? 'border-red-500' : ''}
                            />
                            {errors.discount_amount && (
                                <p className="text-sm mb-0! mt-1! text-red-500">{errors.discount_amount}</p>
                            )}
                        </div>
                    </div>
                </div>
				<DialogFooter>
					<DialogClose asChild>
						<Button variant="outline">Cancel</Button>
					</DialogClose>
					<Button
						onClick={handleSave}
						disabled={isSaving}
					>
						{isSaving
							? (isEditMode ? __('Saving...', 'smartpay') : __('Adding...', 'smartpay'))
							: (isEditMode ? __('Save Changes', 'smartpay') : __('Create Coupon', 'smartpay'))
						}
					</Button>
				</DialogFooter>
            </DialogContent>
        </Dialog>
    )
}
