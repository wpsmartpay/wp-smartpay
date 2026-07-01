# Goal Progress Block — Build Plan (WP SmartPay)

**Status:** planned, not built. This dir (`GoalProgress/`) is the home.
**Owner plugin:** `smartpay` (free / core) — everything lives here. No pro code.
**Replaces:** the static goal-progress markup in
`resources/views/native-form-embed.php:37-72` (currently auto-rendered above the
form when `goal.enabled`). The goal *config* (target, type, behavior) STAYS in
the Form Settings sidebar GoalPanel; this block is the **display surface** the
author can place, style, and position anywhere in the form.

---

## 1. Concept

A one-time-addable Gutenberg block that renders the goal progress bar + counts +
message, with full styling controls. It is a **dynamic (server-rendered) block**:
the live `current`/`target`/`percentage` come from the DB, not from saved
attributes, so the numbers are always fresh.

```
smartpay-form/goal-progress   (single block, multiple:false)
```

- Reads goal config from form meta `_smartpay_settings.goal` (set in GoalPanel).
- Live counts from `smartpay_calculate_goal_progress( $form_id )`
  (`app/Helpers/smartpay.php:1078`) — COUNT/SUM of COMPLETED payments for the form,
  transient-cached.
- Styling (typo, color, bar fill color, track color, spacing, border, radius,
  bar height, label visibility, message text) lives in block attributes.
- Editor shows a **mock** progress (config target + sample %) since real counts
  need the server; frontend shows real numbers.

---

## 2. Why this shape (verified against codebase)

| Fact | Source |
|---|---|
| Goal config stored in `_smartpay_settings.goal` (enabled/type/target/message/behavior) | `form-editor-sidebar/index.js:439-548` (GoalPanel) |
| Live progress computed server-side: COUNT(*) / SUM(amount) of COMPLETED payments matching form_id | `app/Helpers/smartpay.php:1078-1117` |
| Progress already cached in a transient `smartpay_goal_{id}_{type}` | `smartpay.php:1096-1100` |
| Current static render lives above the form, not author-placeable | `native-form-embed.php:37-72` |
| REST already returns goal_data (current/target/percentage/goal_reached/type) for a form | `NativeForm.php:317-328` (admin-only list endpoint) |
| Form blocks rendered via `do_blocks($body)` — supports dynamic render via `render_block` filter | `native-form-embed.php:84` |
| Form blocks are JS-registered, static save — dynamic needs a PHP `render_block` hook keyed on block name | `resources/form-builder/blocks/*/` |
| `do_blocks` runs in template where `$post_id` is in scope | `native-form-embed.php:39,84` |

---

## 3. Files to create (all in free `smartpay/`)

```
resources/form-builder/blocks/GoalProgress/
├── index.js        registerBlockType('smartpay-form/goal-progress') + supports + attrs
├── edit.js         mock progress preview + InspectorControls (bar/track color, height, labels)
├── save.js         RETURN null-ish placeholder wrapper with data-attrs (dynamic block)
└── editor.scss / style.scss   (bar, track, fill animation, count typography)

resources/form-builder/blocks/index.js   ← import GoalProgress, add to smartPayBlocks[]
```

Edits to existing files:
```
app/Modules/NativeForm/NativeForm.php  (or a new GoalProgress render class)
  - add_filter('render_block_smartpay-form/goal-progress', renderGoalProgress, 10, 2)
    → server-renders live progress into the block's saved wrapper using
      smartpay_calculate_goal_progress( current form id ) + the block's style attrs.
  - Resolve form id: the block renders inside the form CPT content; use
    get_the_ID() / the $post_id already in template scope (pass via filter closure).
resources/views/native-form-embed.php
  - Remove the auto-rendered static goal block (37-72) — now author-placed.
  - Fallback: if goal.enabled && form has NO goal-progress block, keep rendering
    the legacy bar at the top (back-compat).
resources/form-builder/blocks/index.js
  - Optionally register a "Minimal" / "Card" block style.
```

No new REST route strictly required (server render covers frontend). A public
read-only endpoint is OPTIONAL — only if we later want live/animated refresh
without page reload (see §7).

---

## 4. Block spec

```js
registerBlockType('smartpay-form/goal-progress', {
  title: 'Goal Progress',
  description: 'Shows fundraising / sales goal progress. Configure the goal in Form Settings → Goal.',
  icon: 'chart-bar',
  category: 'smartpay',
  keywords: ['goal', 'progress', 'fundraising', 'target', 'thermometer'],
  supports: {
    anchor: true,
    html: false,
    multiple: false,
    reusable: false,
    align: ['wide', 'full'],
    color: { background: true, text: true, gradients: true,
             __experimentalDefaultControls: { text: true } },
    typography: { fontSize: true, lineHeight: true, fontWeight: true,
                  __experimentalDefaultControls: { fontSize: true } },
    spacing: { padding: true, margin: ['top','bottom'], blockGap: true,
               __experimentalDefaultControls: { padding: true } },
    __experimentalBorder: { color: true, radius: true, style: true, width: true },
  },
  attributes: {
    // display toggles
    showBar:        { type: 'boolean', default: true },
    showCounts:     { type: 'boolean', default: true },   // "12 of 100 sold"
    showPercentage: { type: 'boolean', default: true },
    showMessage:    { type: 'boolean', default: true },   // goalMetMessage when reached
    // labels (config target/type still come from GoalPanel)
    progressTemplate: { type: 'string', default: '{current} of {target} {unit}' },
    // bar styling (not covered by native supports)
    barColor:       { type: 'string', default: '' },      // fill
    trackColor:     { type: 'string', default: '' },      // background of track
    barHeight:      { type: 'string', default: '12px' },
    barRadius:      { type: 'string', default: '999px' },
    // editor-only mock
    previewPercent: { type: 'number', default: 35 },
    style:          { type: 'object' },
  },
  edit, save,
})
```

