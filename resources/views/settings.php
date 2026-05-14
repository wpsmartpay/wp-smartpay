<?php
defined('ABSPATH') || exit;

use SmartPay\Modules\Admin\Setting;

/* ── Bootstrap ──────────────────────────────────────────────── */

$setting_instance = new Setting();
$settings_tabs    = Setting::settings_tabs();
$all_settings     = Setting::get_registered_settings_sections();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
$active_tab = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';

$sections       = Setting::settings_tab_sections( $active_tab );
$key            = ! empty( $sections ) ? key( $sections ) : 'main';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_section = isset( $_GET['section'] )
	&& ! empty( $sections )
	&& array_key_exists( sanitize_text_field( wp_unslash( $_GET['section'] ) ), $sections )
		? sanitize_text_field( wp_unslash( $_GET['section'] ) )
		: $key;

/* ── Field extraction ───────────────────────────────────────── */

$registered  = Setting::registered_settings();
$tab_data    = $registered[ $active_tab ] ?? [];
$raw_fields  = [];

if ( isset( $tab_data[ $active_section ] ) && is_array( $tab_data[ $active_section ] ) ) {
	$raw_fields = array_values( $tab_data[ $active_section ] );
} elseif ( isset( $tab_data['main'] ) && is_array( $tab_data['main'] ) ) {
	$raw_fields = array_values( $tab_data['main'] );
} else {
	// Extensions: tab > section_id > fields array (no 'main' wrapper)
	foreach ( $tab_data as $s_id => $s_fields ) {
		if ( $s_id === $active_section && is_array( $s_fields ) ) {
			$raw_fields = array_values( $s_fields );
			break;
		}
	}
}

/* ── Group fields by `header` type → each group becomes a card ─ */

$field_defaults = [
	'section'       => $active_section,
	'id'            => null,
	'desc'          => '',
	'name'          => '',
	'size'          => null,
	'options'       => '',
	'std'           => '',
	'min'           => null,
	'max'           => null,
	'step'          => null,
	'chosen'        => null,
	'multiple'      => null,
	'placeholder'   => null,
	'allow_blank'   => true,
	'readonly'      => false,
	'faux'          => false,
	'tooltip_title' => false,
	'tooltip_desc'  => false,
	'field_class'   => '',
	'data'          => [],
	'style'         => '',
];

$groups    = [];
$cur_group = [ 'title' => '', 'fields' => [] ];

foreach ( $raw_fields as $field ) {
	if ( empty( $field['id'] ) ) {
		continue;
	}
	$field = wp_parse_args( $field, $field_defaults );

	if ( 'header' === $field['type'] ) {
		if ( ! empty( $cur_group['fields'] ) ) {
			$groups[] = $cur_group;
		}
		$cur_group = [ 'title' => wp_strip_all_tags( $field['name'] ), 'fields' => [] ];
	} else {
		$cur_group['fields'][] = $field;
	}
}
if ( ! empty( $cur_group['fields'] ) ) {
	$groups[] = $cur_group;
}

/* ── Render ─────────────────────────────────────────────────── */

