import { __ } from '@wordpress/i18n'
import { GetSettings, UpdateSettings, ClearDebugLog } from '../http/settings'
import Swal from 'sweetalert2/dist/sweetalert2'

const { useState, useEffect, useCallback } = wp.element
const { Header, Button, Card, CardContent, CardFooter, Switch, Alert, AlertDescription } = window.WPSmartPayUI

// ─── Helpers ─────────────────────────────────────────────────────────────────

const tabClass = (isActive) =>
	isActive
		? 'px-5 py-2.5 text-sm font-semibold border-b-2 border-primary text-primary -mb-px bg-transparent cursor-pointer'
		: 'px-5 py-2.5 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground -mb-px bg-transparent cursor-pointer'

// ─── Field Components ────────────────────────────────────────────────────────

const FieldWrapper = ({ field, children }) => {
	if (field.type === 'header') return children
	return (
		<tr className="form-field">
			<th scope="row">
				<label htmlFor={`sp_setting_${field.id}`} className="font-medium text-sm text-card-foreground">
					{field.name}
				</label>
			</th>
			<td>
				{children}
				{field.desc && (
					<p
						className="text-muted-foreground text-xs mt-1"
						dangerouslySetInnerHTML={{ __html: field.desc }}
					/>
				)}
			</td>
		</tr>
	)
}

const SettingField = ({ field, value, onChange }) => {
	const id = `sp_setting_${field.id}`

	switch (field.type) {
		case 'header':
			return (
				<tr>
					<td colSpan={2} className="pt-4 pb-1">
						<h4 className="text-sm font-semibold text-muted-foreground uppercase tracking-wide border-b border-border pb-2">
							{field.name}
						</h4>
					</td>
				</tr>
			)

		case 'text':
			return (
				<FieldWrapper field={field}>
					<input
						id={id}
						type="text"
						value={value ?? field.std ?? ''}
						onChange={(e) => onChange(field.id, e.target.value)}
						placeholder={field.placeholder || ''}
						readOnly={field.readonly}
						className="regular-text"
					/>
				</FieldWrapper>
			)

		case 'textarea':
			return (
				<FieldWrapper field={field}>
					<textarea
						id={id}
						value={value ?? field.std ?? ''}
						onChange={(e) => onChange(field.id, e.target.value)}
						rows={field.rows || 5}
						readOnly={field.readonly}
						className="large-text"
						style={{ fontFamily: 'monospace', fontSize: '12px' }}
					/>
				</FieldWrapper>
			)

		case 'select':
		case 'select_currency':
		case 'gateway_select':
		case 'page_select': {
			const selectedValue = value ?? field.std ?? ''
			const options = Object.entries(field.options || {})
			return (
				<FieldWrapper field={field}>
					<select
						id={id}
						value={selectedValue}
						onChange={(e) => onChange(field.id, e.target.value)}
						className="regular-text"
					>
						{field.placeholder && <option value="">{field.placeholder}</option>}
						{options.map(([key, label]) => (
							<option key={key} value={key}>{label}</option>
						))}
					</select>
				</FieldWrapper>
			)
		}

		case 'checkbox':
			return (
				<FieldWrapper field={field}>
					<label className="flex items-center gap-2">
						<input
							id={id}
							type="checkbox"
							checked={!!value && value !== '0' && value !== '-1'}
							onChange={(e) => onChange(field.id, e.target.checked ? '1' : '0')}
						/>
						{field.label && <span className="text-sm text-card-foreground">{field.label}</span>}
					</label>
				</FieldWrapper>
			)

		case 'switch':
			return (
				<FieldWrapper field={field}>
					<label className="flex items-center gap-2 cursor-pointer">
						<Switch
							id={id}
							checked={!!(value && value !== '0')}
							onCheckedChange={(checked) => onChange(field.id, checked ? '1' : '0')}
						/>
						{field.label && <span className="text-sm text-card-foreground">{field.label}</span>}
					</label>
				</FieldWrapper>
			)

		case 'gateways': {
			const currentGateways = (typeof value === 'object' && value !== null) ? value : {}
			return (
				<FieldWrapper field={field}>
					<div className="flex flex-col gap-2">
						{Object.entries(field.options || {}).map(([key, label]) => (
							<label key={key} className="flex items-center gap-2 cursor-pointer">
								<input
									type="checkbox"
									checked={!!currentGateways[key]}
									onChange={(e) => {
										const updated = { ...currentGateways }
										if (e.target.checked) {
											updated[key] = 1
										} else {
											delete updated[key]
										}
										onChange(field.id, updated)
									}}
								/>
								<span className="text-sm text-card-foreground">{label}</span>
							</label>
						))}
					</div>
				</FieldWrapper>
			)
		}

		case 'descriptive_text':
			return (
				<tr>
					<td colSpan={2}>
						<p className="text-sm text-muted-foreground">{field.name}</p>
					</td>
				</tr>
			)

		default:
			return null
	}
}

