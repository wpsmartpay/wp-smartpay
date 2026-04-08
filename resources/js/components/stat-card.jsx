import { TrendingUp, TrendingDown } from 'lucide-react'
import clsx from 'clsx'

/**
 * StatCard — shadcn-style metric card.
 *
 * Props:
 *   title   {string}          — Metric label (e.g. "Total Revenue")
 *   value   {string|number}   — Formatted value (e.g. "$45,231.89")
 *   change  {string}          — Trend string (e.g. "+20.1%" or "-3.58%"). Optional.
 *   trend   {"up"|"down"}     — Direction; controls colour. Optional.
 *   icon    {Component}       — Lucide-react component reference. Optional.
 *   period  {string}          — Sub-label below change (e.g. "from last month"). Optional.
 */
export function StatCard({ title, value, change, trend, icon: Icon, period }) {
    const isUp   = trend === 'up'
    const isDown = trend === 'down'

    return (
        <div className="bg-card text-card-foreground rounded-xl border border-border p-5 flex flex-col gap-3 shadow-sm">
            {/* Top row: title + icon */}
            <div className="flex items-start justify-between gap-2">
                <span className="text-sm font-medium text-muted-foreground leading-tight">
                    {title}
                </span>
                {Icon && (
                    <span className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-muted/50">
                        <Icon className="w-4 h-4 text-muted-foreground" />
                    </span>
                )}
            </div>

            {/* Value */}
            <span className="text-2xl font-bold tracking-tight text-card-foreground leading-none">
                {value}
            </span>

            {/* Trend row */}
            {(change || period) && (
                <div className="flex items-center gap-1.5 text-xs">
                    {change && (
                        <span
                            className={clsx(
                                'inline-flex items-center gap-0.5 font-semibold',
                                isUp   && 'text-green-600',
                                isDown && 'text-red-500',
                                !isUp && !isDown && 'text-muted-foreground',
                            )}
                        >
                            {isUp   && <TrendingUp   className="w-3 h-3" />}
                            {isDown && <TrendingDown className="w-3 h-3" />}
                            {change}
                        </span>
                    )}
                    {period && (
                        <span className="text-muted-foreground">{period}</span>
                    )}
                </div>
            )}
        </div>
    )
}
