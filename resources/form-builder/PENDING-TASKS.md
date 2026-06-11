# Form Builder — Pending Tasks

Carry-over work from the field-blocks / pricing session. Last committed checkpoint:
`c412cbfe` (free plugin) — pricing list/description + Name/Address/Checkbox completion.

---

## 1. Fix RadioField label `for` → `htmlFor` + label linking
`RadioField/input/save.js` uses `for=` (React drops it → clicking the label doesn't
select the radio). Change to `htmlFor`. Then add optional label→input `htmlFor`
linking like Name/Address (render-only-when-set attr, passed from the template).
Radio single-value `name="smartpay_form[field]"` is fine — no `[]`.

## 2. Finish label `htmlFor` linking for the rest
SelectField, TextInputField, TextAreaField, CustomerEmail, NumericField still lack
label→input linking. Add the optional `htmlFor` attr (default `''`, render only when
set → no block invalidation) + pass `fieldName` from each template. Confirm each
input carries `.form-control` so the default field styling + required `*` apply.

## 3. P3 — Pricing recurring (once/recurring) toggle
Per `PricingField/PLAN.md` §11.C. Parent attrs `recurringChoice: off|optional`,
`recurringPeriod`, labels. Render a two-card once/recurring selector (reuse the
`:checked` styling) that writes `_form_billing_type` / `_form_billing_period`.
Pro-locked. Then combination QA: donation grid + recurring, and plan list.

## 4. Verify Submit Button block persists text/settings
User reported the frontend button ignores their text/settings and shows defaults.
Saved `submit-pay` block had **zero attrs**. Reproduce in the editor: set
label/colors → confirm they serialize and the template renders them (the helper
`smartpay_get_submit_child_attrs` now scans the tree recursively). If edits don't
stick, find why `pay/edit.js` `setAttributes` isn't persisting.

## 5. Country → State cascade follow-ups
Cascade is frontend-only (`form.js` → `app.js`). Follow-ups:
- Required `*` not shown in the editor canvas (editor inputs aren't `required` and
  `editor.scss` lacks the `:has(:required)` rule).
- Expand `AddressField/data/locations.js` `SUBDIVISIONS` beyond the current 18
  countries if needed.
- Optional: make the editor State preview react to the Country selection.

## 6. Triage + commit unrelated pre-existing WIP
Dirty files predating this session, unrelated to the field blocks — commit in their
own logical commits:
- Coupon (Controller / Module / Dialog / http / store)
- `Admin.php`, `Common.php`, `framework/*`, `Integration.php`, `gulpfile.js`,
  `package.json`, `readme.txt`, `.github/workflows/phpcs.yml`
- Other field blocks not done this session: RadioField, SelectField, TextInputField,
  TextAreaField, CustomerEmail (+ `blocks/index.js`, `styles.scss`)
- `views/*`, `store/*`, `shortcode.js`, `LockedFeaturePage.jsx`
- Untracked: `app/Modules/Integration/LegacyForms.php` + `public/img/integrations/legacy-forms.png`

> Note: the committed built bundles (`app.js`, `form-builder/index.js`) were compiled
> from the full source tree, so they already contain the other dirty blocks' compiled
> code. Committing those blocks (this task) will reconcile source ↔ build.