---

## 5. edit.js (mock preview — real counts are server-side)

- Read goal config live from form meta to show real target/type in the preview:
  ```js
  const [ meta ] = useEntityProp('postType','smartpay_form','meta')
  const goal = JSON.parse(JSON.parse(meta._smartpay_settings||'{}').goal||'{}')... // same parse as GoalPanel
  ```
- If `! goal.enabled`: render a `<Notice>` — "Goal is off. Enable it in
  Form Settings → Goal." with a button that opens the document sidebar
  (`openGeneralSidebar('edit-post/document')`).
- Otherwise render the bar at `previewPercent` with current styling so the author
  sees their colors/height/radius. Counts use mock numbers derived from target.
- `InspectorControls`:
  - Display toggles (bar / counts / percentage / message).
  - `progressTemplate` TextControl with `{current} {target} {unit} {percent}` tokens.
  - Bar styling: barColor + trackColor (`PanelColorSettings`), barHeight +
    barRadius (`UnitControl`).
  - Note: "Target, goal type and goal-met message are set in Form Settings → Goal."

---

## 6. save.js + server render (dynamic block)

**save.js** — emit ONLY the static shell + style hooks; leave counts to the server:
```jsx
const blockProps = useBlockProps.save({
  className: 'smartpay-goal-progress',
  style: { '--sp-bar': barColor, '--sp-track': trackColor,
           '--sp-bar-h': barHeight, '--sp-bar-r': barRadius },
  'data-show-bar': showBar ? '1' : '0',
  'data-show-counts': showCounts ? '1' : '0',
  'data-show-percentage': showPercentage ? '1' : '0',
  'data-show-message': showMessage ? '1' : '0',
  'data-template': progressTemplate,
})
return <div {...blockProps} />   // server fills inner markup
```

**Server render** — `add_filter('render_block_smartpay-form/goal-progress', …, 10, 2)`:
```php
function ( string $content, array $block ): string {
    $form_id = get_the_ID();                 // form CPT being rendered
    if ( ! $form_id || ! function_exists('smartpay_calculate_goal_progress') ) return $content;
    $p = smartpay_calculate_goal_progress( (int) $form_id );   // current/target/percentage/goal_reached/type
    // Build bar + counts + message inner HTML honoring the data-* flags + template,
    // injecting into the saved wrapper. ESCAPE every value (esc_html / esc_attr).
    return $rendered;     // wrapper kept, inner filled
}
```
- Resolve `$form_id` robustly: prefer the `$post_id` already in template scope
  (pass via a bound closure when `do_blocks` is called in `native-form-embed.php`),
  fall back to `get_the_ID()`.
- Respect `goal.behaviorWhenGoalMet` / `goalMetMessage` exactly like the current
  static block (`native-form-embed.php:48-60`).
- Reuse the existing transient cache — no new query path.

---

## 7. Optional: live refresh (defer; not in MVP)

Public read-only REST: `smartpay/v1/forms/(?P<id>\d+)/goal` →
`{ current, target, percentage, goal_reached, type }`. `permission_callback`
`__return_true` (public, read-only, no PII). A tiny Interactivity API `view`
module polls/animates the bar after a successful payment. Skip for MVP — server
render is enough.

---

## 8. Phased tasks — each ends with a functional gate

> Gate = `npm run dev` build clean · security grep (esc/sanitize on render filter) · browser check. Next phase blocked until `[x]`.

- **P1 — Scaffold + register.** Block in inserter; `multiple:false`; static shell
  saves/reloads with no "Invalid block".
- **P2 — Config read + editor preview.** Pulls goal config from meta; off-state
  Notice + "open settings"; mock bar reflects styling controls.
- **P3 — Styling controls.** Bar/track color, height, radius, display toggles,
  template tokens persist; native color/typography/spacing apply.
- **P4 — Server render.** `render_block_*` filter outputs live current/target/%
  on the frontend with correct escaping; goal-met message + behavior honored.
- **P5 — Remove static top render.** Strip auto-rendered bar from template; legacy
  fallback covers forms without the block; new forms can place it anywhere.
- **P6 — Docs.** Update free `docs/`; pro docs only if a pro surface references it.
  e2e: enable goal → place block → complete payment → count increments (after
  cache window / on next load).

---

## 9. Open verification before P4/P5

- Confirm `$post_id` available to the `render_block` filter when `do_blocks($body)`
  runs (`native-form-embed.php:84`) — bind it into the filter closure to be safe.
- Confirm transient invalidation: when does `smartpay_goal_{id}_{type}` clear after
  a new COMPLETED payment? (grep `delete_transient` / payment status hooks) — block
  inherits whatever freshness the helper already provides.
- Confirm the goal config parse shape matches GoalPanel's double-encoded
  `settings.goal` (string-in-string) — reuse the exact parse from
  `form-editor-sidebar/index.js:453-461` to avoid drift.
