import { DataTable } from '@/components/data-table'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'
import { Container } from 'react-bootstrap'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteCoupon, GetCoupons } from '../../http/coupon'
import { createColumns } from './columns'
const { useEffect, useState, useCallback } = wp.element
const { dispatch } = wp.data

export const CouponList = () => {
	const [data, setData] = useState([])
	const [isLoading, setIsLoading] = useState(false)
	const [searchQuery, setSearchQuery] = useState('')
	const [paymentType, setPaymentType] = useState('')
	const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 10,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0
	})

	// Fetch coupons from API
	const fetchCoupons = useCallback(async (page = 1, perPage = 10, search = '', type = '') => {
		setIsLoading(true)

		try {
			const result = await GetCoupons({ page, perPage, search, type });

			// Extract data and pagination info
			const { data: couponData = [], ...paginationData } = result;

			setData(couponData)
			setPagination({
				current_page: paginationData.current_page,
				per_page: paginationData.per_page,
				last_page: paginationData.last_page,
				total: paginationData.total,
				from: paginationData.from,
				to: paginationData.to
			})
		} catch (error) {
			Swal.fire({
				icon: 'error',
				title: __('Error', 'smartpay'),
				text: __('Failed to load payments', 'smartpay'),
			})
		} finally {
			setIsLoading(false)
		}
	}, []);

	useEffect(() => {
		fetchCoupons(1, pagination.per_page, searchQuery, paymentType)
	}, [fetchCoupons, searchQuery, paymentType, pagination.per_page])

	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchCoupons(page, per_page, searchQuery, paymentType)
	}, [fetchCoupons, searchQuery, paymentType])

    const deleteCoupon = (couponId) => {
        Swal.fire({
            title: __('Are you sure?', 'smartpay'),
            text: __("You won't be able to revert this!", 'smartpay'),
            icon: 'warning',
            confirmButtonText: __('Yes', 'smartpay'),
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                DeleteCoupon(couponId).then((response) => {
                    dispatch('smartpay/coupons').deleteCoupon(couponId)
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
        })
    }

	const handleSearchChange = (search) => {
		setSearchQuery(search)
	}

	const handleTypeFilter = (type) => {
		if (type === 'all') {
			type = '';
		}
		setPaymentType(type);
	}

	// Create columns with deleteCoupon function
	const columns = createColumns(deleteCoupon);

    return (
        <>
			<div className="text-black bg-white border-bottom shadow-xs">
				<Container>
					<div className="d-flex align-items-center justify-content-between py-3">
						<div className='-mt-1.5'>
							<h2 className="text-slate-700! mb-1! mt-0! text-2xl! font-bold!">
								{__('Coupons', 'smartpay')}
							</h2>
							<p className='text-slate-500 font-medium text-sm! m-0'>{__('Manage your coupons here', 'smartpay')}</p>
						</div>
						<div className=''>
							<img className='w-full h-7' src={smartpay.logo} alt="SmartPay Logo"/>
						</div>
					</div>
				</Container>
			</div>

			<Container className="mt-4">
				<div className="bg-white p-4 rounded-lg shadow-md">
					<DataTable
						columns={columns}
						data={data}
						pagination={pagination}
						onPaginationChange={handlePaginationChange}
						onSearchChange={handleSearchChange}
						isLoading={isLoading}
						searchPlaceholder='Search by Coupon Code'
						enableFilters={true}
						filters={[
							<Select key="type-filter" onValueChange={handleTypeFilter}>
								<SelectTrigger className="w-[180px]">
									<SelectValue placeholder="Filter by Type" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="all">All</SelectItem>
									<SelectItem value="fixed">Fixed</SelectItem>
									<SelectItem value="percent">Percent</SelectItem>
								</SelectContent>
							</Select>
						]}
						isJustifyBetween={false}
						enableActions={true}
						actions={[
							<Link
                                role="button"
                                className="text-decoration-none"
                                to="/coupons/create"
                            >
                                <Button
									variant="default"
									title={__('Create', 'smartpay')}
								>
									<Plus className="w-4 h-4 text-white" />
									<span>{__('Add Coupon', 'smartpay')}</span>
								</Button>
                            </Link>
						]}
					/>
				</div>
			</Container>
        </>
    )
}
