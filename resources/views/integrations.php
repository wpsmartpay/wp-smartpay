<?php defined('ABSPATH') || exit; ?>
<div class="smartpay">
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container smartpay-integrations">
        <div class="d-flex align-items-center justify-content-between py-1 px-4 mt-3 text-white bg-dark rounded-top">
            <div class="lh-100">
                <h2 class="text-white"><?php echo esc_html__('SmartPay - Integrations', 'smartpay'); ?></h2>
            </div>
            <div>
                <a class="btn btn-dark btn-sm" href="https://wpsmartpay.com/changelog/" target="_blank">v<?php echo esc_html(SMARTPAY_VERSION); ?></a>
            </div>
        </div>

        <div class="p-4 max-w-7xl mx-auto">

            <?php
            // Collect all categories and tiers for filter bar
            $all_integ = smartpay_integrations();
            $categories = [];
            $has_free = false;
            $has_pro  = false;
            foreach ($all_integ as $integ) {
                $cats = $integ['categories'] ?? [];
                foreach ($cats as $c) {
                    if (!in_array($c, $categories, true)) {
                        $categories[] = $c;
                    }
                }
                $type = $integ['type'] ?? 'pro';
                if ($type === 'free') $has_free = true;
                if ($type === 'pro')  $has_pro  = true;
            }
            ?>

            <?php if (!empty($categories)) : ?>
            <div class="sp-integrations-toolbar mb-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">

                <?php // Category filters — left side ?>
                <div class="sp-period-selector d-inline-flex flex-wrap gap-1">
                    <button type="button" class="sp-period-selector__item sp-period-selector__item--active" data-filter-category="all">
                        All
                    </button>
                    <?php foreach ($categories as $cat) : ?>
                    <button type="button" class="sp-period-selector__item" data-filter-category="<?php echo esc_attr($cat); ?>">
                        <?php echo esc_html($cat); ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <?php // Tier filter — right side ?>
                <div class="sp-period-selector d-inline-flex flex-wrap gap-1">
                    <button type="button" class="sp-period-selector__item sp-period-selector__item--active" data-filter-tier="all">
                        All Tiers
                    </button>
                    <button type="button" class="sp-period-selector__item" data-filter-tier="free">
                        Free
                    </button>
                    <button type="button" class="sp-period-selector__item" data-filter-tier="pro">
                        Pro
                    </button>
                </div>

            </div>
            <?php endif; ?>

            <div class="row" id="integration-grid">
                <?php foreach ($all_integ as $namespace => $integration) : ?>
                    <?php
                    $cats     = $integration['categories'] ?? [];
                    $type     = $integration['type'] ?? 'pro';
                    $cat_attr = implode(',', $cats);
                    ?>
                    <div class="col-lg-3 col-md-4 integration"
                         data-categories="<?php echo esc_attr($cat_attr); ?>"
                         data-tier="<?php echo esc_attr($type); ?>">
                        <div class="card p-3 mb-3 d-flex">
                            <div class="image m-0 mb-3 text-center" style="height: 90px;">
                                <img src="<?php echo esc_url($integration['cover']); ?>" class="img-fluid" alt="">
                            </div>
                            <div class="info">
                                <h3 class="name m-0 mb-1"><?php echo esc_html($integration['name']); ?></h3>
                                <p class="excerpt mb-1"><?php echo wp_kses_post($integration['excerpt']); ?></p>
                            </div>

                            <?php if (!empty($cats)) : ?>
                            <div class="mb-2 d-flex flex-wrap gap-1">
                                <?php foreach ($cats as $cat) : ?>
                                <span class="sp-badge sp-badge--neutral"><?php echo esc_html($cat); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <div class="actions d-flex align-items-center mt-auto border-top pt-2 w-100">
                                <?php $activated = in_array($namespace, smartpay_get_activated_integrations()); ?>
                                <?php if (smartpay_integration_is_installed($integration)) : ?>
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox" class="custom-control-input" id="<?php echo 'integration_' . esc_attr($namespace); ?>" data-namespace="<?php echo esc_attr($namespace); ?>" <?php echo $activated ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="<?php echo 'integration_' . esc_attr($namespace); ?>">
                                        </label>
                                    </div>
                                    <div class="d-flex bd-highlight">
                                        <span class="ml-2 integration-status p-2 bd-highlight">
                                            <?php echo $activated ? esc_html__('Activated', 'smartpay') : esc_html__('Disabled', 'smartpay'); ?>
                                        </span>
                                        <?php if (!empty($integration['setting_link']) && $activated == true) : ?>
                                            <span class="ml-auto p-2 bd-highlight">
                                                <a href="<?php echo esc_url(site_url()); ?>/wp-admin/admin.php?page=smartpay-setting&<?php echo esc_attr($integration['setting_link']); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                                                        <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
                                                    </svg>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <?php smartpay_integration_get_not_installed_message($type); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
        var cards = grid.querySelectorAll('.integration');
        cards.forEach(function (card) {
            var cats  = (card.dataset.categories || '').split(',').filter(Boolean);
            var tier  = card.dataset.tier || 'pro';

            var catOk = activeCategory === 'all' || cats.includes(activeCategory);
            var tierOk = activeTier === 'all' || tier === activeTier;

            card.style.display = (catOk && tierOk) ? '' : 'none';
        });
    }

    // Category buttons
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

    // Tier buttons
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