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

            if (hrefHash === hash) {
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
