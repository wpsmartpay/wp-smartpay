<?php
defined( 'ABSPATH' ) || exit;

$settings        = get_option( 'smartpay_settings', array() );
$profile_page_id = (int) ( $settings['user_profile_page'] ?? 0 );

$sp_current_view = function_exists( 'smartpay_dashboard_current_view' )
	? smartpay_dashboard_current_view()
	: 'overview';

$sp_current_url = home_url( add_query_arg( null, null ) );

/**
 * Built-in nav items (used when no WP menu is assigned to the
 * `smartpay_dashboard_sidebar` location). Each `view` item is a real link
 * that triggers a full page load (WooCommerce-style "each menu its own page").
 */
$sp_nav_items = array(
	array(
		'view'  => 'overview',
		'label' => __( 'Dashboard', 'smartpay' ),
		'url'   => smartpay_dashboard_view_url( 'overview' ),
		'icon'  => '<rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="3" y="12" width="7" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="12" y="3" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="12" y="10" width="5" height="7" rx="1" stroke="currentColor" stroke-width="1.5"/>',
	),
	array(
		'view'  => 'orders',
		'label' => __( 'Orders', 'smartpay' ),
		'url'   => smartpay_dashboard_view_url( 'orders' ),
		'icon'  => '<path d="M4 4H16C17.1 4 18 4.9 18 6V14C18 15.1 17.1 16 16 16H4C2.9 16 2 15.1 2 14V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.5"/><path d="M2 8H18" stroke="currentColor" stroke-width="1.5"/><path d="M6 12H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
	),
);

// Subscriptions are a Pro feature — only show the tab when its table exists.
if ( function_exists( 'smartpay_dashboard_subscriptions_enabled' ) && smartpay_dashboard_subscriptions_enabled() ) {
	$sp_nav_items[] = array(
		'view'  => 'subscriptions',
		'label' => __( 'Subscriptions', 'smartpay' ),
		'url'   => smartpay_dashboard_view_url( 'subscriptions' ),
		'icon'  => '<path d="M17 3H3C2.44772 3 2 3.44772 2 4V16C2 16.5523 2.44772 17 3 17H17C17.5523 17 18 16.5523 18 16V4C18 3.44772 17.5523 3 17 3Z" stroke="currentColor" stroke-width="1.5"/><path d="M14 3V1M6 3V1M2 7H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="10" cy="12" r="2" stroke="currentColor" stroke-width="1.5"/>',
	);
}

// Prefer a user-defined WP menu assigned to the dashboard sidebar location.
$sp_locations = get_nav_menu_locations();
$sp_menu_id   = (int) ( $sp_locations['smartpay_dashboard_sidebar'] ?? 0 );
$sp_menu      = $sp_menu_id ? wp_get_nav_menu_items( $sp_menu_id ) : false;

$sp_icon_kses = array(
	'rect'   => array(
		'x'            => array(),
		'y'            => array(),
		'width'        => array(),
		'height'       => array(),
		'rx'           => array(),
		'stroke'       => array(),
		'stroke-width' => array(),
	),
	'path'   => array(
		'd'               => array(),
		'stroke'          => array(),
		'stroke-width'    => array(),
		'stroke-linecap'  => array(),
		'stroke-linejoin' => array(),
	),
	'circle' => array(
		'cx'           => array(),
		'cy'           => array(),
		'r'            => array(),
		'stroke'       => array(),
		'stroke-width' => array(),
	),
);
?>

<aside class="dashboard-sidebar">
	<nav class="sidebar-nav">
		<div class="sidebar-block">
			<?php if ( ! empty( $sp_menu ) ) : ?>
				<?php foreach ( $sp_menu as $sp_item ) : ?>
					<?php $sp_is_active = untrailingslashit( $sp_item->url ) === untrailingslashit( $sp_current_url ); ?>
					<a href="<?php echo esc_url( $sp_item->url ); ?>" class="nav-item <?php echo $sp_is_active ? 'active' : ''; ?>">
						<span><?php echo esc_html( $sp_item->title ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<?php foreach ( $sp_nav_items as $sp_item ) : ?>
					<?php $sp_is_active = $sp_item['url'] && untrailingslashit( $sp_item['url'] ) === untrailingslashit( $sp_current_url ); ?>
					<a href="<?php echo esc_url( $sp_item['url'] ); ?>" class="nav-item <?php echo $sp_is_active ? 'active' : ''; ?>">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><?php echo wp_kses( $sp_item['icon'], $sp_icon_kses ); ?></svg>
						<span><?php echo esc_html( $sp_item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>

				<?php if ( $profile_page_id ) : ?>
					<?php $sp_profile_url = get_permalink( $profile_page_id ); ?>
					<a href="<?php echo esc_url( $sp_profile_url ); ?>" class="nav-item <?php echo ( $sp_profile_url && untrailingslashit( $sp_profile_url ) === untrailingslashit( $sp_current_url ) ) ? 'active' : ''; ?>">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
							<path d="M17 19V17C17 15.9391 16.5786 14.9217 15.8284 14.1716C15.0783 13.4214 14.0609 13 13 13H7C5.93913 13 4.92172 13.4214 3.17157 14.1716C2.42143 14.9217 2 15.9391 2 17V19" stroke="currentColor" stroke-width="1.5"/>
							<circle cx="10" cy="6" r="4" stroke="currentColor" stroke-width="1.5"/>
						</svg>
						<span><?php esc_html_e( 'Edit Profile', 'smartpay' ); ?></span>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="sidebar-block">
			<div class="nav-divider"></div>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="nav-item nav-logout">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M13 14L17 10M17 10L13 6M17 10H7M7 17H4C3.46957 17 2.96086 16.7893 2.58579 16.4142C2.21071 16.0391 2 15.5304 2 15V5C2 4.46957 2.21071 3.96086 2.58579 3.58579C2.96086 3.21071 3.46957 3 4 3H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span><?php esc_html_e( 'Logout', 'smartpay' ); ?></span>
			</a>
		</div>
	</nav>
</aside>
