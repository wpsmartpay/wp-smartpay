const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { Fragment } = wp.element

import Sidebar from './components/Sidebar'
import SelectForm from './components/SelectForm'

export default registerBlockType('smartpay/form', {
	title: __('SmartPay Form', 'smartpay'),
	description: __('Simple block to show a form', 'smartpay'),
	icon: 'format-aside',
	category: 'widgets',

	attributes: {
		id: {
			type: 'integer',
			default: 0,
		},
		behavior: {
			type: 'string',
			default: 'popup',
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

		let formOptions = [
			{
				value: null,
				label: __('Select a form', 'smartpay'),
			},
			...JSON.parse(smartpay_block_editor_forms).map((form) => {
				return {
					value: form.id,
					label: `(#${form.id}) ${form.name}`,
				}
			}),
		]

		return (
			<Fragment>
				<div class="smartpay">
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
									<SelectForm
										formOptions={formOptions}
										formId={attributes.id}
										onSetId={setId}
										class="form-control form-control-sm mx-auto"
									></SelectForm>
								</div>
							</div>
						</div>
					</div>
				</div>

				<Sidebar
					attributes={attributes}
					onSetId={setId}
					onSetBehavior={setBehavior}
					onSetLabel={setLabel}
				></Sidebar>
			</Fragment>
		)
	},

	save: ({ attributes }) => {
		return (
			<div>{`[smartpay_form id="${attributes.id}" behavior="${attributes.behavior}" label="${attributes.label}"]`}</div>
		)
	},
})