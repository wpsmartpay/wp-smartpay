import { __ } from '@wordpress/i18n'
import { Eye, Printer, Share2 } from 'lucide-react'
import { Link } from 'react-router-dom'
import { createPortal } from '@wordpress/element'

const StatusBadge = ({ status }) => {
    const map = {
        completed: { bg: '#f0fdf4', color: '#166534', border: '#bbf7d0' },
        pending:   { bg: '#fef9c3', color: '#713f12', border: '#fde047' },
        failed:    { bg: '#fef2f2', color: '#7f1d1d', border: '#fecaca' },
    }
    const s = map[status?.toLowerCase()] || map.pending
    return (
        <span style={{
            display:      'inline-block',
            padding:      '2px 8px',
            borderRadius: '20px',
            fontSize:     '11px',
            fontWeight:   600,
            background:   s.bg,
            color:        s.color,
            border:       `1px solid ${s.border}`,
        }}>
            { status || 'pending' }
        </span>
    )
}

const capitalize = (str) => {
    if (!str) return ''
    return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const flattenFormData = (obj, prefix = '') => {
    const rows = []
    if (!obj || typeof obj !== 'object') return rows
    for (const [key, val] of Object.entries(obj)) {
        const label = prefix ? `${prefix} › ${capitalize(key)}` : capitalize(key)
        if (val && typeof val === 'object' && !Array.isArray(val)) {
            rows.push(...flattenFormData(val, label))
        } else {
            rows.push({ label, value: Array.isArray(val) ? val.join(', ') : (val ?? '—') })
        }
    }
    return rows
}

const quickView = (row) => {
    const payment = row.original
    const formData = payment.extra?.form_data || payment.data?.form_data || {}
    const fields = flattenFormData(formData)
    const formTitle = payment.data?.form_title || 'Form Submission'

    const overlay = document.createElement('div')
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99998;display:flex;align-items:center;justify-content:center;padding:20px'

    const dialog = document.createElement('div')
    dialog.style.cssText = 'background:#fff;border-radius:12px;max-width:600px;width:100%;max-height:80vh;overflow:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)'
    dialog.innerHTML = `
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px">${formTitle}</div>
                <h3 style="margin:0;font-size:18px;font-weight:600;color:#111827">${__('Form Submission', 'smartpay')} #${payment.id}</h3>
            </div>
            <button id="sp-dv-close" style="background:none;border:none;cursor:pointer;padding:8px;color:#6b7280">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div style="padding:24px">
            ${fields.length === 0 ? '<p style="color:#6b7280;text-align:center;padding:20px 0">No submission data available.</p>' : `
            <table style="width:100%;border-collapse:collapse">
                <tbody>
                    ${fields.map(({ label, value }) => `
                        <tr style="border-bottom:1px solid #f3f4f6">
                            <td style="padding:12px 0;font-size:13px;font-weight:500;color:#374151;width:40%;vertical-align:top">${label}</td>
                            <td style="padding:12px 0;font-size:13px;color:#111827;text-align:right;word-break:break-all">${String(value)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            `}
        </div>
    `

    overlay.appendChild(dialog)
    document.body.appendChild(overlay)
    document.getElementById('sp-dv-close').addEventListener('click', () => overlay.remove())
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove() })
}

const shareRow = (row) => {
    const payment = row.original
    const url = `${window.smartpay.adminUrl}admin.php?page=smartpay#/payments/${payment.id}`
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            const toast = document.createElement('div')
            toast.textContent = __('Link copied!', 'smartpay')
            toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#111827;color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,0.15)'
            document.body.appendChild(toast)
            setTimeout(() => toast.remove(), 2000)
        })
    }
}

export const createFormDataColumns = () => {
    const { Button } = window.WPSmartPayUI

    return [
        {
            id: 'select',
            enableSorting: false,
            size: 40,
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={() => table.toggleAllPageRowsSelected()}
                    style={{ cursor: 'pointer' }}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={() => row.toggleSelected()}
                    style={{ cursor: 'pointer' }}
                />
            ),
        },
        {
            accessorKey: 'id',
            header: __('ID', 'smartpay'),
            enableSorting: false,
            size: 70,
            cell: ({ row }) => (
                <Link to={`/payments/${row.original.id}`} className="text-sm font-medium text-primary hover:underline">
                    #{ row.original.id }
                </Link>
            ),
        },
        {
            accessorKey: 'form_name',
            header: __('Form Name', 'smartpay'),
            enableSorting: false,
            cell: ({ row }) => {
                const formTitle = row.original.data?.form_title || row.original.data?.form_id || '—'
                return <span className="text-sm text-gray-900">{ formTitle }</span>
            },
        },
        {
            accessorKey: 'name',
            header: __('Name', 'smartpay'),
            enableSorting: false,
            cell: ({ row }) => {
                const fd = row.original.extra?.form_data?.name || row.original.data?.form_data?.name || {}
                const name = fd.first_name || fd.last_name
                    ? `${fd.first_name || ''} ${fd.last_name || ''}`.trim()
                    : '—'
                return <span className="text-sm text-gray-900">{ name }</span>
            },
        },
        {
            accessorKey: 'email',
            header: __('Email', 'smartpay'),
            enableSorting: false,
            cell: ({ row }) => {
                const email = row.original.extra?.form_data?.email || row.original.data?.form_data?.email || '—'
                return <span className="text-sm text-gray-600">{ email }</span>
            },
        },
        {
            accessorKey: 'created_at',
            header: __('Date', 'smartpay'),
            enableSorting: false,
            cell: ({ row }) => {
                const date = row.original.created_at
                    ? new Date(row.original.created_at).toLocaleDateString()
                    : '—'
                return <span className="text-sm text-gray-500">{ date }</span>
            },
        },
        {
            accessorKey: 'status',
            header: __('Status', 'smartpay'),
            enableSorting: false,
            cell: ({ row }) => <StatusBadge status={row.original.status} />,
        },
        {
            id: 'actions',
            header: () => <div className="text-right">{ __('Actions', 'smartpay') }</div>,
            enableSorting: false,
            size: 110,
            cell: ({ row }) => (
                <div className="flex items-center justify-end gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        title={__('Quick View', 'smartpay')}
                        onClick={() => quickView(row)}
                    >
                        <Eye className="w-4 h-4 text-gray-700" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        title={__('Share', 'smartpay')}
                        onClick={() => shareRow(row)}
                    >
                        <Share2 className="w-4 h-4 text-gray-700" />
                    </Button>
                </div>
            ),
        },
    ]
}

export { flattenFormData as flattenFormDataForPrint }
