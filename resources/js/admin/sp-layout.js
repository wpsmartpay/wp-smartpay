/**
 * SmartPay Layout Utilities
 *
 * Handles: checkbox select-all, bulk bar, row-action dropdowns.
 * Vanilla JS — no framework dependency. Import once per SPA or page.
 *
 * Usage:
 *   import { initSpLayout } from './sp-layout'
 *   initSpLayout()           // call after table renders
 *   initSpLayout(rootEl)     // scoped to a container
 */

/**
 * Wire up select-all, row checkboxes, and bulk bar inside `root`.
 *
 * @param {HTMLElement} [root=document]
 */
export function initSpCheckboxes(root = document) {
    const selectAll = root.querySelector('.sp-select-all')
    const rowBoxes  = () => [...root.querySelectorAll('.sp-row-check')]
    const bulkBar   = root.querySelector('.sp-bulk-bar')
    const bulkCount = root.querySelector('.sp-bulk-bar__count')

    if (!selectAll) return

    function updateBulkBar() {
        const checked = rowBoxes().filter(cb => cb.checked)
        const total   = rowBoxes().length

        // Sync select-all indeterminate state
        selectAll.indeterminate = checked.length > 0 && checked.length < total
        selectAll.checked       = checked.length === total && total > 0

        // Highlight selected rows
        rowBoxes().forEach(cb => {
            const row = cb.closest('tr')
            if (row) row.classList.toggle('sp-row--selected', cb.checked)
        })

        // Show/hide bulk bar
        if (bulkBar) {
            bulkBar.classList.toggle('sp-bulk-bar--visible', checked.length > 0)
        }
        if (bulkCount) {
            bulkCount.textContent = checked.length > 0
                ? `${checked.length} selected`
                : ''
        }
    }

    selectAll.addEventListener('change', () => {
        rowBoxes().forEach(cb => { cb.checked = selectAll.checked })
        updateBulkBar()
    })

    root.addEventListener('change', e => {
        if (e.target.classList.contains('sp-row-check')) updateBulkBar()
    })

    // Initial state
    updateBulkBar()
}

/**
 * Wire up row-action dropdowns (.sp-row-actions) inside `root`.
 * Click trigger → toggle dropdown. Click outside → close all.
 *
 * @param {HTMLElement} [root=document]
 */
export function initSpDropdowns(root = document) {
    root.addEventListener('click', e => {
        const trigger = e.target.closest('.sp-row-actions__trigger')

        // Close all open dropdowns
        root.querySelectorAll('.sp-row-actions--open').forEach(el => {
            if (el !== trigger?.closest('.sp-row-actions')) {
                el.classList.remove('sp-row-actions--open')
                el.querySelector('.sp-dropdown')?.classList.remove('sp-dropdown--open')
            }
        })

        if (!trigger) return

        e.stopPropagation()
        const wrapper  = trigger.closest('.sp-row-actions')
        const dropdown = wrapper?.querySelector('.sp-dropdown')
        if (!wrapper || !dropdown) return

        const isOpen = wrapper.classList.contains('sp-row-actions--open')
        wrapper.classList.toggle('sp-row-actions--open', !isOpen)
        dropdown.classList.toggle('sp-dropdown--open', !isOpen)
    })

    // Close on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.sp-row-actions--open').forEach(el => {
            el.classList.remove('sp-row-actions--open')
            el.querySelector('.sp-dropdown')?.classList.remove('sp-dropdown--open')
        })
    })

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.sp-row-actions--open').forEach(el => {
                el.classList.remove('sp-row-actions--open')
                el.querySelector('.sp-dropdown')?.classList.remove('sp-dropdown--open')
            })
        }
    })
}

/**
 * Init everything. Call after table mounts or re-renders.
 *
 * @param {HTMLElement} [root=document]
 */
export function initSpLayout(root = document) {
    initSpCheckboxes(root)
    initSpDropdowns(root)
}

export default initSpLayout
