const { registerBlockType } = wp.blocks
const { createElement } = wp.element

registerBlockType('smartpay/product', {
	title: 'SmartPay Product.',
	description: 'Simple block to show a product',
	icon: 'universal-access-alt',
	category: 'widgets',
	attributes: {
		id: {
			type: 'string',
		},
	},
	edit: ({ attributes, setAttributes }) => {
		function saveId(event) {
			setAttributes({ id: event.target.value })
		}

		return createElement(
			'div',
			{
				class: 'smartpay',
			},
			createElement(
				'div',
				{
					class: 'block-editor-product card py-4',
				},
				createElement(
					'div',
					{
						class: 'card-body text-center',
					},
					createElement('img', {
						src: smartpay_product_block_data.logo,
						class: 'logo img-fluid',
					}),
					createElement(
						'div',
						{
							class: 'd-flex justify-content-center mt-4',
						},
						createElement(
							'div',
							{
								class: 'col-md-8',
							},
							createElement(
								'select',
								{
									class: 'form-control form-control-sm',
								},
								JSON.parse(
									smartpay_product_block_data.products
								).map((product, index) => {
									return createElement(
										'option',
										null,
										`${product.name} (#${product.id})`
									)
								})
							)
						)
					)
				)
			)
		)
	},

	save: ({ attributes }) => {
		return createElement(
			'div',
			null,
			`[smartpay_product id="${attributes.id}"]`
		)
	},
})
