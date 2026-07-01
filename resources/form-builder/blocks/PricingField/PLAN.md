# Pricing Block — Build Plan (WP SmartPay)

**Status:** planned, not built. This dir (`PricingField/`) is the home.
**Owner plugin:** `smartpay` (free / core) — *everything* lives here.
**Pro plugin:** flips one filter to unlock Subscription. No other pro code.

---

## 1. Concept

Compound Gutenberg block modelled on `core/buttons` → `core/button`:

```
smartpay/pricing            (PARENT — container, custom-amount toggle)
└── smartpay/pricing-option (CHILD  — one selectable price card)   × N
```

- Visitor sees price cards (radio group); exactly **one** selectable (Image #4).
- Optional "Enter custom amount" input below the cards (parent toggle).
- Each option carries: `label, amount, billing_type, billing_period, setup_fee, billing_cycle`.
- **Billing Type = Subscription is pro-locked.** Without pro: disabled + "Available in Pro" CTA → website. With pro: fully functional.
- **Block is source of truth.** On form save, options sync into post meta `_smartpay_amounts` (the trusted copy checkout validates against).

---

## 2. Why this shape (verified against codebase)

| Fact | Source |
|---|---|
| Forms are a CPT; amounts stored in post meta `_smartpay_amounts` (JSON) | `app/Modules/NativeForm/NativeForm.php:68,317` |
| Checkout trust boundary: server matches `smartpay_amount_key` against `$form->amounts` | `app/Modules/Payment/Payment.php:274` |
| Submit field names | `smartpay_amount_key`, `smartpay_amount`, `smartpay_is_custom_amount`, `smartpay_form_billing_type` |
| Form-builder blocks = JS-registered trio (`index.js`/`edit.js`/`save.js`), static save, no `block.json` | `resources/form-builder/blocks/*/` |
| Amount data shape (label/amount/billing_type + sub fields) | `resources/js/admin/form-editor/components/sidebar/AmountCard.jsx` |
| Pro→free defaults hook already exists | `smartpay.form.amounts.defaultValue` |
| No `smartpay_is_pro_active()` gate yet — must create | grep: none found |
| Pro-lock pattern | `.agent/skills/pro-lock.md` (pro repo) |
| Compound parent/child pattern (appender, attributesToCopy, splitting) | `.agent/skills/wp-block-development` + core/buttons teardown |

---

## 3. Files to create (all in free `smartpay/`)

```
resources/form-builder/blocks/PricingField/
├── index.js          parent: registerBlockType('smartpay/pricing')
├── edit.js           parent edit: InnerBlocks + custom-amount toggle
├── save.js           parent save: InnerBlocks.Content + custom-amount markup
├── option/
│   ├── index.js      child: registerBlockType('smartpay/pricing-option')
│   ├── edit.js       child edit: inline label/amount + sidebar billing controls + pro-lock
│   └── save.js       child save: radio card markup + hidden inputs
└── editor.scss / style.scss   (or reuse existing sass/block-editor pipeline)

resources/form-builder/blocks/index.js   ← import + register both
app/Modules/NativeForm/NativeForm.php     ← block→_smartpay_amounts sync on save
app/Helpers/ (or helpers.php)             ← smartpay_is_pro_active() + smartpay_pro_feature_available()
admin enqueue                             ← localize window.smartpayFormBuilder.isPro
```

Pro repo (`smartpay-pro/`), one addition only:
```
SmartPayProServiceProvider::boot() (inside valid-licence block)
  add_filter('smartpay_is_pro_active', '__return_true');
```

---

## 4. Block specs

### Parent `smartpay/pricing`
```js
registerBlockType('smartpay/pricing', {
  title: 'Pricing', category: 'smartpay', icon: 'money-alt',
  apiVersion: 3,
  attributes: {
    enableCustomAmount: { type: 'boolean', default: false },
    customAmountLabel:  { type: 'string',  default: 'Enter custom amount' },
    columns:            { type: 'number',  default: 2 },
  },
  // allowedBlocks via InnerBlocks prop
})
```
**edit.js**
```jsx
const inner = useInnerBlocksProps(blockProps, {
  allowedBlocks: ['smartpay/pricing-option'],
  template: [['smartpay/pricing-option', { label: 'Basic', amount: 100 }]],
  defaultBlock: {
    name: 'smartpay/pricing-option',
    attributesToCopy: ['billing_type','billing_period','className','style'],
  },
  directInsert: true,              // "+" appender adds option directly
  orientation: 'horizontal',
})
// InspectorControls: enableCustomAmount toggle + customAmountLabel + columns
```
**save.js** → wrapper + `InnerBlocks.Content` + (if `enableCustomAmount`) custom-amount input markup (`name="smartpay_amount"` + `smartpay_is_custom_amount=1`).

### Child `smartpay/pricing-option`
```js
registerBlockType('smartpay/pricing-option', {
  title: 'Pricing Option', parent: ['smartpay/pricing'],
  apiVersion: 3,
  supports: { splitting: true, reusable: false },
  attributes: {
    key:            { type: 'string' },          // generated, stable
    label:          { type: 'string', default: '' },
    amount:         { type: 'number', default: 0 },
    billing_type:   { type: 'string', default: 'One Time' },
    billing_period: { type: 'string', default: 'month' },  // pro
    setup_fee:      { type: 'number', default: 0 },        // pro
    billing_cycle:  { type: 'number' },                    // pro
  },
})
```
**edit.js**
- Inline on card: `<RichText tagName="span" value={label} … />` + inline number input for `amount`.
- `InspectorControls`:
  - Billing Type `<SelectControl>` — options filtered by `applyFilters('smartpay.pricing_option.billing_types', ['One Time'], …)` via **`window.wp.hooks`**.
  - Sub-fields slot: `applyFilters('smartpay.pricing_option.inspector', null, { attributes, setAttributes })`.
  - **Pro-lock:** if `! window.smartpayFormBuilder.isPro`, the "Subscription" choice is disabled and a `<Notice>`/badge renders "Subscription is a Pro feature →" linking to `https://wpsmartpay.com/pricing`.
- Ensure `key` is set on first render (like other blocks' `clientId`→key).

**save.js** → one radio card:
```html
<label class="smartpay-pricing-option">
  <input type="radio" name="smartpay_amount_key" value="{key}" class="sr-only"
         data-amount="{amount}" data-billing-type="{billing_type}">
  <span class="smartpay-pricing-option__label">{label}</span>
  <span class="smartpay-pricing-option__amount">{formatted amount}</span>
  <span class="smartpay-pricing-option__check" aria-hidden="true"></span>
</label>
```
Subscription meta (`/month`, setup fee) appended via JS hook `smartpay.pricing_option.amount_meta` in save, OR rendered by a small frontend script reading data-attrs. Selected state = CSS `input:checked ~`.

---

## 5. Selection + submit wiring (frontend)

- Cards are a native radio group (`name="smartpay_amount_key"`). One selectable for free.
- On change, frontend script sets `smartpay_amount` (the chosen card's `data-amount`) and `smartpay_form_billing_type` hidden inputs — matching existing checkout submit contract.
- Custom amount input: when focused/filled, deselects cards, sets `smartpay_is_custom_amount=1` and `smartpay_amount` to typed value (mirror existing custom-amount behavior).
- **No checkout/payment changes** — existing `Payment.php` validation reads `smartpay_amount_key` against synced `_smartpay_amounts`.

Prefer the **Interactivity API** (`data-wp-*`) per `.agent/skills/wp-interactivity-api` over jQuery; falls back to a tiny vanilla `view.js` if simpler.

---

## 6. Block → `_smartpay_amounts` sync (the trust copy)

On form CPT save (`save_post_{form_cpt}` or REST save in `NativeForm.php`):
```php
$blocks = parse_blocks( $post->post_content );
$amounts = []; // walk blocks, collect smartpay/pricing + inner smartpay/pricing-option attrs
// each → ['key','label','amount','billing_type','billing_period','setup_fee','billing_cycle', ...]
update_post_meta( $post_id, '_smartpay_amounts', wp_json_encode( $amounts ) );
```
- Strip Subscription entries when `! smartpay_is_pro_active()` (defensive).
- Keep `key` stable so saved-link/analytics references survive edits.
- This is the ONLY place amounts get authoritative values; checkout never trusts the raw posted amount alone.

---

## 7. Pro-lock gate (create in free, flip in pro)

Free `helpers`:
```php
function smartpay_is_pro_active(): bool {
  return (bool) apply_filters( 'smartpay_is_pro_active', false );
}
```
Free localizes to editor: `wp_localize_script( <form-builder handle>, 'smartpayFormBuilder', [ 'isPro' => smartpay_is_pro_active(), 'upgradeUrl' => 'https://wpsmartpay.com/pricing' ] );`

Pro `SmartPayProServiceProvider::boot()` (valid-licence block):
```php
add_filter( 'smartpay_is_pro_active', '__return_true' );
```
Subscription amount **processing already exists** in pro's Subscription module — synced Subscription entries flow through it unchanged.

---

## 8. Extensibility hooks (so pro/3rd-party can extend without forking)

| Hook | Type | Fired in | Purpose |
|---|---|---|---|
| `smartpay.pricing_option.billing_types` | JS (`window.wp.hooks`) | child edit | add "Subscription" to select |
| `smartpay.pricing_option.inspector` | JS | child edit | inject sub-field controls |
| `smartpay.pricing_option.amount_meta` | JS | child save/view | append `/month`, setup-fee line |
| `smartpay_pricing_option_meta` | PHP filter | (if any server render) | server-side meta html |

Split-registry rule (free bundles its own `@wordpress/hooks`): shared filters MUST go through `window.wp.hooks` directly — see pro CLAUDE.md.

---

## 9. Phased tasks — each ends with a functional gate

> Gate = `npm run dev` build clean · security grep (esc/sanitize/nonce/cap on new PHP) · specific browser check. Next phase blocked until `[x]`.

- **P1 — Parent + child scaffold + register.** Both blocks in inserter; parent only accepts child; insert/save/reload, no "Invalid block".
- **P2 — Child inline edit + One-Time sidebar.** Edit label/amount inline; Billing Type select (One Time only); attrs persist.
- **P3 — Parent appender + custom amount.** "+" adds option inheriting styles; custom-amount toggle shows input in preview.
- **P4 — Frontend save markup + selection + styles.** Cards render (Image #4); one selectable; check state; custom amount deselects cards.
- **P5 — Block→`_smartpay_amounts` sync + checkout.** Select option, submit, payment created with correct amount; tampered key rejected.
- **P6 — Pro-lock.** Create `smartpay_is_pro_active()`, localize `isPro`. Without pro: Subscription disabled + CTA. With pro filter on: Subscription enabled, sub fields show (Image #3), subscription checkout creates a subscription.
- **P7 — Docs.** Update free `docs/`, pro `features-and-roadmap.md` / `architecture.md` / `codebase-reference.md`. e2e author→pay pass (one-time + subscription).

---

## 10. Open verification before P4/P5

- Confirm exact submit field contract by reading the existing checkout JS that posts `smartpay_amount_key` (find in `resources/js/frontend/`).
- Confirm how current form-builder blocks reach the frontend (saved markup vs server render) to match the pricing block's render path.
- Confirm the form CPT slug + the precise save hook where `_smartpay_amounts` is currently written (REST `FormController` vs `save_post`) to attach the block sync without double-writing.

---

## 11. Layout & Recurring extensions (refined from screenshots, 2026-06)

Both reference designs are the **same `smartpay-form/pricing` block**, different config.
No new selectable-amount block; extend the existing parent/child.

| Reference | Block config |
|---|---|
| "Choose A Plan" (plan cards, radio + name + description left, `$99 / year` right) | `preset: list` + options carrying `description` + per-option billing |
| "Donation Amount" (amount tiles grid + custom amount + once/recurring two-card) | `preset: grid` + `allowCustomAmount` + `recurringChoice: optional` |

### A. Child `pricing-option` — add `description`
- New attr `description: { type: 'string', default: '' }`.
- Editor: `TextControl`/`RichText` (inspector + inline under label).
- `save.js`: emit `<span class="plan-desc">` when non-empty.
- Carried into `_smartpay_amounts` sync (`NativeForm::sync_pricing_block_amounts`).
- Visible only in `list` preset (CSS); hidden in `grid` so tiles stay compact.

### B. Parent `pricing` — layout presets
- Reuse existing `preset` attr: `grid` (today) | `list` (new).
- `list` = full-width rows: `[radio] label + description  ……  $99 / year`.
  Radio left, text left, price right. Reuses the gateway-accordion `:checked`
  card styling (blue border + filled radio on selected).
- Inspector: `SelectControl` preset. Wrapper class `.form-plan-grid--{preset}`.
  Price-right + description handled in CSS, minimal `save.js` branching.

### C. Parent `pricing` — recurring choice (DECIDED: parent toggle)
- New attrs: `recurringChoice: 'off' | 'optional'` (default `off`),
  `recurringPeriod` (default `month`), `recurringYesLabel`, `recurringNoLabel`.
- When `optional`: render a two-card selector (same `:checked` styling):
  - "Yes, count me in! / Every {period}"  → sets `_form_billing_type=Subscription`,
    `_form_billing_period={recurringPeriod}`.
  - "No, donate once."                     → `_form_billing_type=One Time`.
- Applies recurrence to whatever amount/tile the donor picked.
- **Pro-locked** (same gate as per-option Subscription): without pro the "Yes"
  card is disabled + "Available in Pro" CTA.

### D. Combination (no extra blocks)
One Pricing block exposes independently: amount source (grid|list ± custom amount)
and recurrence (per-option **or** global recurring choice). Author mixes freely:
donation grid + recurring (img 2), plan list (img 3), or any combination.

### E. Checkout — unchanged contract
Cards stay a radio group (`_form_amount` / `_form_amount_key`); recurrence writes
`_form_billing_type` (+ `_form_billing_period`). `Payment.php` validates the key
against synced `_smartpay_amounts`. Selection logic via Interactivity API, shared
by both presets and the recurring cards.

### F. Phases
1. `description` attr — option index/edit/save + meta sync.  ← P1
2. `list` preset — parent inspector select + CSS (radio/text-left, price-right).
3. Recurring choice — parent attrs + two-card render + hidden-input wiring + pro-lock.
4. Combination QA + style polish (donation+recurring, plan-list e2e).
5. Docs — this PLAN.md, free `docs/`, pro roadmap.
