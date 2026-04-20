import { useCallback, useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { AddPaymentLog, GetPaymentLogs } from '@/http/payment'
import Swal from 'sweetalert2/dist/sweetalert2.js'

const { Button, Card, CardContent } = window.WPSmartPayUI

const ACTION_LABELS = {
	status_changed: __( 'Status Changed', 'smartpay' ),
	admin_note: __( 'Admin Note', 'smartpay' ),
	payment_created: __( 'Payment Created', 'smartpay' ),
	payment_completed: __( 'Payment Completed', 'smartpay' ),
	refund_processed: __( 'Refund Processed', 'smartpay' ),
	webhook_received: __( 'Webhook Received', 'smartpay' ),
}

const formatActionLabel = ( action ) =>
	ACTION_LABELS[ action ] ||
	action
		.replace( /_/g, ' ' )
		.replace( /\b\w/g, ( c ) => c.toUpperCase() )

const formatDate = ( dateStr ) => {
	if ( ! dateStr ) return ''
	const d = new Date( dateStr + 'Z' )
	return d.toLocaleString( 'en-US', {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour: 'numeric',
		minute: '2-digit',
	} )
}

export const ActivityLogSection = ( { paymentId } ) => {
	const [ logs, setLogs ] = useState( [] )
	const [ loading, setLoading ] = useState( false )
	const [ note, setNote ] = useState( '' )
	const [ submitting, setSubmitting ] = useState( false )

	const fetchLogs = useCallback( async () => {
		if ( ! paymentId ) return
		setLoading( true )
		try {
			const res = await GetPaymentLogs( paymentId )
			setLogs( res?.data || [] )
		} catch {
			// silently ignore — endpoint may not exist on older free plugin
		} finally {
			setLoading( false )
		}
	}, [ paymentId ] )

	useEffect( () => {
		fetchLogs()
	}, [ fetchLogs ] )

	const handleAddNote = async () => {
		if ( ! note.trim() ) return
		setSubmitting( true )
		try {
			await AddPaymentLog( paymentId, note.trim() )
			setNote( '' )
			await fetchLogs()
		} catch {
			Swal.fire( {
				icon: 'error',
				title: __( 'Error', 'smartpay' ),
				text: __( 'Failed to add note.', 'smartpay' ),
			} )
		} finally {
			setSubmitting( false )
		}
	}

	return (
		<Card>
			<CardContent className="pt-6">
				<h3 className="text-lg font-semibold mb-4">
					{ __( 'Activity Log', 'smartpay' ) }
				</h3>

				{ loading ? (
					<p className="text-sm text-gray-500">{ __( 'Loading…', 'smartpay' ) }</p>
				) : logs.length === 0 ? (
					<p className="text-sm text-gray-500">
						{ __( 'No activity recorded yet.', 'smartpay' ) }
					</p>
				) : (
					<ul className="space-y-2 mb-4">
						{ logs.map( ( log ) => (
							<li
								key={ log.id }
								className="flex items-start gap-3 text-sm border-b pb-2 last:border-0"
							>
								<span className="min-w-[140px] font-medium text-gray-700">
									{ formatActionLabel( log.action ) }
								</span>
								<span className="flex-1 text-gray-600">{ log.note }</span>
								<span className="text-gray-400 whitespace-nowrap text-xs">
									{ formatDate( log.created_at ) }
								</span>
							</li>
						) ) }
					</ul>
				) }

				<div className="flex gap-2 mt-4">
					<input
						type="text"
						value={ note }
						onChange={ ( e ) => setNote( e.target.value ) }
						onKeyDown={ ( e ) => e.key === 'Enter' && handleAddNote() }
						placeholder={ __( 'Add a note…', 'smartpay' ) }
						className="flex-1 border rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
					/>
					<Button
						size="sm"
						onClick={ handleAddNote }
						disabled={ submitting || ! note.trim() }
					>
						{ submitting ? __( 'Adding…', 'smartpay' ) : __( 'Add Note', 'smartpay' ) }
					</Button>
				</div>
			</CardContent>
		</Card>
	)
}
