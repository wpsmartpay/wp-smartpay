import { __ } from '@wordpress/i18n'
import { chartBar } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'
import './editor.scss'

/**
 * Goal Progress — shows the form's sales / fundraising goal progress.
 *
 * Editor-side configurator: save() renders nothing. The live counts are
 * dynamic, so the frontend markup is produced by the
 * `render_block_smartpay-form/goal-progress` filter (see NativeForm), which
 * reads this block's style attributes + smartpay_calculate_goal_progress().
 * The goal itself (target, type, goal-met message) is configured in
 * Form Settings → Goal; this block only controls where it appears and how it
 * looks. Place it anywhere in the form body.
 */
export const GoalProgress = {
    namespace: 'smartpay-form/goal-progress',
    settings: {
        title: __('Goal Progress', 'smartpay'),
        description: __(
            'Shows the form goal progress bar. Configure the goal in Form Settings → Goal.',
            'smartpay'
        ),
        icon: chartBar,
        keywords: ['goal', 'progress', 'fundraising', 'target', 'thermometer', 'bar'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            // display toggles
            showBar: { type: 'boolean', default: true },
            showCounts: { type: 'boolean', default: true },
            showPercentage: { type: 'boolean', default: true },
            showMessage: { type: 'boolean', default: true },
            // custom progress message — tokens {current} {target} {percent} {unit}
            messageTemplate: { type: 'string', default: '' },
            // colors
            bgColor: { type: 'string', default: '#f8f9fa' },
            barColor: { type: 'string', default: '#28a745' },
            trackColor: { type: 'string', default: '#e9ecef' },
            textColor: { type: 'string', default: '#555555' },
            // styles / spacing / typography
            barHeight: { type: 'number', default: 12 },
            barRadius: { type: 'number', default: 4 },
            cardRadius: { type: 'number', default: 8 },
            padding: { type: 'number', default: 16 },
            fontSize: { type: 'number', default: 14 },
            // editor-only mock fill
            previewPercent: { type: 'number', default: 35 },
        },
        edit,
        save,
    },
}
