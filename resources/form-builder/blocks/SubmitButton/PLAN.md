# Submit Button Block — Build Plan (WP SmartPay)

**Status:** planned, not built. This dir (`SubmitButton/`) is the home.
**Owner plugin:** `smartpay` (free / core) — everything lives here. No pro code.
**Replaces:** the static `<button.smartpay-form-pay-now>` in
`resources/views/native-form-embed.php:209-213` + the `pay_button_label` field
in the Form Settings sidebar (`form-editor-sidebar/index.js` OptionsPanel).

---

## 1. Concept

A single, one-time-addable Gutenberg block modelled on `core/button`, but
purpose-built as the form's pay/submit action. Author drops it once into the
form; it carries all the common WP button controls (style, typography, color,
border, spacing, width, alignment) plus an icon + icon position.

```
smartpay-form/submit-button   (single block, multiple:false, one per form)
```

- One per form (`supports.multiple: false`) — like the current static button.
- Frontend save() MUST emit `<button class="… smartpay-form-pay-now">` — that is
  the exact selector `resources/js/frontend/payment/form.js:115` binds the
  payment click to. **Breaking this class = no payment fires.**
- It is a `type="button"` (JS-driven submit, not native form submit) to match the
  current contract — form.js intercepts the click, validates, then AJAX-posts.

---

## 2. Why this shape (verified against codebase)

| Fact | Source |
|---|---|
| Frontend binds click to `.smartpay-form-shortcode button.smartpay-form-pay-now` | `resources/js/frontend/payment/form.js:114-115` |
| Current button is static, label from `settings.pay_button_label` | `native-form-embed.php:17,209-213` |
| Label currently authored in sidebar OptionsPanel | `form-editor-sidebar/index.js:580-589` |
| Form blocks = JS-registered (`index.js`/`edit.js`/`save.js`), static save, no `block.json` | `resources/form-builder/blocks/*/` |
| Block registration loop reads `{namespace, settings}` | `resources/form-builder/blocks/index.js:101-103` |
| Disabled-state markup hook exists around button | `before/after_smartpay_payment_form_button` actions, `native-form-embed.php:207,215` |
| Native button supports pattern (color/typography/border/spacing/width) | `core/button` block.json + `.agent/skills/wp-block-development` |

---

## 3. Files to create (all in free `smartpay/`)

```
resources/form-builder/blocks/SubmitButton/
├── index.js        registerBlockType('smartpay-form/submit-button') + supports + attrs
├── edit.js         RichText label + InspectorControls (icon, position, width, align)
├── save.js         <button class="smartpay-form-pay-now …"> + icon markup
└── editor.scss / style.scss   (icon gap, full-width, alignment helpers)

resources/form-builder/blocks/index.js   ← import SubmitButton, add to smartPayBlocks[]
```

Edits to existing files:
```
resources/views/native-form-embed.php
  - Remove the hard-coded <button …pay-now> (209-213).
  - Keep before/after_smartpay_payment_form_button do_action() wrappers.
  - Fallback: if parse_blocks($body) has NO submit-button block, still emit the
    legacy static button (back-compat for forms authored before this block).
resources/js/admin/form-editor-sidebar/index.js
  - Remove "Pay Button Label" from OptionsPanel (now the block's RichText label).
  - Keep reading settings.pay_button_label only for the legacy fallback above.
app/Modules/NativeForm/NativeForm.php
  - On form save: if no submit-button block present, auto-append one (so every
    form always has a pay action). Mirrors the required Name/Email guard.
resources/form-builder/blocks/index.js
  - Register block styles ("Fill" default / "Outline") like core/button.
```

---

## 4. Block spec

```js
registerBlockType('smartpay-form/submit-button', {
  title: 'Pay Button',
  description: 'The form's pay / submit button. One per form.',
  icon: 'button',           // @wordpress/icons
  category: 'smartpay',
  keywords: ['pay', 'submit', 'button', 'checkout', 'buy'],
  supports: {
    anchor: true,
    html: false,
    multiple: false,        // one per form — replaces static button
    reusable: false,
    align: ['left', 'center', 'right', 'wide', 'full'],  // block alignment
    color: { background: true, text: true, gradients: true,
             __experimentalDefaultControls: { background: true, text: true } },
    typography: { fontSize: true, lineHeight: true, fontWeight: true, textTransform: true,
                  __experimentalDefaultControls: { fontSize: true } },
    spacing: { padding: true, margin: ['top','bottom'],
               __experimentalDefaultControls: { padding: true } },
    __experimentalBorder: { color: true, radius: true, style: true, width: true,
                            __experimentalDefaultControls: { radius: true } },
  },
  attributes: {
    label:         { type: 'string', default: 'Pay Now' },     // was pay_button_label
    width:         { type: 'number' },                          // 25/50/75/100 % preset, like core/button
    fullWidth:     { type: 'boolean', default: false },
    textAlign:     { type: 'string', default: 'center' },       // content alignment inside button
    icon:          { type: 'string', default: '' },             // dashicon/svg slug from a small picker
    iconPosition:  { type: 'string', default: 'left' },         // left | right
    style:         { type: 'object' },                          // native supports serialize here
  },
  edit, save,
})
```

