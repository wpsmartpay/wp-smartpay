import { __ } from '@wordpress/i18n'

export const NotFound = () => {
    return (
        <div className="p-8 text-center text-muted-foreground">
            {__('Page not found.', 'smartpay')}
        </div>
    )
}
