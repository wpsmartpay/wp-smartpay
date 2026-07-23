<?php
defined('ABSPATH') || exit;

use SmartPay\Modules\Admin\Setting;

/* ── Bootstrap ──────────────────────────────────────────────── */

$smartpay_setting_instance = new Setting();
$smartpay_settings_tabs    = Setting::settings_tabs();
$smartpay_all_settings     = Setting::get_registered_settings_sections();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only settings navigation, no state change.
$smartpay_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
$smartpay_active_tab = array_key_exists( $smartpay_active_tab, $smartpay_settings_tabs ) ? $smartpay_active_tab : 'general';

$smartpay_sections       = Setting::settings_tab_sections( $smartpay_active_tab );
$smartpay_key            = ! empty( $smartpay_sections ) ? key( $smartpay_sections ) : 'main';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only settings navigation, no state change.
$smartpay_requested_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
$smartpay_active_section    = ( '' !== $smartpay_requested_section
	&& ! empty( $smartpay_sections )
	&& array_key_exists( $smartpay_requested_section, $smartpay_sections ) )
		? $smartpay_requested_section
		: $smartpay_key;

/* ── Field extraction ───────────────────────────────────────── */

$smartpay_registered  = Setting::registered_settings();
$smartpay_tab_data    = $smartpay_registered[ $smartpay_active_tab ] ?? [];
$smartpay_raw_fields  = [];

if ( isset( $smartpay_tab_data[ $smartpay_active_section ] ) && is_array( $smartpay_tab_data[ $smartpay_active_section ] ) ) {
	$smartpay_raw_fields = array_values( $smartpay_tab_data[ $smartpay_active_section ] );
} elseif ( isset( $smartpay_tab_data['main'] ) && is_array( $smartpay_tab_data['main'] ) ) {
	$smartpay_raw_fields = array_values( $smartpay_tab_data['main'] );
} else {
	// Extensions: tab > section_id > fields array (no 'main' wrapper)
	foreach ( $smartpay_tab_data as $smartpay_s_id => $smartpay_s_fields ) {
		if ( $smartpay_s_id === $smartpay_active_section && is_array( $smartpay_s_fields ) ) {
			$smartpay_raw_fields = array_values( $smartpay_s_fields );
			break;
		}
	}
}

/* ── Group fields by `header` type → each group becomes a card ─ */