**Block styles** (registered in `blocks/index.js`, mirror core/button):
`is-style-fill` (default) · `is-style-outline`.

---

## 5. edit.js

- `<RichText tagName="span" value={label} onChange … allowedFormats={[]} />` inside
  a preview `<button>` styled via `useBlockProps` (native supports apply live).
- Width preset: `ToggleGroupControl` 25/50/75/100% + a "Full width" toggle
  (`fullWidth` → `is-full-width` class), exactly like core/button's width control.
- Content alignment: `BlockControls` `AlignmentToolbar` → `textAlign` (left/center/right).
- Icon panel (`InspectorControls`):
  - Icon picker — small curated dashicon set (`arrow-right`, `cart`, `lock`,
    `yes`, `money-alt`, none). Store slug in `icon`.
  - `iconPosition` ToggleGroupControl (Left / Right) — hidden when no icon.
- Help note: "This is the form's pay action. Only one per form."

---

## 6. save.js (frontend contract — do not break)

```jsx
const blockProps = useBlockProps.save({
  className: classnames('btn smartpay-form-pay-now', {
    'is-full-width': fullWidth,
    [`has-text-align-${textAlign}`]: textAlign,
    [`has-icon-${iconPosition}`]: !!icon,
  }),
  style: width ? { width: `${width}%` } : undefined,
})
return (
  <button type="button" {...blockProps}>
    {icon && iconPosition === 'left'  && <Icon … className="sp-btn-icon" />}
    <RichText.Content tagName="span" value={label} />
    {icon && iconPosition === 'right' && <Icon … className="sp-btn-icon" />}
  </button>
)
```

- **`smartpay-form-pay-now` class is mandatory** — frontend payment handler binds to it.
- `type="button"` (NOT submit) — form.js drives the AJAX submit; native submit would double-post.
- Disabled state (no gateway) is added by frontend JS / the surrounding
  `before/after_smartpay_payment_form_button` hooks — block save stays neutral.

---

## 7. Back-compat + auto-insert (the safety net)

Every form must always have a working pay action, even legacy forms.

`native-form-embed.php`:
```php
$has_submit_block = has_block( 'smartpay-form/submit-button', $form_post );
// echo do_blocks( $body );  ← renders the block button when present
if ( ! $has_submit_block ) {
    // legacy fallback: emit the old static button using settings.pay_button_label
}
```

`NativeForm.php` (on REST/post save): if `! has_block('smartpay-form/submit-button')`,
append a default submit-button block to `post_content` (label from legacy
`pay_button_label` if set, else "Pay Now"). One-time migration on next save.

---

## 8. Phased tasks — each ends with a functional gate

> Gate = `npm run dev` build clean · security grep (esc/sanitize on edited PHP) · browser check. Next phase blocked until `[x]`.

- **P1 — Scaffold + register.** Block in inserter; `multiple:false` enforced
  (can't add twice); insert/save/reload, no "Invalid block".
- **P2 — Label + native supports.** RichText label; color/typography/border/spacing
  controls apply in editor; attrs persist on reload.
- **P3 — Width / alignment / icon.** Width presets + full-width; content + block
  alignment; icon picker + position render in preview.
- **P4 — Frontend save + contract.** Save emits `button.smartpay-form-pay-now`
  `type="button"`; on a real form, clicking it still triggers payment (form.js
  bind intact). Styles render on frontend.
- **P5 — Remove static button + sidebar field.** Strip static button + "Pay Button
  Label" from sidebar; legacy fallback covers forms without the block.
- **P6 — Auto-insert migration.** Saving an old form (no submit block) appends one
  with the previous label; new forms get it via template.
- **P7 — Docs.** Update free `docs/`; if any pro surface touches it, update pro
  `features-and-roadmap.md`. e2e: author → pay passes.

---

## 9. Open verification before P4/P5

- Confirm form.js relies ONLY on the class (not button position/DOM order) — read
  `resources/js/frontend/payment/form.js:114-198`.
- Confirm `has_block()` works against the form CPT content at render time (the
  template receives `$body`/`$post_id` — verify the WP_Post is available).
- Confirm the block template insertion point: does the form editor seed a default
  template (so new forms auto-include the button), or rely on the auto-insert on save?
