import { __ } from '@wordpress/i18n'
import { usePageTab } from '../components/PageTabs'
import { PaymentList } from './payment/index'

const TABS = [
	{ id: 'all',       label: __('All', 'smartpay') },
	{ id: 'payments',  label: __('Payments', 'smartpay') },
	{ id: 'donations', label: __('Donations', 'smartpay') },
]

/** Transactions: Payments + Donations in one page. */
export const Transactions = () => {
	const [tab, tabs] = usePageTab(TABS)
	return <PaymentList key={tab} mode={tab} tabs={tabs} />
}
