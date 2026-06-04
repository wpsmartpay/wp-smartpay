export const CATEGORIES = [
	{ slug: 'all',          label: 'All Forms' },
	{ slug: 'payment',      label: 'Payment Forms' },
	{ slug: 'donation',     label: 'Donation Forms' },
	{ slug: 'registration', label: 'Registration & Signup' },
	{ slug: 'event',        label: 'Event Forms' },
	{ slug: 'survey',       label: 'Survey & Feedback' },
	{ slug: 'contact',      label: 'Contact Forms' },
]

export const TEMPLATES = [
	// ── Payment ────────────────────────────────────────────────
	{
		id: 1001,
		name: 'Simple Payment Form',
		category: 'payment',
		description: 'Minimal payment form with name and email. Perfect starting point.',
		fields: [ 'name', 'email', 'submit' ],
	},
	{
		id: 1002,
		name: 'Product Order Form',
		category: 'payment',
		description: 'Collect customer details, phone, company and order notes before payment.',
		fields: [ 'name', 'email', 'text', 'text', 'textarea', 'submit' ],
	},
	{
		id: 1003,
		name: 'Invoice Payment Form',
		category: 'payment',
		description: 'Allow customers to pay a specific invoice by entering their invoice number.',
		fields: [ 'name', 'email', 'text', 'textarea', 'submit' ],
	},
	{
		id: 1004,
		name: 'Subscription Signup',
		category: 'payment',
		description: 'Let visitors choose a subscription plan — monthly, annual or lifetime.',
		fields: [ 'name', 'email', 'select', 'textarea', 'submit' ],
	},

	// ── Donation ───────────────────────────────────────────────
	{
		id: 2001,
		name: 'Simple Donation Form',
		category: 'donation',
		description: 'Choose donation amount, frequency (one-time or recurring), optional anonymous donation and a personal message.',
		fields: [ 'name', 'email', 'select', 'radio', 'checkbox', 'textarea', 'submit' ],
	},
	{
		id: 2002,
		name: 'Charity Donation',
		category: 'donation',
		description: 'Full-featured charity donation form with phone, tiered amounts, frequency option and purpose message.',
		fields: [ 'name', 'email', 'text', 'select', 'radio', 'textarea', 'submit' ],
	},
	{
		id: 2003,
		name: 'Nonprofit Donation',
		category: 'donation',
		description: 'Collect full mailing address for donation receipts, select amount, and allow anonymous donations.',
		fields: [ 'name', 'email', 'address', 'select', 'checkbox', 'submit' ],
	},

	// ── Registration ───────────────────────────────────────────
	{
		id: 3001,
		name: 'Event Registration',
		category: 'registration',
		description: 'Register attendees for an event — name, email, phone, ticket type and dietary requirements.',
		fields: [ 'name', 'email', 'text', 'select', 'text', 'submit' ],
	},
	{
		id: 3002,
		name: 'Newsletter Signup',
		category: 'registration',
		description: 'Simple newsletter opt-in with interest selection and GDPR consent checkbox.',
		fields: [ 'name', 'email', 'select', 'checkbox', 'submit' ],
	},
	{
		id: 3003,
		name: 'Course Enrollment',
		category: 'registration',
		description: 'Enroll students in a course — select track, experience level and motivational message.',
		fields: [ 'name', 'email', 'text', 'select', 'select', 'textarea', 'submit' ],
	},
	{
		id: 3004,
		name: 'Membership Application',
		category: 'registration',
		description: 'Full membership application with contact details, plan selection, address and introduction.',
		fields: [ 'name', 'email', 'text', 'select', 'address', 'textarea', 'submit' ],
	},

	// ── Event ──────────────────────────────────────────────────
	{
		id: 4001,
		name: 'Conference Registration',
		category: 'event',
		description: 'Register conference attendees — company, job title, ticket type and preferred session.',
		fields: [ 'name', 'email', 'text', 'text', 'select', 'radio', 'submit' ],
	},
	{
		id: 4002,
		name: 'Workshop Registration',
		category: 'event',
		description: 'Register participants for a workshop with session choice, dietary requirements and t-shirt opt-in.',
		fields: [ 'name', 'email', 'select', 'text', 'checkbox', 'submit' ],
	},
	{
		id: 4003,
		name: 'Webinar Registration',
		category: 'event',
		description: 'Sign up for a webinar — company, topic selection and email reminder preferences.',
		fields: [ 'name', 'email', 'text', 'select', 'checkbox', 'submit' ],
	},

	// ── Survey ─────────────────────────────────────────────────
	{
		id: 5001,
		name: 'Customer Satisfaction Survey',
		category: 'survey',
		description: 'Measure satisfaction, likelihood to recommend and gather improvement suggestions.',
		fields: [ 'name', 'email', 'radio', 'radio', 'textarea', 'submit' ],
	},
	{
		id: 5002,
		name: 'Product Feedback Form',
		category: 'survey',
		description: 'Rate product quality, recommendation likelihood, highlight favourite aspects and add comments.',
		fields: [ 'name', 'email', 'radio', 'radio', 'checkbox', 'textarea', 'submit' ],
	},

	// ── Contact ────────────────────────────────────────────────
	{
		id: 6001,
		name: 'Contact & Payment Form',
		category: 'contact',
		description: 'Combined contact and payment — phone, inquiry type and message before the payment step.',
		fields: [ 'name', 'email', 'text', 'radio', 'textarea', 'submit' ],
	},
	{
		id: 6002,
		name: 'Service Request Form',
		category: 'contact',
		description: 'Capture project scope — service type, budget, timeline and detailed requirements.',
		fields: [ 'name', 'email', 'select', 'text', 'text', 'textarea', 'submit' ],
	},
]
