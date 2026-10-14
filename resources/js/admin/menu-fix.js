jQuery(function ($) {
    const menuRoot = $('#toplevel_page_smartpay')

    /**
     * Map SPA hash paths to the WP submenu page slug fragment so we can
     * find and highlight the right menu item on hash changes.
     *
     * WP renders slugs like `smartpay#/products` as
     * `admin.php?page=smartpay%23%2Fproducts` in the href attribute.
     * We match by decoding the href and comparing the hash portion.
     */
    // Old list pages now live as tabs of the merged pages.
    const MERGED = { payments: 'transactions', donations: 'transactions', customers: 'contacts', donors: 'contacts' }

    // '#/payments/12?x=1' → 'transactions' (first path segment, merged).
    const section = (h) => {
        const first = (h.replace(/^#\/?/, '').split(/[/?]/)[0]) || ''
        return MERGED[first] || first
    }

    function activateMenuByHash() {
        const hash = window.location.hash || '#/'

        $('ul.wp-submenu li', menuRoot).removeClass('current')

        let matched = false

        $('ul.wp-submenu a', menuRoot).each(function () {
            const href    = decodeURIComponent($(this).attr('href') || '')
            // href examples:
            //   admin.php?page=smartpay          → dashboard
            //   admin.php?page=smartpay#/products → products
            const hrefHash = href.includes('#') ? href.substring(href.indexOf('#')) : '#/'

            if (hrefHash !== '#/' && section(hrefHash) === section(hash)) {
                $(this).parent().addClass('current')
                matched = true
                return false // break .each
            }
        })

        // Dashboard: hash is "#/" or "#" — highlight first item
        if (!matched && (hash === '#/' || hash === '#')) {
            $('li.wp-first-item', menuRoot).addClass('current')
        }
    }

    // Activate on initial page load
    activateMenuByHash()

    // Re-activate whenever the SPA hash changes (Quick Links, sidebar nav, etc.)
    $(window).on('hashchange', activateMenuByHash)

    // Also handle direct clicks on WP menu sidebar links
    menuRoot.on('click', 'a', function () {
        $('ul.wp-submenu li', menuRoot).removeClass('current')
        if ($(this).hasClass('wp-has-submenu')) {
            $('li.wp-first-item', menuRoot).addClass('current')
        } else {
            $(this).parents('li').addClass('current')
        }
    })
})
