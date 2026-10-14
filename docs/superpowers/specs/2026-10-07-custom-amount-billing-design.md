# Custom Amount Billing (replaces Giving Frequency)

Date: 2026-10-07 · Branch: `feature/donation-campaigns` · Status: built (not committed)

## Why

The Giving Frequency block (unreleased) and Pricing Option both write the same hidden
`smartpay_form_billing_type` / `smartpay_form_billing_period` inputs, with no rule for who wins.
Reproduced in the browser on form 234:

1. A $9/month option with "One-time" selected still bills monthly.
2. A yearly option with "Monthly" selected becomes the yearly amount every month.
3. A one-time option with "Monthly" selected keeps a stale `amount_key` and picks up another option's setup fee and cycle limit.

Pricing Option already covers fixed one-time and fixed recurring amounts. The only thing the
frequency block added that Pricing Option could not do is a **recurring custom amount**. So
the block is removed and that capability moves into the Pricing block's custom amount.

## Decisions

| Question | Decision |
|---|---|
| Who picks the custom amount's period | The donor, on the form, only when the admin allows it |
| Which periods the donor sees | The admin ticks them (Daily / Weekly / Monthly / Yearly); default Monthly + Yearly |
| Existing forms with the frequency block | Block is unreleased, so it is deleted entirely. No migration |
| Short-period gateway mapping bug (`year` → Stripe `month`) | Out of scope, separate task. The new dropdown sends long names so it is not affected |

## 1. Remove Giving Frequency

- `resources/form-builder/blocks/Donation/index.js` registers several donation blocks: remove only the `donation-frequency` registration (and its editor styles). If the file then holds nothing else, delete it and its import in `blocks/index.js`.
- `app/Modules/Campaign/DonationFields.php`: remove `render_frequency` and its `donation-frequency` hook. Keep the other donation blocks, the receipt rows, and `store_donation_fields`.
- `store_donation_fields` keeps working out `extra.donation.frequency` from the billing type that was actually charged. The Pro guard that downgrades a subscription without Pro stays.
- `resources/js/frontend/payment/form.js`: remove the `donation.frequency === 'monthly'` override in `getPaymentFormData`.
- `resources/js/frontend/payment/donation.js`: remove the pay-button "/month" label logic. Keep the steps logic.
- Charity template: remove `donation-frequency` from `NativeForm.php` (`tpl_block` list) and `resources/js/admin/native-forms/templates.js`.
- `resources/js/admin/form-editor-sidebar/index.js`: remove it from the field list and `UNIQUE_BLOCKS`.
- Docs: `DOCS-UPDATE-donations-campaigns.md` replaces the Giving Frequency section with Custom Amount Billing.

Reporting stays as it is: donor badges, campaign reports, the giving history and receipts read the billing type, not the block.

## 2. Editor: Pricing block Custom Amount panel

New attributes on `smartpay-form/pricing` (parent):

| Attribute | Type | Default |
|---|---|---|
| `customBillingMode` | string | `'one_time'` (`'donor'` = let the donor choose) |
| `customBillingPeriods` | array | `['Monthly', 'Yearly']` |

The panel appears under the existing "Allow custom amount" toggle and is only shown when that toggle is on:

- Radio "Billing for custom amount": **One time only** / **Let donor choose**.
- "Let donor choose" is Pro-locked using the same `readProFlag()` and upgrade-notice pattern as the Subscription billing type in `option/edit.js`.
- When the mode is `donor`: four checkboxes (Daily, Weekly, Monthly, Yearly). At least one must stay ticked; unticking the last one is ignored.
- The editor preview shows the dropdown when the mode is `donor`.

## 3. Front end: saved markup (`PricingField/save.js`)

- When `allowCustomAmount && customBillingMode === 'donor'`, render inside the custom amount `.input-group`, after the text input:
  `<select class="form-control smartpay-custom-billing-period" name="smartpay_custom_billing_period">`
  with `<option value="">One time</option>` followed by one option per ticked period (`value` = `Daily|Weekly|Monthly|Yearly`).
