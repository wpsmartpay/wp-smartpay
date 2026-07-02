<?php defined('ABSPATH') || exit; ?>
<div class="smartpay">
    <div class="wrap" style="display:none">
        <h1 class="wp-heading-inline"></h1>
    </div>

    <div class="smartpay-page-header">
        <div class="smartpay-page-header__inner">
            <div class="smartpay-page-header__logo">
                <img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo-lockup-color.png' ); ?>" alt="SmartPay" />
            </div>
            <div class="smartpay-page-header__actions">
                <a href="https://wpsmartpay.com/docs/" target="_blank" rel="noopener noreferrer"
                    class="smartpay-page-header__help-btn"
                    title="<?php esc_attr_e( 'Help &amp; Documentation', 'smartpay' ); ?>"
                    aria-label="<?php esc_attr_e( 'Open help documentation', 'smartpay' ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" style="opacity:.7" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    <?php esc_html_e( 'Help', 'smartpay' ); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="sp-layout smartpay-integrations">

        <?php
        $smartpay_all_integ  = smartpay_integrations();
        $smartpay_categories = [];
        foreach ($smartpay_all_integ as $smartpay_integ) {
            // Skip gateway integrations when building category tabs — gateways live in Settings.
            if ( in_array( 'Payment Gateway', $smartpay_integ['categories'] ?? [], true ) ) {
                continue;
            }
            foreach ($smartpay_integ['categories'] ?? [] as $smartpay_c) {
                if (!in_array($smartpay_c, $smartpay_categories, true)) {
                    $smartpay_categories[] = $smartpay_c;
                }
            }
        }
        ?>

        <?php if (!empty($smartpay_categories)) : ?>
        <div class="sp-integ-toolbar">
            <div class="sp-filter-tabs">
                <button type="button" class="sp-filter-tab sp-filter-tab--active" data-filter-category="all">
                    <?php esc_html_e( 'All', 'smartpay' ); ?>
                </button>
                <?php foreach ($smartpay_categories as $smartpay_cat) : ?>
                <button type="button" class="sp-filter-tab" data-filter-category="<?php echo esc_attr($smartpay_cat); ?>">
                    <?php echo esc_html($smartpay_cat); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="sp-filter-tabs" style="display:none;">
                <button type="button" class="sp-filter-tab sp-filter-tab--active" data-filter-tier="all">
                    <?php esc_html_e( 'All Tiers', 'smartpay' ); ?>
                </button>
                <button type="button" class="sp-filter-tab" data-filter-tier="free">
                    <?php esc_html_e( 'Free', 'smartpay' ); ?>
                </button>
                <button type="button" class="sp-filter-tab" data-filter-tier="pro">
                    <?php esc_html_e( 'Pro', 'smartpay' ); ?>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="sp-integ-grid" id="integration-grid">
            <?php foreach ($smartpay_all_integ as $smartpay_namespace => $smartpay_integration) :
                $smartpay_cats      = $smartpay_integration['categories'] ?? [];
                $smartpay_type      = $smartpay_integration['type'] ?? 'pro';
                $smartpay_cat_attr  = implode(',', $smartpay_cats);
                $smartpay_activated = in_array($smartpay_namespace, smartpay_get_activated_integrations());

                // Gateways belong in Settings > Payment Gateways, not here.
                if ( in_array( 'Payment Gateway', $smartpay_cats, true ) ) {
                    continue;
                }
            ?>
                <div class="sp-integ-card"
                    data-categories="<?php echo esc_attr($smartpay_cat_attr); ?>"
                    data-tier="<?php echo esc_attr($smartpay_type); ?>">

                    <div class="sp-integ-card__logo">
                        <img src="<?php echo esc_url($smartpay_integration['cover']); ?>" alt="">
                    </div>

                    <div class="sp-integ-card__body">
                        <p class="sp-integ-card__name"><?php echo esc_html($smartpay_integration['name']); ?></p>
                        <p class="sp-integ-card__desc"><?php echo wp_kses_post($smartpay_integration['excerpt']); ?></p>
                    </div>

                    <?php if (!empty($smartpay_cats)) : ?>
                    <div class="sp-integ-card__tags">
                        <?php foreach ($smartpay_cats as $smartpay_cat) : ?>
                        <span class="sp-badge sp-badge--neutral"><?php echo esc_html($smartpay_cat); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="sp-integ-card__footer">
                        <?php if (smartpay_integration_is_installed($smartpay_integration)) : ?>
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="<?php echo 'integration_' . esc_attr($smartpay_namespace); ?>"
                                    data-namespace="<?php echo esc_attr($smartpay_namespace); ?>"
                                    <?php echo $smartpay_activated ? 'checked' : ''; ?>>
                                <label class="custom-control-label"
                                    for="<?php echo 'integration_' . esc_attr($smartpay_namespace); ?>">
                                </label>
                            </div>
                            <span class="sp-integ-card__status">
                                <?php echo $smartpay_activated ? esc_html__('Activated', 'smartpay') : esc_html__('Disabled', 'smartpay'); ?>
                            </span>
                            <?php if (!empty($smartpay_integration['setting_link']) && $smartpay_activated) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=smartpay-setting&' . $smartpay_integration['setting_link'])); ?>"
                                    class="sp-integ-card__settings"
                                    title="<?php esc_attr_e('Settings', 'smartpay'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php smartpay_integration_get_not_installed_message($smartpay_type); ?>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <?php wp_nonce_field('smartpay_integrations_toggle_activation', 'smartpay_integrations_toggle_activation'); ?>
    </div>
</div>

<script>
(function () {
    var grid = document.getElementById('integration-grid');
    if (!grid) return;

    var activeCategory = 'all';
    var activeTier     = 'all';

    function applyFilters () {
        var cards = grid.querySelectorAll('.sp-integ-card');
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
                b.classList.remove('sp-filter-tab--active');
            });
            btn.classList.add('sp-filter-tab--active');
            applyFilters();
        });
    });

    document.querySelectorAll('[data-filter-tier]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeTier = btn.dataset.filterTier;
            document.querySelectorAll('[data-filter-tier]').forEach(function (b) {
                b.classList.remove('sp-filter-tab--active');
            });
            btn.classList.add('sp-filter-tab--active');
            applyFilters();
        });
    });
}());
</script>
