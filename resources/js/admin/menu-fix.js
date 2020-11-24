jQuery(function ($) {
    const menuRoot = $('#toplevel_page_smartpay')
    const currentUrl = window.location.href
    const currentPath = currentUrl.substr(currentUrl.indexOf('admin.php'))

    menuRoot.on('click', 'a', function () {
        $('ul.wp-submenu li', menuRoot).removeClass('current')
        if ($(this).hasClass('wp-has-submenu')) {
            $('li.wp-first-item', menuRoot).addClass('current')
        } else {
            $(this).parents('li').addClass('current')
        }
    })

    $('ul.wp-submenu li', menuRoot).removeClass('current')
    $('ul.wp-submenu a', menuRoot).each(function (index, el) {
        if ($(el).attr('href') === currentPath) {
            $(el).parent().addClass('current')
            return
        }
    })
})
