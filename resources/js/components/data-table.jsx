import {
	flexRender,
	getCoreRowModel,
	useReactTable,
} from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table'
import { Spinner } from './ui/spinner'

export function DataTable({ columns, data, pagination, onPaginationChange, onSearchChange, isLoading = false }) {
    const [searchValue, setSearchValue] = React.useState('')
    const [debouncedSearch, setDebouncedSearch] = React.useState('')

    // Debounce search input
    React.useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(searchValue)
        }, 500)

        return () => clearTimeout(timer)
    }, [searchValue])

    // Trigger search when debounced value changes
    React.useEffect(() => {
        if (onSearchChange) {
            onSearchChange(debouncedSearch)
        }
    }, [debouncedSearch, onSearchChange])

    const handleSearchChange = (event) => {
        setSearchValue(event.target.value)
    }

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        manualPagination: true, // Important: Tell TanStack this is server-side pagination
        pageCount: pagination?.last_page ?? -1,
        state: {
            pagination: {
                pageIndex: (pagination?.current_page ?? 1) - 1, // Convert to 0-based index
                pageSize: pagination?.per_page ?? 10,
            },
        },
    })

    const handlePreviousPage = () => {
        if (onPaginationChange && pagination && pagination.current_page > 1) {
            onPaginationChange({
                page: pagination.current_page - 1,
                per_page: pagination.per_page
            })
        }
    }

    const handleNextPage = () => {
        if (onPaginationChange && pagination && pagination.current_page < pagination.last_page) {
            onPaginationChange({
                page: pagination.current_page + 1,
                per_page: pagination.per_page
            })
        }
    }

    const canPreviousPage = pagination && pagination.current_page > 1
    const canNextPage = pagination && pagination.current_page < pagination.last_page

    return (
        <div>
            {/* Search/Filter Input */}
            <div className="flex items-center justify-between py-4">
                <Input
                    placeholder={__('Filter by customer email...', 'smartpay')}
                    value={searchValue}
                    onChange={handleSearchChange}
                    className="max-w-sm"
                    disabled={isLoading}
                />
                {pagination && (
                    <div className="text-sm text-gray-600">
                        {__('Showing', 'smartpay')} {pagination.from} {__('to', 'smartpay')} {pagination.to} {__('of', 'smartpay')} {pagination.total} {__('results', 'smartpay')}
                    </div>
                )}
            </div>

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id} className="text-center">
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                      header.column.columnDef.header,
                                                      header.getContext()
                                                  )}
                                        </TableHead>
                                    )
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {isLoading ? (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center"
                                >
									<div className="h-[513px] w-full flex items-center justify-center">
										<Spinner className="size-6"/>
									</div>
                                </TableCell>
                            </TableRow>
                        ) : table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={row.getIsSelected() && 'selected'}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id} className="text-center">
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext()
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center"
                                >
                                    {__('No payment found.', 'smartpay')}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {/* Pagination */}
            <div className="flex items-center justify-between space-x-2 py-4">
                <div className="text-sm text-gray-600">
                    {pagination && (
                        <>
                            {__('Page', 'smartpay')} {pagination.current_page} {__('of', 'smartpay')} {pagination.last_page}
                        </>
                    )}
                </div>
                <div className="flex space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={handlePreviousPage}
                        disabled={!canPreviousPage || isLoading}
                    >
                        {__('Previous', 'smartpay')}
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={handleNextPage}
                        disabled={!canNextPage || isLoading}
                    >
                        {__('Next', 'smartpay')}
                    </Button>
                </div>
            </div>
        </div>
    )
}
