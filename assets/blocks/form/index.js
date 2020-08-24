const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.editor

const { SelectControl, TextControl, CardBody } = wp.components

// Form block
export default registerBlockType('smartpay/form', {
	title: 'SmartPay Form',
	description: 'Simple block to show a form',
	icon: 'format-aside',
	category: 'widgets',

	attributes: {
		id: {
			type: 'integer',
			default: 0,
		},
		behavior: {
			type: 'string',
			default: '',
		},
		label: {
			type: 'string',
			default: '',
		},
	},
	edit: ({ attributes, setAttributes }) => {
		function setId(id) {
			setAttributes({ id: parseInt(id) })
		}

		function setBehavior(behavior) {
			setAttributes({ behavior: behavior })
		}

		function setLabel(label) {
			setAttributes({ label: label })
		}

		return (
			<div class="smartpay">
				<InspectorControls>
					<CardBody>
						<SelectControl
							label={__('Shortcode behavior')}
							value={attributes.behavior}
							onChange={setBehavior}
							options={[
								{
									value: null,
									label: 'Select a behavior',
									disabled: true,
								},
								{ value: 'popup', label: 'Popup' },
								{ value: 'embedded', label: 'Embedded' },
							]}
						/>
						{'popup' === attributes.behavior && (
							<TextControl
								label="Button label"
								value={attributes.label}
								onChange={setLabel}
							/>
						)}
					</CardBody>
				</InspectorControls>

				<div class="container block-editor form card py-4">
					<div class="card-body text-center">
						<img src={smartpay_logo} class="logo img-fluid" />
						<div class="d-flex justify-content-center mt-1">
							<div class="col-md-8">
								<h5
									class="text-center mb-3 m-0 font-weight-normal"
									style={{ fontSize: '16px' }}
								>
									{__('Select a Form', 'smartpay')}
								</h5>
								<SelectControl
									class="form-control form-control-sm mx-auto"
									value={attributes.id}
									onChange={setId}
									options={[
										{
											value: null,
											label: 'Select a form',
											disabled: true,
										},
										...JSON.parse(
											smartpay_block_editor_forms
										).map((form) => {
											return {
												value: form.id,
												label: form.name,
											}
										}),
									]}
								/>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	},

	save: ({ attributes }) => {
		return createElement(
			'div',
			null,
			`[smartpay_form id="${attributes.id}" behavior="${attributes.behavior}" label="${attributes.label}"]`
		)
	},
})