// ─── Main Settings Page ───────────────────────────────────────────────────────

export const SettingsPage = () => {
	const [activeTab, setActiveTab] = useState(null)
	const [settings, setSettings] = useState({})
	const [schema, setSchema] = useState(null)
	const [isLoading, setIsLoading] = useState(true)
	const [isSaving, setIsSaving] = useState(false)
	const [isClearing, setIsClearing] = useState(false)
	const [loadError, setLoadError] = useState(null)

	const fetchSettings = useCallback(async () => {
		setIsLoading(true)
		setLoadError(null)
		try {
			const data = await GetSettings()
			setSettings(data.settings || {})
			setSchema(data.schema || null)
			if (data.schema?.tabs?.length) {
				setActiveTab((prev) => prev || data.schema.tabs[0].id)
			}
		} catch (err) {
			console.error('Failed to load settings', err)
			setLoadError(__('Failed to load settings. Please refresh and try again.', 'smartpay'))
		} finally {
			setIsLoading(false)
		}
	}, [])

	useEffect(() => {
		fetchSettings()
	}, [fetchSettings])

	const handleChange = (id, value) => {
		setSettings((prev) => ({ ...prev, [id]: value }))
	}

	const handleSave = async () => {
		setIsSaving(true)
		try {
			await UpdateSettings(settings)
			Swal.fire({
				toast: true,
				icon: 'success',
				title: __('Settings saved.', 'smartpay'),
				position: 'top-end',
				showConfirmButton: false,
				timer: 2000,
				showClass: { popup: 'swal2-noanimation' },
				hideClass: { popup: '' },
			})
		} catch (err) {
			Swal.fire({
				icon: 'error',
				title: __('Error', 'smartpay'),
				text: __('Failed to save settings.', 'smartpay'),
			})
		} finally {
			setIsSaving(false)
		}
	}

	const handleClearLog = async () => {
		setIsClearing(true)
		await ClearDebugLog()
		await fetchSettings()
		setIsClearing(false)
	}

	if (isLoading || !schema) {
		return (
			<>
				<Header title={__('Settings', 'smartpay')} subtitle={__('Configure WPSmartPay', 'smartpay')} />
				{loadError ? (
					<div className="p-4 max-w-5xl mx-auto">
						<Alert variant="destructive">
							<AlertDescription>{loadError}</AlertDescription>
						</Alert>
					</div>
				) : (
					<div className="p-8 text-center text-muted-foreground">{__('Loading…', 'smartpay')}</div>
				)}
			</>
		)
	}

	const currentTab = schema.tabs.find((t) => t.id === activeTab) || schema.tabs[0]

	return (
		<>
			<Header title={__('Settings', 'smartpay')} subtitle={__('Configure WPSmartPay', 'smartpay')} />

			<div className="p-4 max-w-5xl mx-auto">
				{/* Tab nav */}
				<div className="border-b border-border">
					<nav className="flex gap-0">
						{schema.tabs.map((tab) => (
							<button
								key={tab.id}
								onClick={() => setActiveTab(tab.id)}
								className={tabClass(tab.id === activeTab)}
							>
								{tab.label}
							</button>
						))}
					</nav>
				</div>

				{/* Tab content */}
				<Card className="rounded-t-none border-t-0 shadow-sm">
					<CardContent>
						<table className="form-table w-full">
							<tbody>
								{currentTab.fields.map((field) => (
									<SettingField
										key={field.id}
										field={field}
										value={settings[field.id]}
										onChange={handleChange}
									/>
								))}
							</tbody>
						</table>
					</CardContent>

					<CardFooter className="border-t border-border pt-4 gap-2">
						{activeTab !== 'debug_log' && (
							<Button variant="default" onClick={handleSave} disabled={isSaving}>
								{isSaving ? __('Saving…', 'smartpay') : __('Save Changes', 'smartpay')}
							</Button>
						)}
						{activeTab === 'debug_log' && (
							<Button variant="destructive" onClick={handleClearLog} disabled={isClearing}>
								{isClearing ? __('Clearing…', 'smartpay') : __('Clear Log', 'smartpay')}
							</Button>
						)}
					</CardFooter>
				</Card>
			</div>
		</>
	)
}
