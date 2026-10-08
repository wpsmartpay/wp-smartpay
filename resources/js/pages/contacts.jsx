import { __ } from '@wordpress/i18n'
import { usePageTab } from '../components/PageTabs'
import { CustomerList } from './customer/index'
import { DonorList } from './donor/index'

const TABS = [
	{ id: 'all',       label: __('All', 'smartpay') },
	{ id: 'customers', label: __('Customers', 'smartpay') },
	{ id: 'donors',    label: __('Donors', 'smartpay') },
]

/** Contacts: Customers + Donors in one page. */
export const Contacts = () => {
	const [tab, tabs] = usePageTab(TABS)
	return 'donors' === tab
		? <DonorList tabs={tabs} />
		: <CustomerList key={tab} all={'all' === tab} tabs={tabs} />
}