ob_start();
?>
<div class="smartpay settings-<?php echo esc_attr( $active_tab ); ?>">
	<div class="wrap" style="display:none"><h1 class="wp-heading-inline"></h1></div>

	<div class="smartpay-page-header">
		<div class="smartpay-page-header__inner">
			<div class="smartpay-page-header__text">
				<h2 class="smartpay-page-header__title"><?php esc_html_e( 'Settings', 'smartpay' ); ?></h2>
				<p class="smartpay-page-header__subtitle"><?php esc_html_e( 'Configure your SmartPay preferences', 'smartpay' ); ?></p>
			</div>
			<div class="smartpay-page-header__actions">
				<div class="smartpay-page-header__logo">
					<img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="SmartPay" />
				</div>
			</div>
		</div>
	</div>

	<div class="sp-layout">

		<!-- Primary tab navigation -->
		<div class="sp-filter-tabs sp-settings-tabs">
			<?php foreach ( $settings_tabs as $tab_id => $tab_name ) :
				$tab_url   = esc_url( add_query_arg( [ 'tab' => $tab_id, 'settings-updated' => false ], remove_query_arg( 'section' ) ) );
				$is_active = ( $active_tab === $tab_id );
			?>
				<a href="<?php echo $tab_url; ?>"
					class="sp-filter-tab<?php echo $is_active ? ' sp-filter-tab--active' : ''; ?>">
					<?php echo esc_html( $tab_name ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $sections ) > 1 ) : ?>
		<!-- Sub-section navigation (e.g. Extensions → Stripe, Paddle…) -->
		<div class="sp-filter-tabs sp-settings-subtabs">
			<?php foreach ( $sections as $section_id => $section_name ) :
				$sec_url    = esc_url( add_query_arg( [ 'tab' => $active_tab, 'section' => $section_id, 'settings-updated' => false ] ) );
				$sec_active = ( $active_section === $section_id );
			?>
				<a href="<?php echo $sec_url; ?>"
					class="sp-filter-tab sp-filter-tab--sm<?php echo $sec_active ? ' sp-filter-tab--active' : ''; ?>">
					<?php echo esc_html( $section_name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<form method="POST" action="options.php">
			<?php settings_fields( 'smartpay_settings' ); ?>

			<?php if ( $active_section !== $key ) : ?>
				<input type="hidden" name="smartpay_section_override" value="<?php echo esc_attr( $active_section ); ?>" />
			<?php endif; ?>

			<div class="sp-settings-cards">

				<?php if ( empty( $groups ) ) : ?>
					<div class="sp-detail-card">
						<div class="sp-detail-card__body">
							<p style="font-size:13px;color:var(--sp-text-muted);margin:0;">
								<?php esc_html_e( 'No settings available for this section.', 'smartpay' ); ?>
							</p>
						</div>
					</div>

				<?php else : ?>
					<?php foreach ( $groups as $group ) : ?>
					<div class="sp-detail-card sp-settings-card">

						<?php if ( ! empty( $group['title'] ) ) : ?>
						<div class="sp-detail-card__header">
							<span class="sp-detail-card__title"><?php echo esc_html( $group['title'] ); ?></span>
						</div>
						<?php endif; ?>

						<div class="sp-detail-card__body sp-settings-card__body">
							<?php
							$total = count( $group['fields'] );
							foreach ( $group['fields'] as $i => $field ) :
								if ( empty( $field['id'] ) ) {
									continue;
								}
								$callback    = 'settings_' . $field['type'] . '_callback';
								if ( ! method_exists( $setting_instance, $callback ) ) {
									continue;
								}
								$is_last     = ( $i === $total - 1 );
								$is_fullwidth = in_array( $field['type'], [ 'textarea', 'descriptive_text', 'gateways' ], true );
							?>
							<div class="sp-settings-row<?php
								echo $is_last     ? ' sp-settings-row--last'      : '';
								echo $is_fullwidth ? ' sp-settings-row--fullwidth' : '';
							?>">
								<div class="sp-settings-row__left">
									<label class="sp-settings-row__label"
										for="smartpay_settings[<?php echo esc_attr( $field['id'] ); ?>]">
										<?php echo esc_html( $field['name'] ); ?>
									</label>
									<?php if ( ! empty( $field['desc'] ) && ! in_array( $field['type'], [ 'text', 'textarea', 'select', 'select_currency', 'gateway_select', 'page_select', 'checkbox', 'switch', 'gateways' ], true ) ) : ?>
										<p class="sp-settings-row__desc"><?php echo wp_kses_post( $field['desc'] ); ?></p>
									<?php endif; ?>
								</div>
								<div class="sp-settings-row__control">
									<?php $setting_instance->$callback( $field ); ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>

					</div>
					<?php endforeach; ?>
				<?php endif; ?>

			</div><!-- .sp-settings-cards -->

			<div class="sp-settings-actions">
				<?php if ( 'debug_log' === $active_tab ) : ?>
					<button type="button" class="sp-btn sp-btn--outline smartpay-clear-debug-log">
						<?php esc_html_e( 'Clear Log', 'smartpay' ); ?>
					</button>
				<?php else : ?>
					<input type="submit" name="submit" id="submit"
						class="sp-btn sp-btn--primary"
						value="<?php echo esc_attr__( 'Save Changes', 'smartpay' ); ?>" />
				<?php endif; ?>
			</div>

		</form>

	</div><!-- .sp-layout -->
</div>
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo ob_get_clean();
