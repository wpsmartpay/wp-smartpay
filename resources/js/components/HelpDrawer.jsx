import { __ } from '@wordpress/i18n'
import { ExternalLink, BookOpen, Code2, Zap, CreditCard, FileText, Package, LayoutGrid } from 'lucide-react'
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from './ui/sheet'

const DOC_SECTIONS = [
    {
        heading: __( 'Getting Started', 'smartpay' ),
        links: [
            {
                label: __( 'Main Documentation', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/category/wpsmartpay',
                icon:  BookOpen,
            },
            {
                label: __( 'Available Shortcodes', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/available-shortcodes',
                icon:  LayoutGrid,
            },
        ],
    },
    {
        heading: __( 'Configuration', 'smartpay' ),
        links: [
            {
                label: __( 'Product Configuration', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/product-configuration',
                icon:  Package,
            },
            {
                label: __( 'Form Configuration', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/form-configuration',
                icon:  FileText,
            },
            {
                label: __( 'How to Setup Stripe', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/how-to-setup-stripe',
                icon:  CreditCard,
            },
        ],
    },
    {
        heading: __( 'Extend', 'smartpay' ),
        links: [
            {
                label: __( 'Integrations', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/developer-integration',
                icon:  Zap,
            },
            {
                label: __( 'Developer Docs', 'smartpay' ),
                url:   'https://docs.wpsmartpay.com/en/category/developer',
                icon:  Code2,
            },
        ],
    },
]

/**
 * HelpDrawer — slides in from the right with categorised documentation links.
 *
 * @param {{ open: boolean, onOpenChange: (open: boolean) => void }} props
 */
export function HelpDrawer( { open, onOpenChange } ) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent side="right" className="w-80 sm:max-w-xs overflow-y-auto">
                <SheetHeader className="pb-4 border-b border-border">
                    <SheetTitle className="flex items-center gap-2 text-base">
                        <BookOpen className="h-4 w-4 text-muted-foreground" />
                        {__( 'Help & Documentation', 'smartpay' )}
                    </SheetTitle>
                    <SheetDescription className="text-xs">
                        {__( 'Guides, references and developer docs for WPSmartPay.', 'smartpay' )}
                    </SheetDescription>
                </SheetHeader>

                <nav className="flex flex-col gap-6 px-6 pt-2 pb-5" aria-label={__( 'Documentation links', 'smartpay' )}>
                    {DOC_SECTIONS.map( ( section ) => (
                        <div key={section.heading}>
                            <p className="mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground/60">
                                {section.heading}
                            </p>
                            <ul className="flex flex-col gap-0.5 list-none m-0 p-0">
                                {section.links.map( ( { label, url, icon: Icon } ) => (
                                    <li key={label} className="m-0 p-0">
                                        <a
                                            href={url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="group flex items-center gap-3 rounded-md px-3 py-2 text-sm text-foreground/80 hover:bg-muted hover:text-foreground transition-colors no-underline"
                                        >
                                            <Icon className="h-4 w-4 flex-shrink-0 text-muted-foreground group-hover:text-foreground transition-colors" />
                                            <span className="flex-1 leading-snug">{label}</span>
                                            <ExternalLink className="h-3 w-3 flex-shrink-0 opacity-0 group-hover:opacity-40 transition-opacity" />
                                        </a>
                                    </li>
                                ) )}
                            </ul>
                        </div>
                    ) )}
                </nav>

                <div className="mt-auto px-6 pb-6 pt-4 border-t border-border">
                    <a
                        href="https://wpsmartpay.com/support/"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-center justify-center gap-2 w-full rounded-md border border-border bg-muted/40 px-4 py-2 text-sm text-muted-foreground hover:bg-muted hover:text-foreground transition-colors no-underline"
                    >
                        {__( 'Contact Support', 'smartpay' )}
                        <ExternalLink className="h-3.5 w-3.5 opacity-60" />
                    </a>
                </div>
            </SheetContent>
        </Sheet>
    )
}
