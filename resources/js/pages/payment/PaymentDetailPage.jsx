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
	StatusBadge,
	Header,
} = window.WPSmartPayUI

const InfoField = ( { label, children } ) => (
	<div>
		<dt className="text-xs text-gray-500 uppercase tracking-wide mb-0.5">{ label }</dt>
		<dd className="text-sm font-medium text-gray-800">{ children || '—' }</dd>
	</div>
)

const SectionCard = ( { title, children } ) => (
	<Card>
		<CardContent className="pt-0 pb-5">
			<div className="border-b px-6 py-3 -mx-6 mb-4">
				<h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider m-0">
					{ title }
				</h3>
			</div>
			{ children }
		</CardContent>
	</Card>
)

const SkeletonBlock = ( { className } ) => (
	<div className={ `animate-pulse bg-gray-100 rounded ${ className }` } />
)

const SkeletonLoader = () => (
	<>
		<Header
			title={ __( 'Payment Details', 'smartpay' ) }
			subtitle={ __( 'Loading…', 'smartpay' ) }
		/>
		<div className="p-4 max-w-7xl mx-auto">
			<div className="grid gap-6" style={ { gridTemplateColumns: '1fr 280px' } }>
				<div className="space-y-4">
					<Card>
						<CardContent className="pt-6">
							<div className="flex items-center gap-4 mb-6">
								<SkeletonBlock className="h-9 w-32" />
								<SkeletonBlock className="h-6 w-20" />
							</div>
							<div className="grid grid-cols-4 gap-4">
								{ [ 1, 2, 3, 4 ].map( ( i ) => (
									<div key={ i }>
										<SkeletonBlock className="h-3 w-16 mb-2" />
										<SkeletonBlock className="h-4 w-24" />
									</div>
								) ) }
							</div>
						</CardContent>
					</Card>
					<Card>
						<CardContent className="pt-6 space-y-3">
							<SkeletonBlock className="h-4 w-1/3" />
							<SkeletonBlock className="h-4 w-2/3" />
							<SkeletonBlock className="h-4 w-1/2" />
						</CardContent>
					</Card>
				</div>
				<div className="space-y-4">
					<Card>
						<CardContent className="pt-6 space-y-3">
							<SkeletonBlock className="h-3 w-24 mb-2" />
							<SkeletonBlock className="h-9 w-full" />
							<SkeletonBlock className="h-9 w-full" />
							<SkeletonBlock className="h-9 w-full" />
						</CardContent>
					</Card>
					<Card>
						<CardContent className="pt-6 space-y-4">
							{ [ 1, 2, 3, 4 ].map( ( i ) => (
								<div key={ i }>
									<SkeletonBlock className="h-3 w-16 mb-1" />
									<SkeletonBlock className="h-4 w-24" />
								</div>
							) ) }
						</CardContent>
					</Card>
				</div>
			</div>
		</div>
	</>
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

	if ( loading ) return <SkeletonLoader />

	if ( ! payment ) {
		return (
			<>
				<Header
					title={ __( 'Payment Details', 'smartpay' ) }
					subtitle={ __( 'Payment not found', 'smartpay' ) }
				/>
				<div className="p-4 max-w-7xl mx-auto">
					<Card>
						<CardContent className="py-16 text-center">
							<p className="text-gray-500 mb-4">
								{ __( 'Payment not found.', 'smartpay' ) }
							</p>
							<Button
								variant="outline"
								size="sm"
								onClick={ () => navigate( '/payments' ) }
							>
								{ __( '← Back to Payments', 'smartpay' ) }
							</Button>
						</CardContent>
					</Card>
				</div>
			</>
		)
	}

	const isProductPurchase = payment.type === 'Product Purchase'
	const isFormPayment = payment.type === 'Form Payment'

	let mainSections = []

	mainSections.push( {
		id: 'summary',
		component: (
			<Card key="summary">
				<CardContent className="pt-6">
					<div className="flex items-center justify-between mb-6">
						<div className="flex items-center gap-3">
							<span className="text-3xl font-bold text-gray-900">
								{ payment.currency } { payment.amount }
							</span>
							<StatusBadge status={ payment.status } />
						</div>
						<span className="text-sm font-medium text-gray-400 bg-gray-100 px-3 py-1 rounded">
							{ payment.type }
						</span>
					</div>

					<dl className="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-4 pt-4 border-t">
						<InfoField label={ __( 'Date', 'smartpay' ) }>{ payment.created_at }</InfoField>
						<InfoField label={ __( 'Customer', 'smartpay' ) }>{ payment.email }</InfoField>
						<InfoField label={ __( 'Gateway', 'smartpay' ) }>{ payment.gateway }</InfoField>
						{ payment.transaction_id && (
							<InfoField label={ __( 'Transaction ID', 'smartpay' ) }>
								{ payment.transaction_id }
							</InfoField>
						) }
						{ payment.subscription_id && (
							<InfoField label={ __( 'Subscription', 'smartpay' ) }>
								<a
									href={ `#/subscriptions/${ payment.subscription_id }` }
									className="text-blue-600 hover:underline"
								>
									#{ payment.subscription_id }
								</a>
							</InfoField>
						) }
					</dl>
				</CardContent>
			</Card>
		),
	} )

	if ( isProductPurchase ) {
		mainSections.push( {
			id: 'product_details',
			component: (
				<SectionCard key="product_details" title={ __( 'Product Details', 'smartpay' ) }>
					<dl className="grid grid-cols-2 gap-x-6 gap-y-4">
						<InfoField label={ __( 'Product', 'smartpay' ) }>
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
						</InfoField>
						<InfoField label={ __( 'Price', 'smartpay' ) }>
							{ payment.currency } { payment.data?.product_price }
						</InfoField>
						<InfoField label={ __( 'Total Amount', 'smartpay' ) }>
							{ payment.currency } { payment.data?.total_amount }
						</InfoField>
						<InfoField label={ __( 'Billing Type', 'smartpay' ) }>
							{ payment.data?.billing_type }
						</InfoField>
					</dl>
				</SectionCard>
			),
		} )
	}

	if ( isFormPayment ) {
		mainSections.push( {
			id: 'form_details',
			component: (
				<SectionCard key="form_details" title={ __( 'Form Details', 'smartpay' ) }>
					<dl className="grid grid-cols-2 gap-x-6 gap-y-4">
						<InfoField label={ __( 'Form', 'smartpay' ) }>
							{ payment.data?.form_edit_url ? (
								<div className="flex items-center gap-2">
									<a
										href={ payment.data.form_edit_url }
										className="text-blue-600 hover:underline"
									>
										{ payment.data?.form_title || `#${ payment.data?.form_id }` }
									</a>
									{ payment.data?.form_type && (
										<span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 capitalize">
											{ payment.data.form_type }
										</span>
									) }
								</div>
							) : (
								payment.data?.form_title || `#${ payment.data?.form_id }`
							) }
						</InfoField>
						<InfoField label={ __( 'Total Amount', 'smartpay' ) }>
							{ payment.data?.total_amount }
						</InfoField>
						<InfoField label={ __( 'Billing Type', 'smartpay' ) }>
							{ payment.data?.billing_type }
						</InfoField>
					</dl>
				</SectionCard>
			),
		} )
	}

	if ( payment.customer ) {
		mainSections.push( {
			id: 'customer_details',
			component: (
				<SectionCard key="customer_details" title={ __( 'Customer Details', 'smartpay' ) }>
					<dl className="grid grid-cols-3 gap-x-6 gap-y-4">
						<InfoField label={ __( 'First Name', 'smartpay' ) }>
							{ payment.customer.first_name }
						</InfoField>
						<InfoField label={ __( 'Last Name', 'smartpay' ) }>
							{ payment.customer.last_name }
						</InfoField>
						<InfoField label={ __( 'Email', 'smartpay' ) }>
							{ payment.customer.email }
						</InfoField>
					</dl>
				</SectionCard>
			),
		} )
	}

	if ( payment.extra?.form_data ) {
		mainSections.push( {
			id: 'form_data',
			component: (
				<FormDataSection
					key="form_data"
					formData={ payment.extra.form_data }
					formFields={ payment.extra.form_fields }
				/>
			),
		} )
	}

	mainSections.push( {
		id: 'activity_log',
		component: <ActivityLogSection key="activity_log" paymentId={ paymentId } />,
	} )

	const sections = applyFilters( 'smartpay_payment_details_sections', mainSections, payment )

	return (
		<>
			<Header
				title={ __( 'Payment Details', 'smartpay' ) }
				subtitle={ `#${ payment.id } · ${ payment.email }` }
			/>

			<div className="p-4 max-w-7xl mx-auto">
				<div className="grid gap-6" style={ { gridTemplateColumns: '1fr 280px' } }>
					<div className="space-y-4 min-w-0">
						{ sections.map( ( s ) => s.component ) }
					</div>

					<div className="space-y-4">
						<SectionCard title={ __( 'Actions', 'smartpay' ) }>
							<div className="space-y-3">
								<div>
									<label className="text-xs text-gray-500 uppercase tracking-wide block mb-1.5">
										{ __( 'Payment Status', 'smartpay' ) }
									</label>
									<Select value={ paymentStatus } onValueChange={ setPaymentStatus }>
										<SelectTrigger className="w-full">
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
								</div>
								<Button
									className="w-full"
									onClick={ handleSave }
									disabled={ saving }
								>
									{ saving
										? __( 'Updating…', 'smartpay' )
										: __( 'Update Status', 'smartpay' ) }
								</Button>
								<Button
									variant="outline"
									className="w-full"
									onClick={ () => navigate( '/payments' ) }
								>
									{ __( '← Back to Payments', 'smartpay' ) }
								</Button>
							</div>
						</SectionCard>

						<SectionCard title={ __( 'Transaction Info', 'smartpay' ) }>
							<dl className="space-y-3">
								<InfoField label={ __( 'Payment ID', 'smartpay' ) }>
									#{ payment.id }
								</InfoField>
								<InfoField label={ __( 'Gateway', 'smartpay' ) }>
									{ payment.gateway }
								</InfoField>
								{ payment.transaction_id && (
									<InfoField label={ __( 'Transaction ID', 'smartpay' ) }>
										{ payment.transaction_id }
									</InfoField>
								) }
								<InfoField label={ __( 'Mode', 'smartpay' ) }>
									{ payment.mode || __( 'live', 'smartpay' ) }
								</InfoField>
								<InfoField label={ __( 'Currency', 'smartpay' ) }>
									{ payment.currency }
								</InfoField>
							</dl>
						</SectionCard>
					</div>
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

	const humanize = ( str ) =>
		String( str ).replace( /_/g, ' ' ).replace( /\b\w/g, ( c ) => c.toUpperCase() )

	const renderRaw = ( data, depth = 0 ) =>
		Object.entries( data || {} ).flatMap( ( [ key, val ] ) => {
			if ( val === null || val === undefined || val === '' ) return []
			if ( typeof val === 'object' && ! Array.isArray( val ) ) {
				return renderRaw( val, depth + 1 )
			}
			return (
				<div key={ key } className="mb-3">
					<dt className="text-xs text-gray-500 uppercase tracking-wide mb-0.5">
						{ humanize( key ) }
					</dt>
					<dd className="text-sm font-medium text-gray-800">{ String( val ) }</dd>
				</div>
			)
		} )

	const renderFields = ( labels, data ) => (
		<div key={ Math.random().toString( 36 ).substr( 2, 9 ) }>
			{ Object.keys( labels ).map( ( key ) =>
				typeof labels[ key ] === 'object'
					? renderFields( labels[ key ], data?.[ key ] )
					: data?.[ key ] && (
						<div key={ key } className="mb-3">
							<dt className="text-xs text-gray-500 uppercase tracking-wide mb-0.5">
								{ labels[ key ] }
							</dt>
							<dd className="text-sm font-medium text-gray-800">{ data[ key ] }</dd>
						</div>
					)
			) }
		</div>
	)

	const labels = build( formFields || [] )
	const hasLabels = Object.keys( labels ).length > 0

	return (
		<SectionCard title={ __( 'Form Data', 'smartpay' ) }>
			<dl>
				{ hasLabels
					? renderFields( labels, formData )
					: renderRaw( formData ) }
			</dl>
		</SectionCard>
	)
}
