import { Badge } from '@/components/ui/badge'
import clsx from 'clsx'
import {
	AlertTriangle,
	Ban,
	CheckCircle2,
	Clock,
	Loader2,
	RotateCcw,
	XCircle
} from 'lucide-react'

export function StatusBadge({ status }) {
  const normalized = status?.toLowerCase()

  const statusMap = {
    completed: {
      icon: CheckCircle2,
      color: 'bg-green-50 text-green-700 border-green-200',
      label: 'Completed',
    },
    refunded: {
      icon: RotateCcw,
      color: 'bg-blue-50 text-blue-700 border-blue-200',
      label: 'Refunded',
    },
    pending: {
      icon: Clock,
      color: 'bg-yellow-50 text-yellow-800 border-yellow-200',
      label: 'Pending',
    },
    failed: {
      icon: XCircle,
      color: 'bg-red-50 text-red-700 border-red-200',
      label: 'Failed',
    },
    abandoned: {
      icon: AlertTriangle,
      color: 'bg-gray-50 text-gray-700 border-gray-200',
      label: 'Abandoned',
    },
    processing: {
      icon: Loader2,
      color: 'bg-purple-50 text-purple-700 border-purple-200',
      label: 'Processing',
    },
    revoked: {
      icon: Ban,
      color: 'bg-orange-50 text-orange-700 border-orange-200',
      label: 'Revoked',
    },
  }

  const { icon: Icon, color, label } =
    statusMap[normalized] || statusMap.pending

  return (
    <Badge
      variant="outline"
      className={clsx(
        'flex items-center min-w-30 justify-center gap-1.5 px-2 py-1 rounded-full border text-xs font-medium',
        color
      )}
    >
      <Icon className="w-3.5 h-3.5" />
      {label}
    </Badge>
  )
}
