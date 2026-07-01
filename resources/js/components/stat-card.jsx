/**
 * StatCard — shadcn dashboard-style metric card.
 *
 * Props:
 *   title   {string}         — Metric label (e.g. "Total Revenue")
 *   value   {string|number}  — Formatted display value (e.g. "$45,231.89")
 *   change  {string}         — Optional trend/period line (e.g. "+20.1% from last month")
 *   icon    {Component}      — Lucide-react component reference (not JSX element)
 */
export function StatCard({ title, value, change, icon: Icon }) {
    return (
        <div className="bg-card text-card-foreground rounded-xl border border-border p-6 flex flex-col gap-2 shadow-sm">
            {/* Top row: label + icon */}
            <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">
                    {title}
                </span>
                {Icon && (
                    <Icon className="h-4 w-4 text-muted-foreground" />
                )}
            </div>

            {/* Value */}
            <span className="text-3xl font-bold tracking-tight text-card-foreground">
                {value}
            </span>

            {/* Trend / period line */}
            {change && (
                <span className="text-xs text-primary font-medium">
                    {change}
                </span>
            )}
        </div>
    )
}
