import clsx from 'clsx'
import {
	CheckCircle2,
	Clock,
	RotateCcw,
	Sigma
} from 'lucide-react'

export default function StatCard({ title, value, type }) {
  const normalized = type?.toLowerCase()

  const typeMap = {
    success: {
      icon: CheckCircle2,
      color: 'bg-green-50 text-green-700 border-green-200',
    },
    danger: {
      icon: RotateCcw,
      color: 'bg-red-50 text-red-700 border-red-200',
    },
    warning: {
      icon: Clock,
      color: 'bg-yellow-50 text-yellow-800 border-yellow-200',
    },
    info: {
      icon: Sigma,
      color: 'bg-blue-50 text-blue-700 border-blue-200',
    },
  }

  const { icon: Icon, color } =
    typeMap[normalized] || typeMap.pending

	return (
		<div className={clsx("p-4 rounded-lg flex-1 shadow-sm flex items-center justify-center text-center flex-col", color)}>
			<span className="text-2xl font-semibold mt-2">{value}</span>
			<span className="text-sm flex items-center justify-center gap-1"> <Icon className="w-3.5 h-3.5" /> {title}</span>
		</div>
	)
}
