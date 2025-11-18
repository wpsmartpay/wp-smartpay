import {
	flexRender,
	getCoreRowModel,
	getSortedRowModel,
	useReactTable,
} from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ChevronDown, ChevronUp, ChevronsUpDown, Loader2 } from 'lucide-react'

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
import { useEffect, useState } from '@wordpress/element'

export function DataTable({
    columns,
    data,
    pagination,
    onPaginationChange,
    onSearchChange,
    onSortChange,
    isLoading = false,
    searchPlaceholder = 'Search...',
    enableSearch = true,
    enableSorting = false,
    enableFilters = false,
    filters = [],
    sortingState = [],
}) {
    const [searchValue, setSearchValue] = useState('')
    const [debouncedSearch, setDebouncedSearch] = useState('')
    const [sorting, setSorting] = useState(sortingState)

    // Debounce search input
    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(searchValue)
        }, 500)

        return () => clearTimeout(timer)
    }, [searchValue])

    // Trigger search when debounced value changes
    useEffect(() => {
        if (onSearchChange && enableSearch) {
            onSearchChange(debouncedSearch)
        }
    }, [debouncedSearch, onSearchChange, enableSearch])

    // Trigger sort change
    useEffect(() => {
        if (onSortChange && enableSorting) {
            onSortChange(sorting)
        }
    }, [sorting, onSortChange, enableSorting])

    const handleSearchChange = (event) => {
        setSearchValue(event.target.value)
    }

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: enableSorting ? getSortedRowModel() : undefined,
        manualPagination: true,
        manualSorting: enableSorting,
        pageCount: pagination?.last_page ?? -1,
        state: {
            pagination: {
                pageIndex: (pagination?.current_page ?? 1) - 1,
                pageSize: pagination?.per_page ?? 10,
            },
            sorting: enableSorting ? sorting : undefined,
        },
        onSortingChange: enableSorting ? setSorting : undefined,
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

    const getSortIcon = (header) => {
        if (!enableSorting || !header.column.getCanSort()) return null

        const sorted = header.column.getIsSorted()
        if (sorted === 'asc') return <ChevronUp className="ml-2 h-4 w-4" />
        if (sorted === 'desc') return <ChevronDown className="ml-2 h-4 w-4" />
        return <ChevronsUpDown className="ml-2 h-4 w-4" />
    }

    return (
        <div>
            {/* Search and Filters */}
            {(enableSearch || enableFilters) && (
                <div className="flex items-center justify-between gap-4 py-4">
                    <div className="flex items-center gap-4 flex-1">
                        {enableSearch && (
							<div className='relative w-xs'>
								<Input
									placeholder={searchPlaceholder}
									value={searchValue}
									onChange={handleSearchChange}
									className="max-w-xs"
								/>
								{isLoading && searchValue.length > 0 && (
									<Loader2 className="animate-spin absolute right-3 top-2.5 size-4 text-gray-500" />
								)}
							</div>
                        )}
                        {enableFilters && filters && filters.length > 0 && (
                            <div className="flex items-center gap-2">
                                {filters.map((filter, index) => (
                                    <div key={index}>
                                        {filter}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                    {pagination && (
                        <div className="text-sm text-gray-600">
                            {__('Showing', 'smartpay')} {pagination.from} {__('to', 'smartpay')} {pagination.to} {__('of', 'smartpay')} {pagination.total} {__('results', 'smartpay')}
                        </div>
                    )}
                </div>
            )}

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    const canSort = enableSorting && header.column.getCanSort()
                                    return (
                                        <TableHead
                                            key={header.id}
                                        >
                                            {header.isPlaceholder ? null : (
                                                <div
                                                    className={canSort ? "flex justify-center items-center cursor-pointer select-none" : ""}
                                                    onClick={canSort ? header.column.getToggleSortingHandler() : undefined}
                                                >
                                                    {flexRender(
                                                        header.column.columnDef.header,
                                                        header.getContext()
                                                    )}
                                                    {getSortIcon(header)}
                                                </div>
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
                                    <div className="w-full flex items-center justify-center">
										<Loader2 className="animate-spin size-6 text-gray-500" />
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
                                        <TableCell key={cell.id}>
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
