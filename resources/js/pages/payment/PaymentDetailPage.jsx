import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { GetPayment, Update } from '@/http/payment'
import { ActivityLogSection } from './ActivityLogSection'
import Swal from 'sweetalert2/dist/sweetalert2.js'

const {
	Button,
	Card,
	CardContent,
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} = window.WPSmartPayUI

const StatusBadge = ( { status } ) => {
	const isCompleted = status === 'Completed'
	return (
		<span
			className={
				'px-2 py-1 text-white rounded text-sm ' +
				( isCompleted ? 'bg-green-600' : 'bg-red-600' )
			}
		>
			{ status }
		</span>
	)
}

const InfoRow = ( { label, children } ) => (
	<p className="mb-2">
		<strong>{ label }: </strong>
		{ children }
	</p>
)

export const PaymentDetailPage = () => {
	const { paymentId } = useParams()
	const navigate = useNavigate()

	const [ payment, setPayment ] = useState( null )
	const [ paymentStatus, setPaymentStatus ] = useState( 'pending' )
	const [ loading, setLoading ] = useState( true )
	const [ saving, setSaving ] = useState( false )

	useEffect( () => {
		if ( ! paymentId ) return
		setLoading( true )
		GetPayment( paymentId )
			.then( ( data ) => {
				if ( data ) {
					setPayment( data )
					setPaymentStatus( data.status?.toLowerCase() || 'pending' )
				}
			} )
			.catch( () => {
				Swal.fire( {
					icon: 'error',
					title: __( 'Error', 'smartpay' ),
					text: __( 'Failed to load payment.', 'smartpay' ),
				} )
			} )
			.finally( () => setLoading( false ) )
	}, [ paymentId ] )

	const handleSave = async () => {
		setSaving( true )
		try {
			const response = await Update(
				paymentId,
				JSON.stringify( { ...payment, status: paymentStatus } )
			)
			setPayment( response.payment )
			Swal.fire( {
				toast: true,
				icon: 'success',
				title: __( response.message || 'Updated', 'smartpay' ),
				position: 'top-end',
				showConfirmButton: false,
				timer: 2000,
				showClass: { popup: 'swal2-noanimation' },
				hideClass: { popup: '' },
			} )
		} catch {
			Swal.fire( {
				icon: 'error',
				title: __( 'Error', 'smartpay' ),
				text: __( 'Failed to update payment.', 'smartpay' ),
			} )
		} finally {
			setSaving( false )
		}
	}

	if ( loading ) {
		return (
			<div className="p-4 max-w-4xl mx-auto">
				<p className="text-gray-500">{ __( 'Loading…', 'smartpay' ) }</p>
			</div>
		)
	}

	if ( ! payment ) {
		return (
			<div className="p-4 max-w-4xl mx-auto">
				<p className="text-gray-500">{ __( 'Payment not found.', 'smartpay' ) }</p>
				<Button variant="outline" size="sm" onClick={ () => navigate( '/payments' ) }>
					{ __( '← Back to Payments', 'smartpay' ) }
				</Button>
			</div>
		)
	}

	const isProductPurchase = payment.type === 'Product Purchase'
	const isFormPayment = payment.type === 'Form Payment'

	// Build default sections array; plugins can inject via filter.
	const defaultSections = [
		{
			id: 'payment_info',
			component: (
				<Card key="payment_info">
					<CardContent className="pt-6">
						<div className="flex pb-3 border-b justify-between items-center mb-4">
							<div className="flex items-center gap-3">
								<h3 className="text-2xl font-bold m-0">
									{ payment.currency } { payment.amount }
								</h3>
								<StatusBadge status={ payment.status } />
							</div>
							<h3 className="text-primary m-0 text-right">{ payment.type }</h3>
						</div>

						<div className="space-y-2">
							<InfoRow label={ __( 'Date', 'smartpay' ) }>
								{ payment.created_at }
							</InfoRow>
							<InfoRow label={ __( 'Customer', 'smartpay' ) }>
								{ payment.email }
							</InfoRow>
							<InfoRow label={ __( 'Payment Method', 'smartpay' ) }>
								{ payment.gateway }
							</InfoRow>
							{ payment.transaction_id && (
								<InfoRow label={ __( 'Transaction ID', 'smartpay' ) }>
									{ payment.transaction_id }
								</InfoRow>
							) }
							{ payment.subscription_id && (
								<InfoRow label={ __( 'Subscription', 'smartpay' ) }>
									<Link
										to={ `/subscriptions/${ payment.subscription_id }/edit` }
										className="text-blue-600 hover:underline"
									>
										#{ payment.subscription_id }
									</Link>
								</InfoRow>
							) }
						</div>

						{ isProductPurchase && (
							<div className="mt-4 pt-4 border-t">
								<h3 className="text-lg font-semibold mb-3">
									{ __( 'Product Details', 'smartpay' ) }
								</h3>
								<div className="space-y-2">
									<InfoRow label={ __( 'Product', 'smartpay' ) }>
										{ payment.data?.product_edit_url ? (
											<a
												href={ payment.data.product_edit_url }
												className="text-blue-600 hover:underline"
											>
												{ payment.data.product_title || `#${ payment.data.product_id }` }
											</a>
										) : (
											payment.data?.product_title || `#${ payment.data?.product_id }`
										) }
									</InfoRow>
									<InfoRow label={ __( 'Product Price', 'smartpay' ) }>
										{ payment.currency } { payment.data?.product_price }
									</InfoRow>
									<InfoRow label={ __( 'Total Amount', 'smartpay' ) }>
										{ payment.currency } { payment.data?.total_amount }
									</InfoRow>
									<InfoRow label={ __( 'Price Type', 'smartpay' ) }>
										{ payment.data?.billing_type }
									</InfoRow>
								</div>
							</div>
						) }

						{ isFormPayment && (
							<div className="mt-4 pt-4 border-t">
								<h3 className="text-lg font-semibold mb-3">
									{ __( 'Form Payment Details', 'smartpay' ) }
								</h3>
								<div className="space-y-2">
									<InfoRow label={ __( 'Form', 'smartpay' ) }>
										{ payment.data?.form_edit_url ? (
											<Link
												to={ payment.data.form_edit_url.replace( '#', '' ) }
												className="text-blue-600 hover:underline"
											>
												{ payment.data.form_title || `#${ payment.data.form_id }` }
											</Link>
										) : (
											payment.data?.form_title || `#${ payment.data?.form_id }`
										) }
									</InfoRow>
									<InfoRow label={ __( 'Total Amount', 'smartpay' ) }>
										{ payment.data?.total_amount }
									</InfoRow>
									<InfoRow label={ __( 'Price Type', 'smartpay' ) }>
										{ payment.data?.billing_type }
									</InfoRow>
								</div>
							</div>
						) }

						{ payment.customer && (
							<div className="mt-4 pt-4 border-t">
								<h3 className="text-lg font-semibold mb-3">
									{ __( 'Customer Details', 'smartpay' ) }
								</h3>
								<div className="space-y-2">
									<InfoRow label={ __( 'First Name', 'smartpay' ) }>
										{ payment.customer.first_name }
									</InfoRow>
									<InfoRow label={ __( 'Last Name', 'smartpay' ) }>
										{ payment.customer.last_name }
									</InfoRow>
									<InfoRow label={ __( 'Email', 'smartpay' ) }>
										{ payment.customer.email }
									</InfoRow>
								</div>
							</div>
						) }
					</CardContent>
				</Card>
			),
		},
	]

	if ( payment.extra?.form_data ) {
		defaultSections.push( {
			id: 'form_data',
			component: <FormDataSection key="form_data" formData={ payment.extra.form_data } formFields={ payment.extra.form_fields } />,
		} )
	}

	defaultSections.push( {
		id: 'activity_log',
		component: <ActivityLogSection key="activity_log" paymentId={ paymentId } />,
	} )

	const sections = applyFilters( 'smartpay_payment_details_sections', defaultSections, payment )

	return (
		<>
			<div className="p-4 max-w-4xl mx-auto">
				<div className="flex items-center justify-between mb-4">
					<button
						onClick={ () => navigate( '/payments' ) }
						className="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1"
					>
						← { __( 'Back to Payments', 'smartpay' ) }
					</button>

					<div className="flex items-center gap-2">
						<Select
							value={ paymentStatus }
							onValueChange={ setPaymentStatus }
						>
							<SelectTrigger className="w-[150px]">
								<SelectValue placeholder={ __( 'Select Status', 'smartpay' ) } />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="pending">{ __( 'Pending', 'smartpay' ) }</SelectItem>
								<SelectItem value="completed">{ __( 'Completed', 'smartpay' ) }</SelectItem>
								<SelectItem value="refunded">{ __( 'Refunded', 'smartpay' ) }</SelectItem>
								<SelectItem value="failed">{ __( 'Failed', 'smartpay' ) }</SelectItem>
								<SelectItem value="abandoned">{ __( 'Abandoned', 'smartpay' ) }</SelectItem>
								<SelectItem value="revoked">{ __( 'Revoked', 'smartpay' ) }</SelectItem>
								<SelectItem value="processing">{ __( 'Processing', 'smartpay' ) }</SelectItem>
							</SelectContent>
						</Select>
						<Button onClick={ handleSave } disabled={ saving } size="sm">
							{ saving ? __( 'Updating…', 'smartpay' ) : __( 'Update', 'smartpay' ) }
						</Button>
					</div>
				</div>

				<div className="space-y-4">
					{ sections.map( ( section ) => section.component ) }
				</div>
			</div>
		</>
	)
}