$smartpay_field_defaults = [
	'section'       => $smartpay_active_section,
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

$smartpay_groups    = [];
$smartpay_cur_group = [ 'title' => '', 'fields' => [] ];

foreach ( $smartpay_raw_fields as $smartpay_field ) {
	if ( empty( $smartpay_field['id'] ) ) {
		continue;
	}
	$smartpay_field = wp_parse_args( $smartpay_field, $smartpay_field_defaults );

	if ( 'header' === $smartpay_field['type'] ) {
		if ( ! empty( $smartpay_cur_group['fields'] ) ) {
			$smartpay_groups[] = $smartpay_cur_group;
		}
		$smartpay_cur_group = [ 'title' => wp_strip_all_tags( $smartpay_field['name'] ), 'fields' => [] ];
	} else {
		$smartpay_cur_group['fields'][] = $smartpay_field;
	}
}
if ( ! empty( $smartpay_cur_group['fields'] ) ) {
	$smartpay_groups[] = $smartpay_cur_group;
}

/* ── Render ─────────────────────────────────────────────────── */

ob_start();
?>
<div class="smartpay settings-<?php echo esc_attr( $smartpay_active_tab ); ?>">
	<div class="wrap" style="display:none"><h1 class="wp-heading-inline"></h1></div>

	<?php settings_errors( 'smartpay-notices' ); ?>

	<div class="smartpay-page-header">
		<div class="smartpay-page-header__inner">
			<div class="smartpay-page-header__logo">
				<img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="SmartPay" />
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

	<div class="sp-layout">

		<!-- Primary tab navigation -->
		<div class="sp-filter-tabs sp-settings-tabs">
			<?php foreach ( $smartpay_settings_tabs as $smartpay_tab_id => $smartpay_tab_name ) :
				$smartpay_tab_url   = esc_url( add_query_arg( [ 'tab' => $smartpay_tab_id, 'settings-updated' => false ], remove_query_arg( 'section' ) ) );
				$smartpay_is_active = ( $smartpay_active_tab === $smartpay_tab_id );
			?>
				<a href="<?php echo esc_url( $smartpay_tab_url ); ?>"
					class="sp-filter-tab<?php echo $smartpay_is_active ? ' sp-filter-tab--active' : ''; ?>">
					<?php echo esc_html( $smartpay_tab_name ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $smartpay_sections ) > 1 && 'emails' !== $smartpay_active_tab ) : ?>
		<!-- Sub-section navigation (e.g. Extensions → Stripe, Paddle…) -->
		<div class="sp-filter-tabs sp-settings-subtabs">
			<?php foreach ( $smartpay_sections as $smartpay_section_id => $smartpay_section_name ) :
				$smartpay_sec_url    = esc_url( add_query_arg( [ 'tab' => $smartpay_active_tab, 'section' => $smartpay_section_id, 'settings-updated' => false ] ) );
				$smartpay_sec_active = ( $smartpay_active_section === $smartpay_section_id );
			?>
				<a href="<?php echo esc_url( $smartpay_sec_url ); ?>"
					class="sp-filter-tab sp-filter-tab--sm<?php echo $smartpay_sec_active ? ' sp-filter-tab--active' : ''; ?>">
					<?php echo esc_html( $smartpay_section_name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ( 'emails' === $smartpay_active_tab ) : ?>

			<div id="sp-email-templates">
				<p style="font-size:13px;color:var(--sp-text-muted);margin:0;padding:20px 0;">
					<?php esc_html_e( 'Loading email notifications…', 'smartpay' ); ?>
				</p>
			</div>

		<?php elseif ( 'antispam' === $smartpay_active_tab ) : ?>

			<form method="POST" action="options.php">
				<?php settings_fields( 'smartpay_settings' ); ?>

				<table class="form-table"><tbody>
					<?php foreach ( $smartpay_groups as $smartpay_group ) : ?>
						<?php foreach ( $smartpay_group['fields'] as $smartpay_field ) :
							if ( empty( $smartpay_field['id'] ) ) {
								continue;
							}
							$smartpay_callback = 'settings_' . $smartpay_field['type'] . '_callback';
							if ( ! method_exists( $smartpay_setting_instance, $smartpay_callback ) ) {
								continue;
							}
						?>
						<tr>
							<th scope="row">
								<label for="smartpay_settings[<?php echo esc_attr( $smartpay_field['id'] ); ?>]">
									<?php echo esc_html( $smartpay_field['name'] ); ?>
								</label>
							</th>
							<td><?php $smartpay_setting_instance->$smartpay_callback( $smartpay_field ); ?></td>
						</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody></table>

				<div class="sp-settings-actions">
					<input type="submit" name="submit" id="submit"
						class="sp-btn sp-btn--primary"
						value="<?php echo esc_attr__( 'Save Changes', 'smartpay' ); ?>" />
				</div>

			</form>

		<?php else : ?>

		<form method="POST" action="options.php">
			<?php settings_fields( 'smartpay_settings' ); ?>

			<?php if ( $smartpay_active_section !== $smartpay_key ) : ?>
				<input type="hidden" name="smartpay_section_override" value="<?php echo esc_attr( $smartpay_active_section ); ?>" />
			<?php endif; ?>

			<div class="sp-settings-cards">

				<?php if ( empty( $smartpay_groups ) ) : ?>
					<div class="sp-detail-card">
						<div class="sp-detail-card__body">
							<p style="font-size:13px;color:var(--sp-text-muted);margin:0;">
								<?php esc_html_e( 'No settings available for this section.', 'smartpay' ); ?>
							</p>
						</div>
					</div>

				<?php else : ?>
					<?php foreach ( $smartpay_groups as $smartpay_group ) : ?>
					<div class="sp-detail-card sp-settings-card">

						<?php if ( ! empty( $smartpay_group['title'] ) ) : ?>
						<div class="sp-detail-card__header">
							<span class="sp-detail-card__title"><?php echo esc_html( $smartpay_group['title'] ); ?></span>
						</div>
						<?php endif; ?>

						<div class="sp-detail-card__body sp-settings-card__body">
							<?php
							$smartpay_total = count( $smartpay_group['fields'] );
							foreach ( $smartpay_group['fields'] as $smartpay_i => $smartpay_field ) :
								if ( empty( $smartpay_field['id'] ) ) {
									continue;
								}
								$smartpay_callback     = 'settings_' . $smartpay_field['type'] . '_callback';
								if ( ! method_exists( $smartpay_setting_instance, $smartpay_callback ) ) {
									continue;
								}
								$smartpay_is_last      = ( $smartpay_i === $smartpay_total - 1 );
								$smartpay_is_fullwidth = in_array( $smartpay_field['type'], [ 'textarea', 'descriptive_text', 'gateways' ], true );
							?>
							<div class="sp-settings-row<?php
								echo $smartpay_is_last     ? ' sp-settings-row--last'      : '';
								echo $smartpay_is_fullwidth ? ' sp-settings-row--fullwidth' : '';
							?>">
								<div class="sp-settings-row__left">
									<label class="sp-settings-row__label"
										for="smartpay_settings[<?php echo esc_attr( $smartpay_field['id'] ); ?>]">
										<?php echo esc_html( $smartpay_field['name'] ); ?>
									</label>
									<?php if ( ! empty( $smartpay_field['desc'] ) && ! in_array( $smartpay_field['type'], [ 'text', 'textarea', 'select', 'select_currency', 'gateway_select', 'page_select', 'checkbox', 'switch', 'gateways' ], true ) ) : ?>
										<p class="sp-settings-row__desc"><?php echo wp_kses_post( $smartpay_field['desc'] ); ?></p>
									<?php endif; ?>
								</div>
								<div class="sp-settings-row__control">
									<?php $smartpay_setting_instance->$smartpay_callback( $smartpay_field ); ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>

					</div>
					<?php endforeach; ?>
				<?php endif; ?>

			</div><!-- .sp-settings-cards -->

			<div class="sp-settings-actions">
				<?php if ( 'debug_log' === $smartpay_active_tab ) : ?>
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

		<?php endif; ?>

	</div><!-- .sp-layout -->
</div>
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo ob_get_clean();