- Otherwise the output is **byte-identical** to the current save. Existing forms therefore get no block-validation errors and no deprecation entry is needed.
- The period values are the long names (the `Subscription::BILLING_PERIOD_*` constants) that every gateway helper maps correctly.

## 4. Front-end behavior (`resources/js/frontend/payment/form.js`)

One helper, `applyCustomBilling($amounts)`, runs on custom-amount `focus`/`input` and on `change` of `.smartpay-custom-billing-period`:

- Select present with a non-empty value: set `smartpay_form_billing_type` = `Subscription` and `smartpay_form_billing_period` = that value.
- Otherwise: `smartpay_form_billing_type` = `One Time` (the period input is left alone; it is only sent for subscriptions).
- Changing the select also counts as choosing the custom amount: it marks the custom input selected, unchecks the cards and sets `smartpay_is_custom_payment` = `true`, the same as focusing the input.

Card clicks are unchanged: the existing handler sets the type and period from the card. The
custom-focus handler also fixes the current hidden bug where a custom amount inherits the last
card's Subscription type.

## 5. Server

**Meta sync** (`NativeForm` block → meta sync, next to `_smartpay_amounts`): store
`_smartpay_custom_billing` = `{"mode": "one_time"|"donor", "periods": [...]}`. Periods are filtered to
the four allowed values. Without Pro, or with no custom amount, or with an empty list, the mode is
`one_time`.

**Payment filter** (new, in free, `smartpay_prepare_payment_data` priority **15**, after Pro's
`subscriptionPaymentData` at 10): only for `form_payment` where the payment is a custom amount.
The flag is read from the raw post as
`filter_var( $raw['smartpay_is_custom_amount'] ?? false, FILTER_VALIDATE_BOOLEAN )`. The browser sends the
string `"false"`, which is truthy in PHP; `payment_data.is_custom_amount` is only a real boolean on custom-post-type
forms (`NativeForm::fix_cpt_form_payment_data`), and legacy table forms keep the raw string (`Payment.php:283`).
A truthiness check would treat every card subscription as custom and downgrade it. A
subscription is kept only when all of these hold:

- Pro is active
- the form's `_smartpay_custom_billing.mode` is `donor`
- the posted `smartpay_form_billing_period` is in the stored `periods`

Otherwise the payment is downgraded to `One Time` and `billing_period` is removed from
`$data['billing_type']`, `$data['billing_period']`, `payment_data.billing_type` and
`payment_data.billing_period`, mirroring the existing downgrade in `DonationFields::store_donation_fields`.

Unchanged: payments made with a card (non-custom) are not validated by this filter; the setup fee and
billing cycles stay off for custom amounts (`Payment.php:287`).

## 6. Verification

- A PHP self-check (assert-based, run with `wp eval-file` in the container) for the payment filter
  covering: allowed period kept, period not ticked → one time, mode `one_time` → one time,
  no Pro → one time, non-custom payment untouched, and a card subscription posted with
  `smartpay_is_custom_amount = "false"` (string) stays a subscription.
- Browser on the local site (request intercepted, nothing charged):
  - Existing cards on form 234 send the same payloads as before.
  - Custom amount, no dropdown → `One Time`, even after a subscription card was clicked first.
  - Custom amount + Monthly → `Subscription` / `Monthly`; + One time → `One Time`.
  - Existing forms (234, 221, Charity template) open in the editor with no block-validation warning.
- `npm run build` is clean.
- The free plugin has no PHPUnit suite, so the PHP self-check above stands in for the tests CLAUDE.md asks for.

## Docs to update (per CLAUDE.md)

- `DOCS-UPDATE-donations-campaigns.md` (user docs draft)
- `../wp-smartpay-pro/docs/features-and-roadmap.md` and `codebase-reference.md`: frequency block removed, custom amount billing added, new `_smartpay_custom_billing` meta and payment filter

## Out of scope

- Short-period mapping bug (`day/week/month/year` vs gateway long names) for Pricing Options.
- Server-side validation of card payments against `_smartpay_amounts`.
- Reports that say "Monthly" for any recurring gift.
