/**
 * Donation form behaviour: Step Break blocks become steps, and the pay button
 * shows the recurring amount when the donor picks monthly giving
 * ("Give Now · $50/month"). Server markup: app/Modules/Campaign/DonationFields.php.
 */
jQuery(($) => {
    const { __, sprintf } = window.wp?.i18n || { __: (s) => s, sprintf: (s, ...a) => a.reduce((t, v) => t.replace(/%[sd]/, v), s) }

    const decode = (str) => {
        const t = document.createElement('textarea')
        t.innerHTML = str || ''
        return t.value
    }

    /* ── Monthly: show the recurring amount on the pay button ───────────── */

    const refreshPayLabel = ($form) => {
        const $label = $form.find('.smartpay-form-pay-now > span').first()
        const $target = $label.length ? $label : $form.find('.smartpay-form-pay-now').first()
        if (!$target.length) return

        if (undefined === $target.data('sp-label')) $target.data('sp-label', $target.text().trim())
        const base = $target.data('sp-label')
        const monthly = 'monthly' === $form.find('input[name="smartpay_form[donation][frequency]"]:checked').val()
        const amount = parseFloat($form.find('[name="smartpay_form_amount"]').val())

        if (!monthly) {
            $target.text(base)
            return
        }

        const symbol = decode(window.smartpay?.options?.currencySymbol) || '$'
        $target.text(
            amount > 0
                ? sprintf(__('%1$s · %2$s/month', 'smartpay'), base, `${symbol}${amount.toLocaleString()}`)
                : sprintf(__('%s monthly', 'smartpay'), base)
        )
    }

    $(document.body).on(
        'change input click',
        '.smartpay-payment-form input[name="smartpay_form[donation][frequency]"], .smartpay-payment-form [name="smartpay_form_amount"], .smartpay-payment-form .form-plan-card',
        function () {
            const $form = $(this).closest('form')
            // Amount cards update the amount input on the same click; read it after.
            setTimeout(() => refreshPayLabel($form), 0)
        }
    )

    /* ── Steps ───────────────────────────────────────────────────────────── */

    const validStep = ($step) => {
        let ok = true
        $step.find('input, select, textarea').filter(':visible').each(function () {
            if (ok && typeof this.checkValidity === 'function' && !this.checkValidity()) {
                this.reportValidity()
                ok = false
            }
        })
        return ok
    }

    const buildSteps = ($form) => {
        // Split layout already arranges fields beside the payment column.
        if ($form.closest('.smartpay-form-shortcode--split').length) return

        const $breaks = $form.children('.smartpay-step-break')
        if (!$breaks.length) return

        const groups = [{ nodes: [], next: '' }]
        $form.children().each(function () {
            if ($(this).hasClass('smartpay-step-break')) {
                groups[groups.length - 1].next = $(this).data('next-label') || __('Continue', 'smartpay')
                groups.push({ nodes: [], title: $(this).data('step-title') || '' })
                $(this).remove()
                return
            }
            groups[groups.length - 1].nodes.push(this)
        })

        // Hidden inputs, nonce and response containers stay outside the steps.
        const keepOutside = (el) => el.tagName === 'INPUT' && el.type === 'hidden'
        const steps = groups.filter((g) => g.nodes.some((n) => !keepOutside(n)))
        if (steps.length < 2) return

        const $bar = $('<div class="smartpay-steps" aria-hidden="true"></div>')
        steps.forEach(() => $bar.append('<i></i>'))
        $form.prepend($bar)

        const $panes = steps.map((step, i) => {
            const $pane = $('<div class="smartpay-step" role="group"></div>')
                .attr('aria-label', sprintf(__('Step %1$d of %2$d', 'smartpay'), i + 1, steps.length))
            if (step.title) $pane.append($('<h3 class="smartpay-step__title"></h3>').text(step.title))
            step.nodes.filter((n) => !keepOutside(n)).forEach((n) => $pane.append(n))

            const $nav = $('<div class="smartpay-step__nav"></div>')
            if (i > 0) {
                $nav.append($('<button type="button" class="smartpay-step__back"></button>').text(__('Back', 'smartpay')))
            }
            if (i < steps.length - 1) {
                $nav.append($('<button type="button" class="smartpay-step__next"></button>').text(step.next))
            }
            $pane.append($nav)
            $form.append($pane)
            return $pane
        })

        let current = 0
        const show = (i) => {
            current = i
            $panes.forEach(($p, idx) => $p.toggle(idx === i))
            $bar.children().each((idx, el) => $(el).toggleClass('is-on', idx <= i))
            if (i > 0) $panes[i].find('input, select, textarea, button').filter(':visible').first().trigger('focus')
        }

        $form.on('click', '.smartpay-step__next', () => {
            if (validStep($panes[current])) show(Math.min(current + 1, $panes.length - 1))
        })
        $form.on('click', '.smartpay-step__back', () => show(Math.max(current - 1, 0)))

        show(0)
    }

    $('.smartpay-payment-form').each(function () {
        buildSteps($(this))
        refreshPayLabel($(this))
    })
})
