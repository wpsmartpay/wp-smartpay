<?php
/**
 * Public campaign page — /campaign/{slug}.
 *
 * Shows the cover, goal progress, story and the campaign's forms ("Ways to
 * give"). The chosen form (default, or ?give={form id}) is embedded below.
 * Donor names never appear here in Free; add-ons can hook
 * `smartpay_campaign_page_after_forms` to add a public donor wall.
 *
 * @package SmartPay
 */

defined( 'ABSPATH' ) || exit;

$sp_term     = get_queried_object();
$sp_campaign = $sp_term instanceof WP_Term ? smartpay_get_campaign( $sp_term ) : null;

if ( ! $sp_campaign ) {
	return;
}

$sp_progress = smartpay_calculate_campaign_progress( $sp_campaign['id'] );
$sp_forms    = array_values(
	array_filter(
		array_map( 'get_post', $sp_campaign['form_ids'] ),
		static function ( $post ) {
			return $post instanceof WP_Post && 'publish' === $post->post_status;
		}
	)
);
$sp_form_ids = wp_list_pluck( $sp_forms, 'ID' );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only form switch on a public page.
$sp_give     = isset( $_GET['give'] ) ? absint( $_GET['give'] ) : 0;
$sp_selected = in_array( $sp_give, $sp_form_ids, true ) ? $sp_give : $sp_campaign['default_form_id'];
$sp_selected = in_array( $sp_selected, $sp_form_ids, true ) ? $sp_selected : ( $sp_form_ids[0] ?? 0 );

wp_enqueue_style( 'smartpay-campaign' );

$sp_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

if ( $sp_block_theme ) : ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'smartpay-campaign-template' ); ?>>
	<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php block_template_part( 'header' ); ?>
	<?php
else :
	get_header();
endif;
?>

<main id="smartpay-campaign-<?php echo esc_attr( (string) $sp_campaign['id'] ); ?>" class="smartpay-campaign">
	<?php if ( $sp_campaign['cover_url'] ) : ?>
		<div class="smartpay-campaign__cover">
			<img src="<?php echo esc_url( $sp_campaign['cover_url'] ); ?>" alt="" />
		</div>
	<?php endif; ?>

	<header class="smartpay-campaign__header">
		<h1 class="smartpay-campaign__title"><?php echo esc_html( $sp_campaign['title'] ); ?></h1>
		<?php if ( $sp_campaign['description'] ) : ?>
			<p class="smartpay-campaign__summary"><?php echo esc_html( $sp_campaign['description'] ); ?></p>
		<?php endif; ?>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- View output is escaped inside the template.
		echo smartpay_view(
			'campaign.progress',
			array(
				'campaign' => $sp_campaign,
				'progress' => $sp_progress,
			)
		);
		?>

		<?php if ( $sp_selected && 'active' === $sp_campaign['status'] ) : ?>
			<a class="smartpay-campaign__donate" href="#smartpay-campaign-donate"><?php esc_html_e( 'Donate now', 'smartpay' ); ?></a>
		<?php elseif ( 'ended' === $sp_campaign['status'] ) : ?>
			<p class="smartpay-campaign__ended"><?php esc_html_e( 'This campaign has ended. Thank you to everyone who gave.', 'smartpay' ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $sp_campaign['story'] ) : ?>
		<section class="smartpay-campaign__story">
			<?php echo wp_kses_post( wpautop( $sp_campaign['story'] ) ); ?>
		</section>
	<?php endif; ?>

	<?php if ( count( $sp_forms ) > 1 ) : ?>
		<section class="smartpay-campaign__ways">
			<h2><?php esc_html_e( 'Ways to give', 'smartpay' ); ?></h2>
			<ul class="smartpay-campaign__forms">
				<?php foreach ( $sp_forms as $sp_form ) : ?>
					<li class="<?php echo $sp_form->ID === $sp_selected ? 'is-selected' : ''; ?>">
						<span class="smartpay-campaign__form-title">
							<?php echo esc_html( $sp_form->post_title ); ?>
							<?php if ( $sp_form->ID === $sp_campaign['default_form_id'] ) : ?>
								<span aria-hidden="true">★</span>
							<?php endif; ?>
						</span>
						<a href="<?php echo esc_url( add_query_arg( 'give', $sp_form->ID, $sp_campaign['url'] ) . '#smartpay-campaign-donate' ); ?>">
							<?php esc_html_e( 'Give', 'smartpay' ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php
	/**
	 * Fires after the campaign's "Ways to give" section.
	 *
	 * @param array $sp_campaign Normalised campaign.
	 * @param array $sp_progress Campaign totals.
	 */
	do_action( 'smartpay_campaign_page_after_forms', $sp_campaign, $sp_progress );
	?>

	<?php if ( $sp_selected && 'active' === $sp_campaign['status'] ) : ?>
		<section id="smartpay-campaign-donate" class="smartpay-campaign__donate-form">
			<?php echo do_shortcode( sprintf( '[sp_form id="%d"]', $sp_selected ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form markup is escaped by its own template. ?>
		</section>
	<?php endif; ?>
</main>

<?php if ( $sp_block_theme ) : ?>
	<?php block_template_part( 'footer' ); ?>
</div>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
else :
	get_footer();
endif;