const FormDataSection = ( { formData, formFields } ) => {
	const build = ( fields ) => {
		if ( ! Array.isArray( fields ) ) return {}
		let tempFields = {}
		fields.forEach( ( item ) => {
			const data = item[ Object.keys( item )[ 0 ] ]
			if ( data?.attributes ) item = data
			const key = item[ 'attributes' ]?.[ 'name' ]
			if ( ! key ) return
			tempFields[ key ] = item.fields
				? build( item.fields )
				: item.settings?.label
		} )
		return tempFields
	}

	const renderFields = ( labels, data ) => (
		<div key={ Math.random().toString( 36 ).substr( 2, 9 ) }>
			{ Object.keys( labels ).map( ( key ) =>
				typeof labels[ key ] === 'object'
					? renderFields( labels[ key ], data?.[ key ] )
					: data?.[ key ] && (
						<p key={ key } className="mb-2">
							<strong>{ labels[ key ] }: </strong>
							<span>{ data[ key ] }</span>
						</p>
					)
			) }
		</div>
	)

	return (
		<Card>
			<CardContent className="pt-6">
				<h3 className="text-lg font-semibold mb-3">
					{ __( 'Form Data', 'smartpay' ) }
				</h3>
				{ renderFields( build( formFields ), formData ) }
			</CardContent>
		</Card>
	)
}
