=== WPSmartPay – Payment Forms, Invoices, Donations & Subscriptions ===
Contributors: converswp
Tags: payment forms, stripe, subscriptions, invoices, donation
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.1
Stable Tag: 3.2.9
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Accept payments, subscriptions, and donations on WordPress with Stripe, PayPal, and more — invoices included, no store or cart required.

== Description ==

Need to charge for something on WordPress — a service, a subscription, a donation — but don't want to set up a whole online store just to do it?

**WPSmartPay** accepts payments, subscriptions, and donations without WooCommerce, cart plugins, or custom code. Create a payment form, connect a gateway, share the link — start getting paid in minutes.

Invoice clients, bill recurring subscriptions, or collect one-time and recurring donations. No store to configure, no products to manage — just a form and a gateway.

[youtube https://www.youtube.com/watch?v=PdqA7XNH60Q]

---

### ✅ What's New in Version 3.2.6

* **PayPal notices scoped to SmartPay pages** — API key warnings and unsupported-currency notices no longer appear on unrelated admin pages.
* **Integration toggle conflict fixed** — The toggle AJAX action was renamed to avoid a conflict with the SmartMembers plugin that was causing nonce failures.
* **Block API version warning gone** — `apiVersion: 3` added to the form and product blocks to suppress the WP 6.9 deprecation warning on every admin page load.
* **Required textarea validation** — Required message/textarea fields now show an inline error directly below the field when left empty, instead of silently blocking submission.
* **Product validation** — Products can no longer be saved without a title or a price greater than zero; clear error messages guide you to fix it before saving.

---

### Accept Payments Your Way

Connect the gateway that fits your business and start accepting payments immediately:

* **PayPal Standard** — Free, trusted, works out of the box
* **Manual / Free Payment** — For $0 or pay-later flows
* **Stripe** *(Pro — most popular)* — Cards, Apple Pay, Google Pay, Link, 135+ currencies
* **Authorize.net** *(Pro)* — Industry-standard gateway for US and Canadian businesses
* **Mollie** *(Pro)* — Ideal for European businesses; iDEAL, SEPA, and more
* **Paddle** *(Pro)* — Global merchant of record; handles VAT and sales tax automatically
* **Razorpay** *(Pro)* — Best for India-based businesses; cards, UPI, netbanking
* **toyyibPay** *(Pro)* — Malaysian FPX online banking gateway
* **Paytm** *(Pro)* — Popular Indian payment gateway
* **bKash** *(Pro)* — Mobile banking payments in Bangladesh

[See all gateways and pricing](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=gateway-list)

---

### Invoicing Built Into WordPress

Stop switching between tools. WPSmartPay lets you create and send professional invoices without leaving the WordPress admin.

* Generate invoices for one-time or recurring payments
* Send to customers by email in one click
* Track status from Draft through Sent, Paid, and Overdue
* Customize with your logo and business details

[Learn more about invoicing](https://wpsmartpay.com/features/invoices/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=invoicing-section)

---

### Subscription and Recurring Payments *(Pro)*

Accept recurring payments and build predictable revenue streams with WPSmartPay Pro.

* Set up recurring billing on any payment form — weekly, monthly, or yearly
* Installment plans and fixed-period billing cycles
* Membership plans with role-based access control
* Renewal reminder emails sent automatically
* Supported on Stripe and Paddle gateways

[Learn more about subscriptions](https://wpsmartpay.com/features/subscriptions/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=subscriptions-section)

---

### Native Gutenberg Payment Form Builder

Build payment forms the WordPress way — with blocks. Live preview as you design.

* Drag-and-drop fields: name, email, address, phone, and more
* Flexible pricing: fixed price, custom amount, or pricing tiers
* Start from the **template library** or build from scratch
* Embed anywhere with the SmartPay block, shortcode, or popup overlay
* Stacked or split checkout layout, and an optional "require login to checkout" toggle - per form

---

### Donations Made Simple

Set up a donation form in minutes. Accept one-time or recurring donations from supporters worldwide.

* Let donors choose their own amount with the custom amount field
* Collect recurring donations with subscription billing *(Pro)*
* Send automatic thank-you emails on every donation
* Works with PayPal, Stripe, and all supported gateways

---

### Anti-Spam Protection

Stop bot abuse before it reaches your payment processor. Choose from 3 built-in integrations:

* **Cloudflare Turnstile** — Privacy-first, invisible to real users
* **hCaptcha** — GDPR-compliant with high bot-detection accuracy
* **reCAPTCHA v3** — Score-based, never interrupts real customers

---

### Tax Control

Stay compliant without extra plugins:

* Percentage or fixed-amount tax rates
* Apply globally or per payment form
* Tax line shown clearly on checkout and receipts

---

### Reports and Analytics

* **Dashboard overview** (free) — Total revenue, payment count, recent transactions, and key stats
* **Advanced reports** *(Pro)* — Tabbed reports for Revenue, Forms, Subscriptions, Goals, and Payment Recovery
* Date-range filters: today, this week, this month, custom
* Per-form conversion and revenue breakdown

[Explore Pro reporting](https://wpsmartpay.com/features/reports/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=reporting-section)

---

### CRM and Marketing Integrations *(Pro)*

Connect WPSmartPay to your marketing stack and automate post-payment workflows.

* **[Mailchimp](https://mailchimp.com/)** — Add customers to lists and tags on payment
* **[MailerLite](https://www.mailerlite.com/)** — Sync subscribers and trigger automations
* **[FluentCRM](https://wordpress.org/plugins/fluent-crm/)** — Tag contacts and trigger sequences in your WordPress CRM
* **[AffiliateWP](https://affiliatewp.com/)** — Track affiliate referrals on every payment
* **[Zapier](https://zapier.com/)** — Connect to 6,000+ apps with no code
* **[Pabbly Connect](https://www.pabbly.com/connect/)** — Automate workflows with multi-step triggers

[See all integrations](https://wpsmartpay.com/features/integrations/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=integrations-section)

---

### Course and Membership Integrations *(Pro)*

Sell courses and memberships without a separate checkout — payment and enrollment happen in one step.

* **[LearnDash](https://www.learndash.com/)** — Enroll students in a course the moment payment clears
* **[Tutor LMS](https://wordpress.org/plugins/tutor/)** — Grant course access automatically on successful payment
* **[LifterLMS](https://wordpress.org/plugins/lifterlms/)** — Unlock memberships and courses after checkout

---

### AI Agent Integration *(Pro)*

WPSmartPay Pro is built for the agentic web. Connect Claude, Cursor, or any MCP-compatible AI agent to your payment operations — no API scripting required.

Agents can take real actions inside your site using the built-in Model Context Protocol (MCP) server:

* **Create payment forms** from a prompt — agent scaffolds the full Gutenberg form with pricing tiers in seconds
* **Query payments and customers** — ask your agent to find a customer, pull recent transactions, or check payment status
* **Create and send invoices** — agent drafts an invoice and fires it to the customer by email in one step
* **Generate coupon codes** — bulk-create codes with custom prefixes, limits, and expiry from a single instruction
* **Manage subscriptions** — list active subscriptions or cancel one by customer email

Use Claude, ChatGPT, Codex, Cursor, or any tool that speaks Model Context Protocol. No webhooks or custom scripts needed — the MCP endpoint is built into the plugin.

[Learn how to connect an AI agent](https://docs.wpsmartpay.com/en/mcp?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=mcp-section)

---

### Who Is WPSmartPay For?

* **Freelancers and Agencies** — Invoice clients and accept one-time or recurring service payments
* **Coaches and Consultants** — Charge for sessions, courses, and memberships
* **Nonprofits and Charities** — Accept one-time and recurring donations
* **Content Creators** — Sell eBooks, templates, audio, and digital files
* **Event Organizers** — Ticket sales and event registrations
* **Fitness and Wellness Studios** — Sell subscription and class packages

---

### Free Features at a Glance

* Native Gutenberg payment form builder + template library
* Invoice management (create, send, track)
* Anti-spam: Cloudflare Turnstile, hCaptcha, reCAPTCHA v3
* Tax control system
* Dashboard reports and analytics
* Customizable email templates
* Guided onboarding wizard
* Support and system info page
* Customer management
* Coupon codes
* PayPal Standard + manual/free payment gateway
* Test mode for safe development
* GDPR-friendly
* Developer hooks and filters

---

### WPSmartPay Pro

Unlock more with [WPSmartPay Pro](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=pro-section):

**Pro Payment Gateways:**
Stripe - Authorize.net - Paddle - Razorpay - Mollie - bKash - toyyibPay - Paytm

**Subscriptions and Recurring Billing:**
* Recurring plans on forms and products
* Billing cycles and installment plans
* Renewal reminder emails

**Advanced Coupon Management:**
* Bulk coupon generation with custom prefix/suffix and quantity
* CSV export of generated codes
* Per-coupon and per-customer usage limits
* Percentage or fixed discounts with expiry dates

**Outgoing Webhooks:**
* Fire payment events to any URL in real time
* HMAC-SHA256 signed requests (Standard Webhooks spec)
* Automatic retry with delivery log
* Works with Zapier, Make, n8n, and custom endpoints

**Advanced Reports:**
* Revenue, Forms, Subscriptions, Goals, and Recovery tabs
* Per-form conversion and revenue breakdown
* Failed/abandoned payment recovery tracking

**Marketing and Automation Integrations:**
Mailchimp - MailerLite - FluentCRM - AffiliateWP - Pabbly - Zapier

**Course and Membership Integrations:**
LearnDash - Tutor LMS - LifterLMS

**AI Agent Integration (MCP):**
* Built-in Model Context Protocol server
* AI agents can create forms, query payments, send invoices, manage coupons and subscriptions
* Works with Claude, Cursor, and any MCP-compatible client

[Get WPSmartPay Pro](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=pro-cta)

---

#### Gateway Coverage

* **Stripe (Pro)** — 47+ countries, 135+ currencies
* **PayPal** — 200+ countries, 25 currencies
* **Authorize.net (Pro)** — US and Canada; USD, CAD, GBP, EUR and more
* **Mollie (Pro)** — European Economic Area (EEA)
* **Paddle (Pro)** — Global merchant of record; handles VAT and sales tax worldwide
* **Razorpay (Pro)** — India, 100+ currencies
* **toyyibPay (Pro)** — Malaysia (MYR)
* **Paytm (Pro)** — India (INR)
* **bKash (Pro)** — Bangladesh (BDT)

---

**Documentation:** [docs.wpsmartpay.com](https://docs.wpsmartpay.com/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=docs-cta)
**Support:** Priority email support for Pro users — [get Pro](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=support-cta)

== Installation ==

1. Go to **Plugins → Add New** in your WordPress dashboard.
2. Search for **"WPSmartPay"**.
3. Click **Install Now**, then **Activate Plugin**.
4. Follow the **Setup Wizard** to configure your currency, pages, and first payment gateway.
5. Go to **WPSmartPay → Forms** to create your first payment form.

For Pro features: upload WP SmartPay Pro, activate, and enter your license key at **SmartPay → Settings → License**.

== Frequently Asked Questions ==

= Is WPSmartPay free? =
Yes. The core plugin is completely free and includes the Gutenberg form builder, invoice management, tax control, anti-spam protection, dashboard reports, email templates, and the PayPal Standard gateway — no credit card required to install.

= How is WPSmartPay different from other WordPress payment plugins? =
Most payment plugins are either a full store (WooCommerce and its cart, products, and shipping) or a single-purpose donation button. WPSmartPay is neither — it's a standalone form-and-gateway tool built for anyone who needs to get paid without running a store: invoicing built into the same admin screen as your forms, three anti-spam options out of the box, and a free tier that includes invoicing and donation forms, which most competing free plugins don't.

= Do I need WooCommerce to accept payments? =
No. WPSmartPay is a fully standalone payment plugin. There is no cart, no store setup, and no WooCommerce required. Install, connect a gateway, and start getting paid in minutes.

= Which payment gateways are supported? =
Free: PayPal Standard and Manual/Free gateway. Pro adds Stripe, Authorize.net, Paddle, Razorpay, Mollie, bKash, toyyibPay, and Paytm. [See the full gateway list](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=faq-gateways)

= Is Stripe supported? =
Yes — Stripe is available in WPSmartPay Pro. It supports cards, Apple Pay, Google Pay, Link, and 135+ currencies across 47+ countries. [Learn more](https://wpsmartpay.com/features/stripe/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=faq-stripe)

= Can I send invoices from WordPress? =
Yes. WPSmartPay includes a built-in invoice management system. Create invoices, send them to customers by email, and track their status from Draft through Sent, Paid, and Overdue — all from the WordPress admin.

= Can I accept recurring or subscription payments? =
Yes, with WPSmartPay Pro. Set up recurring billing on any payment form with weekly, monthly, or yearly billing cycles. Membership plans with role-based access are also included.

= Can I collect donations? =
Yes. Enable the custom amount field on any payment form to accept one-time donations. Recurring donations are available with WPSmartPay Pro.

= Does it work with PayPal? =
Yes. PayPal Standard is built into the free plugin. Connect your PayPal account under SmartPay → Settings → Payment Gateways and start accepting payments immediately — no Pro license required.

= Is there a coupon or discount code feature? =
Yes. Create percentage or fixed-amount coupon codes, set expiry dates, and apply them at checkout. Basic coupon management is included in the free plugin. Pro adds bulk generation, per-customer limits, and CSV export.

= What is the difference between free and Pro? =
The free plugin includes PayPal, manual payment, payment forms, invoices, coupons, customer management, and dashboard reports. Pro adds premium gateways (Stripe, Authorize.net, Mollie, Paddle, etc.), subscription billing, advanced tabbed reports, CRM/marketing integrations, LMS integrations (LearnDash, Tutor LMS, LifterLMS), bulk coupons, and outgoing webhooks. [Compare plans](https://wpsmartpay.com/pricing/?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=faq-upgrade)

= Does it work with the Gutenberg block editor? =
Yes — the form builder is built entirely on Gutenberg blocks and integrates natively with the WordPress editor. Embed forms anywhere using the SmartPay block or the `[smartpay_form]` shortcode.

= Is WPSmartPay GDPR compliant? =
Yes. Payment card data is never stored on your server — all sensitive data is handled directly by the payment gateway (Stripe, PayPal, etc.). Turnstile and hCaptcha are privacy-first anti-spam options; reCAPTCHA v3 is also supported.

= Can AI agents like Claude, ChatGPT, or Cursor manage my payment forms and store? =
Yes, with WPSmartPay Pro. The plugin ships a built-in Model Context Protocol (MCP) server that exposes 15 abilities to any MCP-compatible AI agent. Agents can create payment forms from a natural-language prompt, query payments and customers, create and send invoices, bulk-generate coupon codes, and manage subscriptions — all without writing custom code. [Learn more](https://docs.wpsmartpay.com/en/mcp?utm_source=readme&utm_medium=link&utm_campaign=plugin-readme&utm_content=faq-mcp)

= What PHP version is required? =
PHP 8.1 or higher. WordPress 6.0 or higher.

= How do I install and set up WPSmartPay? =
Go to Plugins → Add New in your WordPress dashboard, search for "WPSmartPay", click Install Now, then Activate. A guided setup wizard walks you through currency selection, page creation, and connecting your first payment gateway — the entire process takes just a few minutes.

= Do I need a merchant account to accept payments? =
No. You only need an account with a supported payment gateway such as Stripe, PayPal, or Mollie. Funds go directly into your gateway account — there is no separate merchant account required.

= Will WPSmartPay slow down my website? =
No. WPSmartPay is built to be lightweight. It only loads its scripts and styles on pages where a payment form is actually present, so your other pages remain fast and unaffected.

= Can I add custom fields to my payment forms? =
Yes. The form builder lets you add text fields, dropdowns, checkboxes, phone numbers, addresses, and other input fields. This is useful for collecting company names, dietary preferences, agreement to terms, or any extra information you need from customers.

= Can I issue refunds from the WordPress admin? =
Yes. Open any payment from the Payments screen and click Refund. For gateways like Stripe and Paddle, the refund is processed automatically through the gateway and the money returns to the customer's original payment method.

= Can I export my payment and transaction data? =
Yes. Export your payment records from the Payments page as a CSV file that you can open in Excel or Google Sheets for accounting, tax reporting, or team sharing.

= Can customers manage their own subscriptions and payment details? =
Yes. WPSmartPay provides a customer self-service portal where subscribers can view payment history, update their payment method, pause, or cancel their subscriptions — without needing access to your WordPress admin.

= Can I offer a free trial for subscription plans? =
Yes, with WPSmartPay Pro. Add a trial period to any subscription form — for example, a 7-day free trial before the first charge. Trial settings are configured directly in the form builder.

= Can I create installment plans for expensive products or courses? =
Yes, with WPSmartPay Pro. Break a large payment into smaller installments — for example, 6 monthly payments of $100 instead of $600 upfront. This makes expensive products and courses more affordable and can increase conversions.

= Can I run multiple donation campaigns at the same time? =
Yes. Create as many donation forms as you need, each with its own goal amount and progress tracker. Every campaign tracks independently so you can run different fundraisers simultaneously on your site.

= Can I show a progress bar on my donation page? =
Yes. WPSmartPay can display a live progress bar that updates automatically each time a new donation is received, showing donors how much has been raised toward your campaign goal.

= Does WPSmartPay take a percentage of my donations or sales? =
No. WPSmartPay charges zero transaction fees on top of what your gateway charges. The only fees you pay are the standard processing fees from your payment gateway (for example, Stripe charges 2.9% + $0.30 per transaction in the US). All funds go directly to your own account.

= Can I use multiple payment gateways at the same time? =
Yes. Enable as many gateways as you need and let customers choose their preferred payment method at checkout. For example, you can offer both Stripe and PayPal on the same form so customers can pick whichever they prefer.

= Can I sell online courses with WPSmartPay? =
Yes, with WPSmartPay Pro. The plugin integrates with LearnDash, Tutor LMS, and LifterLMS. When a student completes payment, they are automatically enrolled in the course — no manual steps required.

= What currencies does WPSmartPay support? =
WPSmartPay supports over 157 currencies. Set your default currency in the general settings to accept payments from customers worldwide in their local currency.

= Does WPSmartPay work with my WordPress theme and page builder? =
Yes. WPSmartPay is designed to work with all standard WordPress themes and popular page builders. Payment forms inherit your theme styles and look like a natural part of your website.

= Can I connect WPSmartPay to email marketing tools like Mailchimp? =
Yes, with WPSmartPay Pro. WPSmartPay integrates with Mailchimp, FluentCRM, MailerLite, and ActiveCampaign. When a customer makes a payment, their information is automatically added to your email list with the correct tags and segments.

= What happens when a subscription renewal payment fails? =
WPSmartPay Pro includes a built-in payment recovery system. When a renewal fails, the system automatically retries the charge using smart retry logic and notifies the customer to update their payment details. The Recovery Report tracks all failed and recovered payments so you can see exactly how much revenue was saved.

= Can I track which payment forms generate the most revenue? =
Yes, with WPSmartPay Pro. The Forms Report shows views, payments, and conversion rates for each form, so you can identify your top performers and focus on what works best.

= Can I add a surcharge or percentage fee to specific payment methods? =
Yes. WPSmartPay Pro includes a tax and surcharge system that lets you add percentage-based or fixed-amount fees globally or per form. The surcharge line item is displayed clearly on the checkout page and in receipts so customers see exactly what they are paying for.

== Screenshots ==
1. Dashboard - revenue overview, payment stats, and a quick-start checklist.
2. Payment form builder with native Gutenberg blocks and a checkout layout picker.
3. Form template library - pick a template or start from scratch.
4. Invoice management - create, send, and track invoices.
5. Payment gateway settings - connect PayPal, Stripe, Authorize.net, and more.
6. Reports dashboard with revenue charts and payment metrics.
7. Anti-spam settings for Turnstile, hCaptcha, and reCAPTCHA v3.
8. Guided onboarding wizard for new users.
9. Support page with system info, docs, and debug log.
10. Payment form preview

== Changelog ==

= 3.2.9 =
* Fix - Integrations page toggle is now disabled and shows a warning icon when the required third-party plugin (e.g. Restrict Content Pro, LearnDash, LifterLMS, WishlistMember, AffiliateWP) is not installed — prevents silently activating an integration that cannot work
* Fix - Settings → Extensions: navigating to an integration's settings page when its plugin is missing now shows a clear "Required plugin not installed" notice instead of silently displaying another integration's settings
* Fix - Settings → Extensions sub-section navigation changed from a horizontal tab row to a vertical sidebar list, scaling correctly as more integrations are added
* Fix - Stripe test mode is no longer reset to Live on every plugin activation or update — fixed merge order in the settings defaults so existing admin values always win over defaults
* Fix - Settings sanitizer now explicitly saves `0` for switch-type toggles (e.g. Test Mode) instead of deleting the key from the database, preventing unexpected mode shifts after a settings save

= 3.2.8 =
* New - WordPress Playground Live Preview blueprint — try WP SmartPay instantly on WP.org with a pre-configured "Quick Donation" form, PayPal gateway, and sample donation data

= 3.2.7 =
* Fix - Integration enable/disable checkboxes (e.g. WP User Registration, Slack, Telegram, Google Sheets) now save and display correctly after toggling
* Fix - Frontend scripts reduced from 880 KB to under 60 KB — only the Bootstrap Modal component is loaded, not the full Bootstrap bundle
* Fix - Single-gateway checkout form fields now render inside a proper wrapper, preventing layout shifts when only one payment method is active
* Fix - Admin footer no longer throws a PHP TypeError on non-admin pages when WordPress passes a null value

= 3.2.6 =
* Fix - PayPal API key and unsupported-currency admin notices now scoped to SmartPay pages only — no longer appear on unrelated admin screens
* Fix - Row-action dropdowns in table cards no longer clipped by overflow:hidden — menus render correctly without being cut off
* Fix - Integration toggle AJAX action renamed to `smartpay_toggle_integration_activation` to prevent nonce failure conflict with SmartMembers plugin
* Fix - `apiVersion: 3` added to smartpay/form and smartpay/product block registrations — suppresses WP 6.9 deprecation warning on every admin page load
* Fix - Required textarea fields (e.g. message field) now show inline validation error directly below the field instead of silently blocking form submission
* Fix - Product create/update now validates title (non-empty) and price (> 0) before saving — removes silent "Untitled product" fallback
* Fix - Support page resource links corrected (contact, developer docs, leave-a-review URLs)

= 3.2.5 =
* New - Name Fields and Address Fields blocks now have a "Layout Columns" setting (Auto / 1 / 2 / 3) in the block sidebar, applied identically in the editor and on the frontend
* Fix - Layout Columns setting now works in both the form editor preview and on the frontend; Bootstrap's flex row no longer overrides the grid display
* Fix - Quick-insert strip below the form editor canvas removed; fields are now added exclusively through the Guide modal
* Fix - Form editor canvas now renders full-width, eliminating the column count mismatch between editor and frontend
* Fix - Field spacing (margin-bottom) now matches between the block editor canvas and the embedded form on the frontend
* Fix - Deleted blocks no longer reappear when the editor is reopened — the form autosave is cleared after each real save
* Fix - A visual divider now appears before the Settings item in the SmartPay admin submenu
* Fix - Integration card toggles and gear icon now align correctly at all viewport widths; focus ring sized to the switch control
* Fix - Payment Gateways settings page now uses the same pill-switch toggle UI as the Integrations page — replaced Bootstrap custom-switch with sp-switch
* Fix - Upgrade and learn-more URLs across the admin UI corrected to point to the right destination pages
* Update - Tested up to WordPress 7.1
* Update - Security: bump brace-expansion dependency (CVE fix)

= 3.2.4 =
* New - Live Preview on WordPress.org — try WPSmartPay in a working demo site straight from the plugin listing, no install required
* Update - Payment Form and Product blocks now ship block.json metadata, improving block registration and editor performance
* Update - Admin menu grouping and dividers now ship with the free plugin, so the SmartPay sidebar reads the same whether or not an add-on is active
* Update - Pricing block: Allow Custom Amount moved alongside the price options, where the packages are, instead of sitting in a separate settings panel
* Update - Pricing block: colour options consolidated into the single Color group on the Styles tab, and now offer the theme palette
* Fix - Complete Profile form no longer rejects a correct current password containing characters such as < or >, or leading and trailing spaces
* Fix - Multiple payment forms on a single page now work independently; form scripts are scoped to the surrounding form instead of a page-wide element ID
* Fix - Allow Custom Amount toggle restored to the form editor Document sidebar, with an optional Custom Amount Label field
* Fix - Forms with no preset amounts now render the custom amount input instead of hiding the amount section
* Fix - Customer dashboard and profile pages no longer redirect logged-in users who have no payment history
* Fix - Dashboard and profile redirects now stop execution, so page content is no longer rendered behind the redirect header

= 3.2.3 =
* Fix - Database migrations now only run in wp-admin, WP-CLI, and cron; a version sentinel prevents re-running when the schema is already current
* Fix - smartpay_settings option removed from WordPress alloptions autoload cache — no longer loaded on every frontend page
* Fix - Plugin settings global ($smartpay_options) now populated inside plugins_loaded so Pro and add-on filters apply correctly
* Fix - wp-admin/includes/plugin.php no longer loaded on frontend requests

= 3.2.2 =
* Fix - WPSmartPay Form Gutenberg block renamed and updated to list native CPT forms; renders the full embedded payment form instead of a popup button
* Fix - Invoices now appear before Payments in the WordPress admin sidebar (correct visual grouping with Dashboard and Forms)
* Fix - Added top spacing before "Select a payment method" label on native payment forms
* Fix - Shortcode column on the Forms list page now uses a dedicated copy icon button instead of a clickable code element
* New - Charity donation template auto-includes the goal progress bar block and pre-configures a funding goal

= 3.2.1 =
* New - Subscriptions, Reports, and Invoices locked pages now show blurred real screenshots as upgrade preview background

= 3.2.0 =
* New - Gateway settings tab redesigned as a visual card grid with instant AJAX enable/disable toggle
* New - Integration cards now show category, tier label, and a "needs setup" badge; gear icon dims when integration is inactive
* New - Deprecation banners on Products and Legacy Forms admin pages to guide users to the native form builder
* New - New Form modal and template library fully redesigned with ready-to-use templates
* New - Native form pricing block: Grid, List, and Compact layout presets; custom amount input; list-view tab; gap control
* New - Onboarding checklist and setup wizard improved: correct step order and clickable checklist items
* Fix - Payment gateways no longer appear in the Integrations list (they are in Gateway settings instead)
* Fix - 100% discount coupons now apply correctly at checkout
* Fix - Product page correctly shows available gateways when the parent product has a sale price of zero
* Fix - Undefined array key "align" PHP warning on form pages resolved
* Fix - Radio and checkbox options now align correctly on the frontend
* Fix - All REST controller inputs are sanitized and AJAX handlers hardened against unexpected input
* Fix - Plugin Check compatibility issues cleared; plugin name and PHP requirement updated to match wp.org guidelines
* Fix - Payment form assets now always load correctly on smartpay_form post type pages

= 3.1.0 =
* New - Per-form Checkout Layout setting (stacked or split) and a "Require Login to Checkout" toggle
* Fix - Prevent a fatal error when WP_Filesystem fails to initialize while writing to the debug log
* Fix - Single-gateway checkout no longer shows a bare "Pay Now" button with no payment fields
* Fix - Payment form preview no longer shows stale settings from an incomplete autosave
* Update - Refreshed readme and plugin screenshots

= 3.0.1 =
* New - Logo and banners added to plugin assets

= 3.0.0 =
* New - Rebuilt Gutenberg payment form builder with live block preview
* New - Form template library with ready-made templates across categories
* New - Invoice management: create, send, and track invoices from WP admin
* New - Advanced reports dashboard with revenue charts and date-range filters
* New - Anti-spam control: Cloudflare Turnstile, hCaptcha, reCAPTCHA v3
* New - Tax control system: percentage and fixed-amount rates, global or per-form
* New - Redesigned email templates with subject, heading, and content controls
* New - Dashboard redesign: revenue overview, recent payments, quick actions
* New - Guided onboarding wizard for currency, pages, and gateway setup
* New - Support page: system info, documentation links, and debug log
* New - Payment gateways managed from Settings - Payment Gateways; Pro gateway locked cards shown when Pro is not active
* New - Legacy form migrator: recovers pre-3.0 forms with stale block markup
* Improved - Entire admin UI redesigned with a modern, accessible interface
* Improved - Subscriptions, Payments, Customers, Coupons, Integrations, and Settings pages redesigned for speed and usability
* Fix - Legacy form builder no longer shows "invalid content" for migrated forms
* Fix - Pricing and submit blocks rebuilt to match current native form format

= 2.8.3 =
* Security - Direct file access protection added to all PHP files
* Fix - PHP 8.1 nullable parameter compatibility in framework classes
* Fix - WordPress Plugin Check compliance: escaping, i18n, sanitization
* Update - Tested up to WordPress 6.9

= 2.8.2 =
* Fix - Broken form issue

= 2.8.1 =
* Feature - Coupon validation (frontend and backend)
* Fix - Compatibility issue with Pro version
* Fix - Deprecation issues
* Update - Coupon list UI and UX
* Update - Documentation link

= 2.8.0 =
* Fix - Major security issues (nonces, escaping, sanitization)
* Fix - Deprecation issues
* Fix - Text domain warnings

= 2.7.13 =
* Fix - Input sanitization for form submission
* Fix - Text domain warning

= 2.7.12 =
* Fix - Add instructions to enable payment gateways in Pro version

= 2.7.0 =
* Add - Paytm payment gateway

= 2.6.7 =
* Add - toyyibPay payment gateway

= 2.6.1 =
* New - Mollie payment gateway
* New - Register custom payment gateway hook

= 2.5.0 =
* Add - Razorpay payment gateway

= 2.4.0 =
* New - Coupon system
* New - MailerLite integration
* New - Onboarding welcome flow

= 2.0.0 =
* New - Debug log settings
* New - Customer details page
* New - Monthly report page

= 1.1.0 =
* New - ReactJS admin SPA
* New - Gutenberg form builder
* New - Dashboard

= 1.0.0 =
* Initial stable release

== Upgrade Notice ==

= 3.2.4 =
Fixes multiple payment forms on one page, restores the Allow Custom Amount toggle to the form editor sidebar, renders donation-only forms correctly, and stops the customer dashboard redirecting users who have no payments yet. No database changes.

= 3.2.3 =
Performance update: eliminates database queries on every frontend page load, removes smartpay_settings from the alloptions autoload cache, and fixes settings initialisation order. No database changes.

= 3.2.2 =
Renames the WPSmartPay Form Gutenberg block to correctly list native forms and render the full embedded form; fixes admin sidebar grouping for Invoices; improves payment form UX. No database changes.

= 3.1.0 =
Adds a split checkout layout and require-login option for payment forms, plus a fix for a rare fatal error in debug logging. No database changes.

= 3.0.1 =
Minor update: adds plugin logo and banners. No database changes.

= 3.0.0 =
Major release: rebuilt form builder, invoice management, anti-spam, tax, redesigned dashboard and UI. All existing forms are automatically migrated.
