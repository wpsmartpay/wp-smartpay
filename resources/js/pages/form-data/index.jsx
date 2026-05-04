import { __ } from '@wordpress/i18n'
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element'
import { Printer } from 'lucide-react'
import { GetFormSubmissions } from '../../http/form-data'
import { createFormDataColumns, flattenFormDataForPrint } from './columns'

export const FormData = () => {
    const { Header, DataTable, Button, Card, CardContent } = window.WPSmartPayUI

    const [data, setData] = useState([])
    const [isLoading, setIsLoading] = useState(false)
    const [perPage, setPerPage] = useState(10)
    const [pagination, setPagination] = useState({
        current_page: 1,
        per_page: 10,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
    })
    const [searchQuery, setSearchQuery] = useState('')
    const [sortBy, setSortBy] = useState('id:desc')
    const [rowSelection, setRowSelection] = useState({})

    const fetchSubmissions = useCallback(async (page = 1, pageSize = 10, search = '', sortBy = 'id:desc') => {
        setIsLoading(true)
        try {
            const result = await GetFormSubmissions({ page, perPage: pageSize, search, sortBy })
            const { data: rows = [], ...paging } = result
            setData(rows)
            setPagination({
                current_page: paging.current_page,
                per_page: paging.per_page,
                last_page: paging.last_page,
                total: paging.total,
                from: paging.from,
                to: paging.to,
            })
        } catch (err) {
            console.error('Failed to load submissions', err)
        } finally {
            setIsLoading(false)
        }
    }, [])

    useEffect(() => {
        fetchSubmissions(1, perPage, searchQuery, sortBy)
    }, [fetchSubmissions, searchQuery, sortBy, perPage])

    const handlePaginationChange = useCallback(({ page, per_page }) => {
        if (per_page !== perPage) {
            setPerPage(per_page)
            setRowSelection({})
        }
        fetchSubmissions(page, per_page !== perPage ? per_page : perPage, searchQuery, sortBy)
    }, [fetchSubmissions, searchQuery, sortBy, perPage])

    const handleSearchChange = (search) => {
        setRowSelection({})
        setSearchQuery(search)
    }

    const handlePerPageChange = (e) => {
        const val = parseInt(e.target.value, 10)
        setPerPage(val)
        setRowSelection({})
        setPagination(p => ({ ...p, current_page: 1, per_page: val }))
        fetchSubmissions(1, val, searchQuery, sortBy)
    }

    const handleBulkPrint = () => {
        const rows = Object.keys(rowSelection).map(idx => data[idx]).filter(Boolean)
        if (!rows.length) return

        const win = window.open('', '_blank')
        win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>${__('Form Submissions', 'smartpay')}</title><style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #111827; }
            h1 { font-size: 20px; font-weight: 700; margin-bottom: 24px; color: #111827; }
            .submissions { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
            .card { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
            .card-header { background: #f9fafb; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
            .card-header-title { font-size: 13px; font-weight: 600; color: #374151; }
            .card-header-id { font-size: 12px; color: #6b7280; }
            .card-body { padding: 16px; }
            .card-body table { width: 100%; border-collapse: collapse; }
            .card-body tr { border-bottom: 1px solid #f3f4f6; }
            .card-body tr:last-child { border-bottom: none; }
            .card-body td { padding: 8px 0; font-size: 13px; }
            .card-body td:first-child { font-weight: 500; color: #6b7280; width: 40%; }
            .card-body td:last-child { color: #111827; }
            .empty { color: #9ca3af; font-size: 13px; padding: 8px 0; }
            @media print {
                body { padding: 20px; }
                .submissions { display: block; }
                .card { margin-bottom: 24px; border: 1px solid #ccc; }
                .card-header { background: #eee; }
            }
        </style></head><body>
            <h1>${__('Form Submissions', 'smartpay')} (${rows.length})</h1>
            <div class="submissions">
                ${rows.map(row => {
                    const formData = row.extra?.form_data || row.data?.form_data || {}
                    const fields = flattenFormDataForPrint(formData)
                    const formTitle = row.data?.form_title || 'Form Submission'
                    return `
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <div class="card-header-title">${formTitle}</div>
                                </div>
                                <div class="card-header-id">#${row.id}</div>
                            </div>
                            <div class="card-body">
                                ${fields.length === 0
                                    ? '<p class="empty">No data</p>'
                                    : `<table><tbody>${fields.map(({ label, value }) => `
                                        <tr><td>${label}</td><td>${String(value)}</td></tr>
                                    `).join('')}</tbody></table>`
                                }
                            </div>
                        </div>
                    `
                }).join('')}
            </div>
        </body></html>`)
        win.document.close()
        win.print()
    }

    const selectedCount = Object.keys(rowSelection).length
    const columns = useMemo(() => createFormDataColumns(), [])

    return (
        <>
            <Header
                title={__('Form Data', 'smartpay')}
                subtitle={__('View all form submissions', 'smartpay')}
            />

            <div className="p-4 max-w-7xl mx-auto">
                <Card>
                    <CardContent>
                        <DataTable
                            columns={columns}
                            data={data}
                            pagination={pagination}
                            onPaginationChange={handlePaginationChange}
                            onSearchChange={handleSearchChange}
                            isLoading={isLoading}
                            searchPlaceholder={__('Search submissions...', 'smartpay')}
                            enableRowSelection={true}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                            enableActions={true}
                            actions={
                                selectedCount > 0
                                    ? [
                                        <span key="selected-count" className="text-sm text-gray-500 mr-2">
                                            {selectedCount} {selectedCount === 1 ? __('selected', 'smartpay') : __('selected', 'smartpay')}
                                        </span>,
                                        <Button
                                            key="bulk-print"
                                            variant="default"
                                            size="sm"
                                            onClick={handleBulkPrint}
                                        >
                                            <Printer className="w-4 h-4 mr-1" />
                                            {__('Print Selected', 'smartpay')}
                                        </Button>,
                                    ]
                                    : []
                            }
                        />

                        <div className="flex items-center justify-between mt-4 pt-4 border-t">
                            <div className="flex items-center gap-2">
                                <label className="text-sm text-gray-600">{__('Show', 'smartpay')}</label>
                                <select
                                    className="border border-gray-300 rounded px-2 py-1 text-sm"
                                    value={perPage}
                                    onChange={handlePerPageChange}
                                >
                                    <option value={10}>10</option>
                                    <option value={25}>25</option>
                                    <option value={50}>50</option>
                                    <option value={100}>100</option>
                                </select>
                                <label className="text-sm text-gray-600">{__('per page', 'smartpay')}</label>
                            </div>
                            {selectedCount > 0 && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setRowSelection({})}
                                >
                                    {__('Clear selection', 'smartpay')}
                                </Button>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    )
}
