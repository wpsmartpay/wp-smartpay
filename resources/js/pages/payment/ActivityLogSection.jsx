import { useCallback, useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { AddPaymentLog, GetPaymentLogs } from '@/http/payment'
import Swal from 'sweetalert2/dist/sweetalert2.js'

/* ── Helpers ──────────────────────────────────────────────── */

const ACTION_LABELS = {
	status_changed:    __('Status Changed',    'smartpay'),
	admin_note:        __('Admin Note',        'smartpay'),
	payment_created:   __('Payment Created',   'smartpay'),
	payment_completed: __('Payment Completed', 'smartpay'),
	refund_processed:  __('Refund Processed',  'smartpay'),
	webhook_received:  __('Webhook Received',  'smartpay'),
}

const formatLabel = (action) =>
	ACTION_LABELS[action] ||
	action.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())

const formatDate = (dateStr) => {
	if (!dateStr) return ''
	const d = new Date(dateStr + 'Z')
	return d.toLocaleString('en-US', {
		month: 'short', day: 'numeric', year: 'numeric',
		hour: 'numeric', minute: '2-digit',
	})
}

const dotClass = (action) => {
	const map = {
		payment_created:   'sp-timeline__dot--green',
		payment_completed: 'sp-timeline__dot--green',
		refund_processed:  'sp-timeline__dot--amber',
		status_changed:    'sp-timeline__dot--indigo',
		webhook_received:  'sp-timeline__dot--indigo',
		admin_note:        'sp-timeline__dot--gray',
	}
	return map[action] || 'sp-timeline__dot--gray'
}

const dotInitial = (action) => {
	const map = {
		payment_created:   'P',
		payment_completed: '✓',
		refund_processed:  'R',
		status_changed:    'S',
		webhook_received:  'W',
		admin_note:        'N',
	}
	return map[action] || (action || '?')[0].toUpperCase()
}

/* ── Component ────────────────────────────────────────────── */

export const ActivityLogSection = ({ paymentId }) => {
	const [logs,       setLogs]       = useState([])
	const [loading,    setLoading]    = useState(false)
	const [note,       setNote]       = useState('')
	const [submitting, setSubmitting] = useState(false)

	const fetchLogs = useCallback(async () => {
		if (!paymentId) return
		setLoading(true)
		try {
			const res = await GetPaymentLogs(paymentId)
			setLogs(res?.data || [])
		} catch {
			/* silently ignore — endpoint may not exist on older installs */
		} finally {
			setLoading(false)
		}
	}, [paymentId])

	useEffect(() => { fetchLogs() }, [fetchLogs])

	const handleAddNote = async () => {
		if (!note.trim()) return
		setSubmitting(true)
		try {
			await AddPaymentLog(paymentId, note.trim())
			setNote('')
			await fetchLogs()
		} catch {
			Swal.fire({ icon: 'error', title: __('Error', 'smartpay'), text: __('Failed to add note.', 'smartpay') })
		} finally {
			setSubmitting(false)
		}
	}

	return (
		<div className="sp-detail-card">
			<div className="sp-detail-card__header">
				<span className="sp-detail-card__title">{__('Activity Log', 'smartpay')}</span>
			</div>
			<div className="sp-detail-card__body">

				{loading ? (
					<p style={{ fontSize: 13, color: 'var(--sp-text-muted)' }}>{__('Loading…', 'smartpay')}</p>
				) : logs.length === 0 ? (
					<p style={{ fontSize: 13, color: 'var(--sp-text-muted)' }}>
						{__('No activity recorded yet.', 'smartpay')}
					</p>
				) : (
					<ul className="sp-timeline">
						{logs.map((log) => (
							<li key={log.id} className="sp-timeline__item">
								<div className={`sp-timeline__dot ${dotClass(log.action)}`}>
									{dotInitial(log.action)}
								</div>
								<div className="sp-timeline__body">
									<div className="sp-timeline__action">{formatLabel(log.action)}</div>
									{log.note && <div className="sp-timeline__note">{log.note}</div>}
								</div>
								<div className="sp-timeline__meta">
									{log.user_name && (
										<span className="sp-timeline__user">{log.user_name}</span>
									)}
									<span className="sp-timeline__time">{formatDate(log.created_at)}</span>
								</div>
							</li>
						))}
					</ul>
				)}

				<div className="sp-note-form">
					<input
						type="text"
						className="sp-note-input"
						value={note}
						onChange={(e) => setNote(e.target.value)}
						onKeyDown={(e) => e.key === 'Enter' && handleAddNote()}
						placeholder={__('Add a note…', 'smartpay')}
					/>
					<button className="sp-btn sp-btn--primary"
						onClick={handleAddNote}
						disabled={submitting || !note.trim()}>
						{submitting ? __('Adding…', 'smartpay') : __('Add Note', 'smartpay')}
					</button>
				</div>
			</div>
		</div>
	)
}
