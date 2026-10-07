/**
 * Periods a donor can pick for a custom amount. Values are the long names
 * (Subscription::BILLING_PERIOD_*) every gateway helper maps. Labels stay
 * untranslated because they are baked into the static save markup.
 */
export const CUSTOM_BILLING_PERIODS = [
    { value: 'Daily', label: 'Daily' },
    { value: 'Weekly', label: 'Weekly' },
    { value: 'Monthly', label: 'Monthly' },
    { value: 'Yearly', label: 'Yearly' },
]
