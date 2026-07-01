export const CATEGORIES = [
	{ slug: 'all',          label: 'All Forms' },
	{ slug: 'payment',      label: 'Payment Forms' },
	{ slug: 'donation',     label: 'Donation Forms' },
	{ slug: 'registration', label: 'Registration & Signup' },
	{ slug: 'event',        label: 'Event Forms' },
	{ slug: 'survey',       label: 'Survey & Feedback' },
	{ slug: 'contact',      label: 'Contact Forms' },
	{ slug: 'booking',      label: 'Booking & Appointments' },
]

// `fields` lists the input field types (excluding pricing/pay) and drives the
// card preview: icon strip + field count. The real, fully-configured block tree
// for each id is built server-side in NativeForm::get_template_definition().
export const TEMPLATES = [
	// ── Payment ────────────────────────────────────────────────
	{
		id: 1001,
		name: 'Simple Payment Form',
		category: 'payment',
		description: 'Minimal payment form — name, email and a single amount. The perfect starting point.',
		fields: [ 'name', 'email', 'submit' ],
	},
	{
		id: 1002,
		name: 'Product Order Form',
		category: 'payment',
		description: 'Collect contact, phone, quantity, shipping address and order notes, then choose a product tier (Basic / Standard / Premium).',
		fields: [ 'name', 'email', 'text', 'text', 'address', 'textarea', 'submit' ],
	},
	{
		id: 1003,
		name: 'Subscription Plans',
		category: 'payment',
		description: 'Let visitors pick a Monthly, Annual or Lifetime plan from a list layout, with a referral-source dropdown.',
		fields: [ 'name', 'email', 'select', 'submit' ],
	},

	// ── Donation ───────────────────────────────────────────────
	{
		id: 2001,
		name: 'Quick Donation',
		category: 'donation',
		description: 'Fast one-screen donation with preset amount tiers ($10–$100). Just name and email.',
		fields: [ 'name', 'email', 'submit' ],
	},
	{
		id: 2002,
		name: 'Charity Donation',
		category: 'donation',
		description: 'Full donation form — phone, giving frequency, a dedication message, anonymous + receipt options, and tiered amounts.',
		fields: [ 'name', 'email', 'text', 'radio', 'textarea', 'checkbox', 'submit' ],
	},

	// ── Registration ───────────────────────────────────────────
	{
		id: 3001,
		name: 'Newsletter Signup',
		category: 'registration',
		description: 'Free opt-in with interest checkboxes and a GDPR marketing-consent checkbox.',
		fields: [ 'name', 'email', 'checkbox', 'checkbox', 'submit' ],
	},
	{
		id: 3002,
		name: 'Membership Application',
		category: 'registration',
		description: 'Detailed application — full name (incl. middle), phone, membership level, mailing address, introduction, and tiered dues.',
		fields: [ 'name', 'email', 'text', 'select', 'address', 'textarea', 'submit' ],
	},

	// ── Event ──────────────────────────────────────────────────
	{
		id: 4001,
		name: 'Event Registration',
		category: 'event',
		description: 'Register attendees — phone, preferred date, ticket type and dietary requirements, with General / VIP / Group pricing.',
		fields: [ 'name', 'email', 'text', 'text', 'select', 'checkbox', 'submit' ],
	},
	{
		id: 4002,
		name: 'Conference Registration',
		category: 'event',
		description: 'In-depth conference signup — company, job title, date, primary track, add-on workshops, and Early Bird / Regular / Student tiers.',
		fields: [ 'name', 'email', 'text', 'text', 'text', 'radio', 'checkbox', 'submit' ],
	},

	// ── Survey ─────────────────────────────────────────────────
	{
		id: 5001,
		name: 'Customer Satisfaction Survey',
		category: 'survey',
		description: 'Measure satisfaction and recommendation likelihood with rating scales and an open comments box. Free to submit.',
		fields: [ 'name', 'email', 'radio', 'radio', 'textarea', 'submit' ],
	},
	{
		id: 5002,
		name: 'Product Feedback Form',
		category: 'survey',
		description: 'Rate product quality, pick favourite features, give an NPS-style score and detailed suggestions. Free to submit.',
		fields: [ 'name', 'email', 'radio', 'checkbox', 'radio', 'textarea', 'submit' ],
	},

	// ── Contact ────────────────────────────────────────────────
	{
		id: 6001,
		name: 'Contact & Payment Form',
		category: 'contact',
		description: 'Combined contact + payment — phone, inquiry type and message before a consultation fee.',
		fields: [ 'name', 'email', 'text', 'radio', 'textarea', 'submit' ],
	},
	{
		id: 6002,
		name: 'Service Request Form',
		category: 'contact',
		description: 'Capture project scope — company, service, budget range, desired start date and detailed requirements, with a project deposit.',
		fields: [ 'name', 'email', 'text', 'select', 'select', 'text', 'textarea', 'submit' ],
	},

	// ── Booking ────────────────────────────────────────────────
	{
		id: 7001,
		name: 'Appointment Booking',
		category: 'booking',
		description: 'Book an appointment — phone, service, preferred date and time, notes, and Standard / Extended duration pricing.',
		fields: [ 'name', 'email', 'text', 'select', 'text', 'select', 'textarea', 'submit' ],
	},
	{
		id: 7002,
		name: 'Table Reservation',
		category: 'booking',
		description: 'Reserve a table — phone, date, party size, seating preference and special requests, secured with a small deposit.',
		fields: [ 'name', 'email', 'text', 'text', 'text', 'radio', 'textarea', 'submit' ],
	},
]
