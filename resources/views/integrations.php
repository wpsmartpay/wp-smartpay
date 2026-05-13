<?php defined('ABSPATH') || exit; ?>
<div class="smartpay">
    <div class="container-full">
        <div class="wrap" style="display:none">
            <h1 class="wp-heading-inline"></h1>
        </div>

        <div class="smartpay-page-header">
            <div class="smartpay-page-header__inner">
                <div class="smartpay-page-header__text">
                    <h2 class="smartpay-page-header__title"><?php esc_html_e( 'Integrations', 'smartpay' ); ?></h2>
                    <p class="smartpay-page-header__subtitle"><?php esc_html_e( 'Manage your payment gateways and extensions', 'smartpay' ); ?></p>
                </div>
                <div class="smartpay-page-header__actions">
                    <div class="smartpay-page-header__logo">
                        <img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="SmartPay" />
                    </div>
                </div>
            </div>
        </div>

        <div class="sp-content-wide smartpay-integrations">

            <?php
            $all_integ  = smartpay_integrations();
            $categories = [];
            foreach ($all_integ as $integ) {
                foreach ($integ['categories'] ?? [] as $c) {
                    if (!in_array($c, $categories, true)) {
                        $categories[] = $c;
                    }
                }
            }
            ?>

            <?php if (!empty($categories)) : ?>
            <div class="sp-integrations-toolbar mb-4 flex flex-wrap items-center justify-between">
                <div class="sp-period-selector">
                    <button type="button" class="sp-period-selector__item sp-period-selector__item--active" data-filter-category="all">
                        <?php esc_html_e( 'All', 'smartpay' ); ?>
                    </button>
                    <?php foreach ($categories as $cat) : ?>
                    <button type="button" class="sp-period-selector__item" data-filter-category="<?php echo esc_attr($cat); ?>">
                        <?php echo esc_html($cat); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <div class="sp-period-selector">
                    <button type="button" class="sp-period-selector__item sp-period-selector__item--active" data-filter-tier="all">
                        <?php esc_html_e( 'All Tiers', 'smartpay' ); ?>
                    </button>
                    <button type="button" class="sp-period-selector__item" data-filter-tier="free">
                        <?php esc_html_e( 'Free', 'smartpay' ); ?>
                    </button>
                    <button type="button" class="sp-period-selector__item" data-filter-tier="pro">
                        <?php esc_html_e( 'Pro', 'smartpay' ); ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <div class="sp-grid sp-grid--4" id="integration-grid">
                <?php foreach ($all_integ as $namespace => $integration) :
                    $cats     = $integration['categories'] ?? [];
                    $type     = $integration['type'] ?? 'pro';
                    $cat_attr = implode(',', $cats);
                    $activated = in_array($namespace, smartpay_get_activated_integrations());
                ?>
                    <div class="integration"
                        data-categories="<?php echo esc_attr($cat_attr); ?>"
                        data-tier="<?php echo esc_attr($type); ?>">
                        <div class="bg-white border border-border rounded-lg p-4 flex flex-col h-full transition-colors hover:border-gray-300">

                            <div class="flex items-center justify-center h-14 mb-3">
                                <img src="<?php echo esc_url($integration['cover']); ?>"
                                    class="max-h-full max-w-full object-contain" alt="">
                            </div>

                            <div class="flex-1 mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 mb-1"><?php echo esc_html($integration['name']); ?></h3>
                                <p class="text-xs text-muted-foreground leading-relaxed"><?php echo wp_kses_post($integration['excerpt']); ?></p>
                            </div>

                            <?php if (!empty($cats)) : ?>
                            <div class="flex flex-wrap gap-1 mb-3">
                                <?php foreach ($cats as $cat) : ?>
                                <span class="sp-badge sp-badge--neutral"><?php echo esc_html($cat); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <div class="flex items-center mt-auto border-t border-border pt-3">
                                <?php if (smartpay_integration_is_installed($integration)) : ?>
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="<?php echo 'integration_' . esc_attr($namespace); ?>"
                                            data-namespace="<?php echo esc_attr($namespace); ?>"
                                            <?php echo $activated ? 'checked' : ''; ?>>
                                        <label class="custom-control-label"
                                            for="<?php echo 'integration_' . esc_attr($namespace); ?>">
                                        </label>
                                    </div>
                                    <span class="ml-2 text-xs text-muted-foreground">
                                        <?php echo $activated ? esc_html__('Activated', 'smartpay') : esc_html__('Disabled', 'smartpay'); ?>
                                    </span>
                                    <?php if (!empty($integration['setting_link']) && $activated) : ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=smartpay-setting&' . $integration['setting_link'])); ?>"
                                        class="ml-auto text-muted-foreground hover:text-foreground transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <?php smartpay_integration_get_not_installed_message($type); ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php wp_nonce_field('smartpay_integrations_toggle_activation', 'smartpay_integrations_toggle_activation'); ?>
        </div>
    </div>
</div>

<script>
(function () {
    var grid = document.getElementById('integration-grid');
    if (!grid) return;

    var activeCategory = 'all';
    var activeTier     = 'all';

    function applyFilters () {
        var cards = grid.querySelectorAll('.integration');
        cards.forEach(function (card) {
            var cats  = (card.dataset.categories || '').split(',').filter(Boolean);
            var tier  = card.dataset.tier || 'pro';
            var catOk = activeCategory === 'all' || cats.includes(activeCategory);
            var tierOk = activeTier === 'all' || tier === activeTier;
            card.style.display = (catOk && tierOk) ? '' : 'none';
        });
    }

    document.querySelectorAll('[data-filter-category]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeCategory = btn.dataset.filterCategory;
            document.querySelectorAll('[data-filter-category]').forEach(function (b) {
                b.classList.remove('sp-period-selector__item--active');
            });
            btn.classList.add('sp-period-selector__item--active');
            applyFilters();
        });
    });

    document.querySelectorAll('[data-filter-tier]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeTier = btn.dataset.filterTier;
            document.querySelectorAll('[data-filter-tier]').forEach(function (b) {
                b.classList.remove('sp-period-selector__item--active');
            });
            btn.classList.add('sp-period-selector__item--active');
            applyFilters();
        });
    });
}());
</script>
