import { __ } from '@wordpress/i18n';
import { Button, SelectControl, TextControl } from '@wordpress/components';

const AmountCard = ({ amount, onRemove, onUpdate, canRemove }) => {
	const billingTypes = [
		{ label: __('One Time', 'smartpay'), value: 'One Time' },
		{ label: __('Subscription', 'smartpay'), value: 'Subscription' },
	];

	const billingPeriods = [
		{ label: __('Daily', 'smartpay'), value: 'day' },
		{ label: __('Weekly', 'smartpay'), value: 'week' },
		{ label: __('Monthly', 'smartpay'), value: 'month' },
		{ label: __('Yearly', 'smartpay'), value: 'year' },
	];

	return (
		<div className="sp-card sp-amount-card">
			<div className="card-header">
				<span className="sp-label">{__('Amount Label', 'smartpay')}</span>
				{canRemove && (
					<Button
						isSmall
						variant="link"
						isDestructive
						onClick={onRemove}
						className="remove-btn"
					>
						{__('Remove', 'smartpay')}
					</Button>
				)}
			</div>
			<TextControl
				className="sp-input"
				value={amount.label}
				onChange={(val) => onUpdate({ label: val })}
				placeholder={__('e.g. Basic Plan', 'smartpay')}
			/>

			<div className="card-row">
				<div className="card-col">
					<span className="sp-label">{__('Amount', 'smartpay')}</span>
					<TextControl
						className="sp-input"
						type="number"
						step="0.01"
						value={amount.amount}
						onChange={(val) => onUpdate({ amount: val })}
					/>
				</div>
				<div className="card-col">
					<span className="sp-label">{__('Billing Type', 'smartpay')}</span>
					<SelectControl
						className="sp-input"
						options={billingTypes}
						value={amount.billing_type}
						onChange={(val) => onUpdate({ billing_type: val })}
					/>
				</div>
			</div>

			{amount.billing_type === 'Subscription' && (
				<div className="subscription-fields">
					<div className="card-row">
						<div className="card-col">
							<span className="sp-label">{__('Billing Period', 'smartpay')}</span>
							<SelectControl
								className="sp-input"
								options={billingPeriods}
								value={amount.billing_period || 'month'}
								onChange={(val) => onUpdate({ billing_period: val })}
							/>
						</div>
						<div className="card-col">
							<span className="sp-label">{__('Setup Fee', 'smartpay')}</span>
							<TextControl
								className="sp-input"
								type="number"
								step="0.01"
								value={amount.setup_fee || '0.00'}
								onChange={(val) => onUpdate({ setup_fee: val })}
							/>
						</div>
					</div>
					<div className="card-row">
						<div className="card-col">
							<span className="sp-label">{__('Billing Cycles', 'smartpay')}</span>
							<TextControl
								className="sp-input"
								type="number"
								value={amount.billing_cycle || ''}
								onChange={(val) => onUpdate({ billing_cycle: val })}
								placeholder={__('Unlimited', 'smartpay')}
							/>
						</div>
					</div>
				</div>
			)}
		</div>
	);
};

export default AmountCard;
