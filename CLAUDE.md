# CLAUDE.md — WP SmartPay (Free)

## Project Overview
WordPress payment plugin (SmartPay). Free version on wp.org. Handles digital downloads, donations, and payment gateway integrations (Stripe, PayPal, Paddle, etc.).
Free plugin must pass WP.org automated + AI review.
Pro add-on (`wp-smartpay-pro`) extends this via hooks/filters.

## Architecture Rules

### PHP Standards
- Follow WordPress Coding Standards (WPCS) strictly
- PHP 8.1+ minimum
- PSR-4 autoloading via Composer with `SmartPay\` namespace
- Every PHP file starts with: `defined('ABSPATH') || exit;`
- Never use short PHP tags `<?` — always `<?php`
- Never use `eval()`, `base64_encode/decode` for code, `error_reporting()`, or `ini_set('display_errors')`

### Security (Critical — WP.org AI checks these)
- **Sanitize ALL input**: `sanitize_text_field()`, `absint()`, `sanitize_email()`, `wp_kses_post()`, etc.
- **Escape ALL output**: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` — use `esc_html_e()` instead of `_e()`
- **Nonce everything**: Every form and AJAX handler must verify nonces with `wp_verify_nonce()` or `check_ajax_referer()`
- **Capability checks**: Always verify `current_user_can()` before actions
- **Prepared SQL**: Always use `$wpdb->prepare()` for database queries
- **No direct DB queries** when a WP API exists
- **No direct file access**: Every PHP file has the ABSPATH check
- **`wp_safe_redirect()`** instead of `wp_redirect()` where possible

### Internationalization
- Text domain: `smartpay` (must match plugin slug exactly)
- Wrap ALL user-facing strings: `__()`, `_e()`, `_n()`, `esc_html__()`
- Never concatenate translated strings — use `sprintf()`:
  ```php
  // ❌ Wrong
  echo __('Hello', 'smartpay') . ' ' . $name;
  // ✅ Correct
  printf( esc_html__( 'Hello %s', 'smartpay' ), esc_html( $name ) );
  ```
- Never use variables as text domain

### Prefixing
- All functions: `smartpay_` prefix (or namespaced)
- All classes: `SmartPay\` namespace
- All hooks: `smartpay_` prefix
- All database options: `smartpay_` prefix
- All REST routes: `smartpay/v1/`
- All post types, taxonomies: `smartpay_` prefix
- All constants: `SMARTPAY_` prefix
- All CSS classes: `.smartpay-*`
- All JS globals: `window.smartpayData`

### Free vs Pro Boundary
- Free plugin must work 100% standalone — no fatal errors without Pro
- Pro extends via hooks/filters defined in Free, never modifies Free files
- No "upgrade to pro" nags that hijack admin experience
- No feature flags that imply locked features (violates wp.org guidelines)

### Enqueueing Assets
- Always use `wp_enqueue_script()` / `wp_enqueue_style()`
- Register first, enqueue only on pages where needed
- Use WordPress bundled libraries (jQuery, React, wp-element, etc.) — never bundle your own copy
- Use `wp_set_script_translations()` for JS i18n
- Set `strategy => 'defer'` for scripts when possible

## File Organization
- `app/` — Core PHP classes (PSR-4, namespace `SmartPay\`)
- `framework/` — Framework classes (namespace `SmartPay\Framework\`)
- `resources/` — JS/CSS source files
- `public/` — Compiled assets (CSS, JS)
- `database/` — Database migrations/schema
- `languages/` — Translation files

## Git Workflow
- Branch from `develop` for features: `feature/issue-{number}-{slug}`
- Branch from `develop` for fixes: `fix/issue-{number}-{slug}`
- PR into `develop`, then `develop` → `main` for releases
- Commit messages: `type(scope): description (#issue)`
  Types: feat, fix, refactor, docs, test, chore, style

## Testing Requirements
- PHPUnit tests for all new classes/methods
- Test with WP_DEBUG = true, no notices/warnings/errors
- Test with latest WP + PHP 8.1 and 8.2+
- Run `wp plugin check smartpay` locally before PR

## Review Checklist (AI reviewer must verify all)
- [ ] All input sanitized
- [ ] All output escaped
- [ ] Nonces on all forms/AJAX
- [ ] Capability checks on all actions
- [ ] $wpdb->prepare() on all queries
- [ ] No direct file operations without ABSPATH check
- [ ] All strings translatable with correct text domain (`smartpay`)
- [ ] Assets enqueued properly (not loaded globally)
- [ ] No PHP notices/warnings with WP_DEBUG
- [ ] WordPress bundled libraries used (no duplicates)
- [ ] All functions/classes properly prefixed or namespaced
- [ ] readme.txt updated if user-facing changes
- [ ] Changelog entry added

---

## Skills Reference

Detailed coding standards live in the pro plugin (private):
- `../wp-smartpay-pro/.claude/skills/wp-standards.md`
- `../wp-smartpay-pro/.claude/skills/wp-ui-components.md`
- `../wp-smartpay-pro/.claude/skills/php-coding-standards.md`
- `../wp-smartpay-pro/.claude/skills/security-standards.md`
- `../wp-smartpay-pro/.claude/skills/hooks-catalog.md`
- `../wp-smartpay-pro/.claude/skills/integration-patterns.md`

When running Claude Code from the pro plugin directory (recommended), these are
available at `.claude/skills/`. Read them before writing any code for this plugin.

## Reference Docs (in pro plugin)

- `../wp-smartpay-pro/docs/architecture.md`
- `../wp-smartpay-pro/docs/codebase-reference.md`
- `../wp-smartpay-pro/docs/project-rules.md`

---

## !! IMPORTANT: Keeping Docs in Sync !!

Whenever changes are made to this plugin, update the relevant docs in the pro plugin:

1. `../wp-smartpay-pro/docs/features-and-roadmap.md` — feature additions, removals, status changes
2. `../wp-smartpay-pro/docs/architecture.md` — folder structure, DB, REST endpoint changes
3. `../wp-smartpay-pro/docs/codebase-reference.md` — new patterns, hooks, helpers, key files
4. This `CLAUDE.md` — if plugin namespace, prefixes, or major architecture decisions change

Do this **in the same response** as implementing the change.
Never let the docs drift from the current state of the plugin.
