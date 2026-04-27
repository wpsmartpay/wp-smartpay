import { __ } from '@wordpress/i18n'
import { ExternalLink, SquarePen, Trash2 } from 'lucide-react'
import { Link } from 'react-router-dom'

const decodeHtmlEntity = ( str ) => {
	if ( ! str ) return ''
	const txt = document.createElement( 'textarea' )
	txt.innerHTML = str
	return txt.value
}

const currencySymbol = decodeHtmlEntity( window.smartpay?.options?.currencySymbol ) || '$'

const fmt = ( v ) => {
	const n = parseFloat( v )
	return isNaN( n ) ? null : currencySymbol + n.toFixed( 2 )
}

/* Effective price for a row (sale_price if > 0, else base_price) */
const effectivePrice = ( row ) => {
	const sale = parseFloat( row.sale_price )
	const base = parseFloat( row.base_price )
	return sale > 0 ? sale : ( isNaN( base ) ? 0 : base )
}

const TypeBadge = ( { label } ) => {
	const isSubscription = label === 'Subscription'
	return (
		<span style={ {
			display:      'inline-block',
			padding:      '2px 8px',
			borderRadius: '20px',
			fontSize:     '11px',
			fontWeight:   600,
			background:   isSubscription ? '#ede9fe' : '#f0fdf4',
			color:        isSubscription ? '#6d28d9' : '#166534',
			border:       isSubscription ? '1px solid #ddd6fe' : '1px solid #bbf7d0',
		} }>
			{ label }
		</span>
	)
}

export const createProductColumns = ( deleteProduct ) => {
	const { Button } = window.WPSmartPayUI

	return [
		{
			accessorKey: 'title',
			header: __( 'Product', 'smartpay' ),
			enableSorting: false,
			cell: ( { row } ) => {
				const product = row.original
				return (
					<div className="flex flex-col">
						<span className="font-medium text-gray-900">
							{ product.title || __( 'Untitled', 'smartpay' ) }
						</span>
						<span className="text-xs text-gray-400">
							{ __( 'ID', 'smartpay' ) }: { product.id }
						</span>
					</div>
				)
			},
		},
		{
			id: 'billing_type',
			header: __( 'Type', 'smartpay' ),
			enableSorting: false,
			cell: ( { row } ) => {
				const product = row.original
				const variations = product.variations || []

				/* Collect unique billing types */
				let types = []
				if ( variations.length > 0 ) {
					variations.forEach( ( v ) => {
						const t = v.extra?.billing_type
						if ( t && ! types.includes( t ) ) types.push( t )
					} )
				} else {
					const t = product.extra?.billing_type
					if ( t ) types.push( t )
				}

				if ( types.length === 0 ) {
					return <span className="text-xs text-gray-400">—</span>
				}

				return (
					<div className="flex flex-wrap gap-1">
						{ types.map( ( t ) => <TypeBadge key={ t } label={ t } /> ) }
					</div>
				)
			},
		},
		{
			id: 'options',
			header: () => <div className="text-center">{ __( 'Options', 'smartpay' ) }</div>,
			enableSorting: false,
			cell: ( { row } ) => {
				const product = row.original
				const count = ( product.variations || [] ).length

				return (
					<div className="text-center">
						{ count > 0 ? (
							<span className="text-sm font-medium text-gray-700">
								{ count } { count === 1 ? __( 'option', 'smartpay' ) : __( 'options', 'smartpay' ) }
							</span>
						) : (
							<span className="text-xs text-gray-400">{ __( 'Single', 'smartpay' ) }</span>
						) }
					</div>
				)
			},
		},
		{
			id: 'price_range',
			header: () => <div className="text-right">{ __( 'Price', 'smartpay' ) }</div>,
			enableSorting: false,
			cell: ( { row } ) => {
				const product = row.original
				const variations = product.variations || []

				if ( variations.length > 1 ) {
					/* Multi-option: show range */
					const prices = variations.map( effectivePrice )
					const min = Math.min( ...prices )
					const max = Math.max( ...prices )

					return (
						<div className="text-right text-sm font-medium text-gray-700">
							{ min === max
								? fmt( min )
								: <>{ fmt( min ) } <span className="text-gray-400 font-normal">–</span> { fmt( max ) }</>
							}
						</div>
					)
				}

				if ( variations.length === 1 ) {
					const v   = variations[ 0 ]
					const base = fmt( v.base_price )
					const sale = parseFloat( v.sale_price ) > 0 ? fmt( v.sale_price ) : null

					return (
						<div className="text-right text-sm text-gray-700 font-medium">
							{ sale ? (
								<>
									<span className="line-through text-gray-400 mr-1">{ base }</span>
									{ sale }
								</>
							) : base }
						</div>
					)
				}

				/* No variations: use product's own prices */
				const base = fmt( product.base_price )
				const sale = parseFloat( product.sale_price ) > 0 ? fmt( product.sale_price ) : null

				return (
					<div className="text-right text-sm text-gray-700 font-medium">
						{ sale ? (
							<>
								<span className="line-through text-gray-400 mr-1">{ base }</span>
								{ sale }
							</>
						) : ( base || <span className="text-gray-400">—</span> ) }
					</div>
				)
			},
		},
		{
			accessorKey: 'created_at',
			header: () => <div className="text-center">{ __( 'Date', 'smartpay' ) }</div>,
			enableSorting: false,
			cell: ( { row } ) => (
				<div className="text-center text-sm text-gray-500">
					{ row.getValue( 'created_at' )
						? new Date( row.getValue( 'created_at' ) ).toLocaleDateString()
						: '—' }
				</div>
			),
		},
		{
			id: 'actions',
			header: () => <div className="text-right mr-4!">{ __( 'Actions', 'smartpay' ) }</div>,
			cell: ( { row } ) => {
				const product = row.original

				return (
					<div className="flex items-center justify-end gap-2">
						{ product?.extra?.product_preview_page_permalink && (
							<a href={ product.extra.product_preview_page_permalink } target="_blank" rel="noopener noreferrer">
								<Button
									variant="outline"
									size="icon"
									title={ __( 'Preview', 'smartpay' ) }
									className="hover:bg-gray-100 cursor-pointer"
								>
									<ExternalLink className="w-4 h-4 text-gray-700" />
								</Button>
							</a>
						) }
						<Link to={ `/products/${ product.id }/edit` }>
							<Button
								variant="outline"
								size="icon"
								title={ __( 'Edit', 'smartpay' ) }
								className="hover:bg-gray-100 cursor-pointer"
							>
								<SquarePen className="w-4 h-4 text-gray-700" />
							</Button>
						</Link>
						<Button
							variant="outline"
							size="icon"
							title={ __( 'Delete', 'smartpay' ) }
							onClick={ () => deleteProduct( product.id ) }
							className="hover:bg-red-50 cursor-pointer border-red-200!"
						>
							<Trash2 className="w-4 h-4 text-red-600" />
						</Button>
					</div>
				)
			},
		},
	]
}
