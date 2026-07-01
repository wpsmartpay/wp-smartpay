import { __ } from '@wordpress/i18n'
import { GetIntegrations, ToggleIntegration } from '../http/integration'

const { useState, useEffect, useCallback } = wp.element
const { Header, Card, CardContent, CardFooter, Button, Switch, Alert, AlertDescription } = window.WPSmartPayUI

// ─── Integration Card ─────────────────────────────────────────────────────────

const IntegrationCard = ({ integration, onToggle }) => {
	const [isToggling, setIsToggling] = useState(false)

	const handleToggle = async (checked) => {
		setIsToggling(true)
		try {
			await onToggle(integration.namespace, checked)
		} finally {
			setIsToggling(false)
		}
	}

	return (
		<Card>
			<CardContent className="flex flex-col gap-3">
				{/* Logo */}
				<div className="flex items-center justify-center h-16">
					<img
						src={integration.cover}
						alt={integration.name}
						className="max-h-full max-w-full object-contain"
					/>
				</div>

				{/* Info */}
				<div className="flex-1">
					<p className="font-semibold text-sm text-card-foreground">
						{integration.name}
					</p>
					<p className="text-xs text-muted-foreground mt-1 leading-relaxed">
						{integration.excerpt}
					</p>
				</div>

				{/* Categories */}
				{integration.categories?.length > 0 && (
					<div className="flex flex-wrap gap-1">
						{integration.categories.map((cat) => (
							<span
								key={cat}
								className="text-xs px-2 py-0.5 rounded-full bg-muted text-muted-foreground"
							>
								{cat}
							</span>
						))}
					</div>
				)}
			</CardContent>

			<CardFooter className="border-t border-border pt-3 justify-between">
				{integration.is_installed ? (
					<>
						<div className="flex items-center gap-2">
							<Switch
								checked={integration.is_active}
								onCheckedChange={handleToggle}
								disabled={isToggling}
								aria-label={
									integration.is_active
										? __('Deactivate', 'smartpay')
										: __('Activate', 'smartpay')
								}
							/>
							<span className="text-xs text-muted-foreground">
								{integration.is_active
									? __('Activated', 'smartpay')
									: __('Disabled', 'smartpay')}
							</span>
						</div>
						{integration.is_active && integration.setting_link && (
							<Button variant="ghost" size="icon-sm" asChild>
								<a
									href={`${smartpay.adminUrl}?page=smartpay#/settings`}
									title={__('Settings', 'smartpay')}
								>
									<span className="dashicons dashicons-admin-settings" />
								</a>
							</Button>
						)}
					</>
				) : (
					<Button variant="outline" size="sm" asChild>
						<a
							href="https://wpsmartpay.com"
							target="_blank"
							rel="noopener noreferrer"
						>
							{__('Upgrade to Pro', 'smartpay')}
						</a>
					</Button>
				)}
			</CardFooter>
		</Card>
	)
}

// ─── Integrations Page ────────────────────────────────────────────────────────

export const IntegrationsPage = () => {
	const [integrations, setIntegrations] = useState([])
	const [isLoading, setIsLoading] = useState(true)
	const [error, setError] = useState(null)

	const fetchIntegrations = useCallback(async () => {
		setIsLoading(true)
		setError(null)
		try {
			const data = await GetIntegrations()
			setIntegrations(data || [])
		} catch (err) {
			console.error('Failed to load integrations', err)
			setError(__('Failed to load integrations. Please refresh and try again.', 'smartpay'))
		} finally {
			setIsLoading(false)
		}
	}, [])

	useEffect(() => {
		fetchIntegrations()
	}, [fetchIntegrations])

	const handleToggle = async (namespace, active) => {
		// Optimistic update
		setIntegrations((prev) =>
			prev.map((i) => (i.namespace === namespace ? { ...i, is_active: active } : i))
		)
		try {
			await ToggleIntegration(namespace, active)
		} catch (err) {
			console.error('Failed to toggle integration', err)
			// Revert on failure
			setIntegrations((prev) =>
				prev.map((i) => (i.namespace === namespace ? { ...i, is_active: !active } : i))
			)
		}
	}

	return (
		<>
			<Header
				title={__('Integrations', 'smartpay')}
				subtitle={__('Manage your integrations here', 'smartpay')}
			/>

			<div className="sp-layout">

				<div className="sp-page-title__inner">
					<h1 className="sp-page-title__heading">{__('Integrations', 'smartpay')}</h1>
					<p className="sp-page-title__sub">{__('Connect SmartPay with your favourite tools', 'smartpay')}</p>
				</div>

				{isLoading && (
					<div className="sp-state-loading">
						{__('Loading…', 'smartpay')}
					</div>
				)}

				{error && !isLoading && (
					<Alert variant="destructive">
						<AlertDescription>{error}</AlertDescription>
					</Alert>
				)}

				{!isLoading && !error && (
					<div className="sp-grid sp-grid--cards">
						{integrations.map((integration) => (
							<IntegrationCard
								key={integration.namespace}
								integration={integration}
								onToggle={handleToggle}
							/>
						))}
					</div>
				)}
			</div>
		</>
	)
}
