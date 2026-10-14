import { useSearchParams } from 'react-router-dom'

/**
 * Tabs in the Subscriptions style (`sp-tabs__nav`). The active tab lives in
 * `?tab=` so a reload or a shared link opens the same tab.
 *
 * @param {{ id: string, label: string }[]} tabs First tab is the default.
 * @returns {[string, JSX.Element]} Active tab id and the tab bar.
 */
export const usePageTab = (tabs) => {
	const [params, setParams] = useSearchParams()
	const active = tabs.some((t) => t.id === params.get('tab')) ? params.get('tab') : tabs[0].id

	const nav = (
		<div className="sp-tabs__nav" role="tablist">
			{tabs.map((t) => (
				<button key={t.id} type="button" role="tab" aria-selected={active === t.id}
					className={active === t.id ? 'sp-tabs__item sp-tabs__item--active' : 'sp-tabs__item'}
					onClick={() => setParams({ tab: t.id }, { replace: true })}>
					{t.label}
				</button>
			))}
		</div>
	)

	return [active, nav]
}
