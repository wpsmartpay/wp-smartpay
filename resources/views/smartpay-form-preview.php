<?php
defined( 'ABSPATH' ) || exit;
/**
 * Frontend preview template for smartpay_form CPT posts.
 *
 * Standalone full-page template — no theme wrapper. Reads body and meta
 * directly from the post (or autosave revision) so unsaved Gutenberg changes
 * are immediately visible without a full save.
 *
 * @package SmartPay
 */

$post    = get_queried_object();
$post_id = $post instanceof WP_Post ? $post->ID : 0;

$body          = $post instanceof WP_Post ? $post->post_content : '';
$form_title    = $post instanceof WP_Post ? $post->post_title : '';
$amounts_json  = $post_id ? get_post_meta( $post_id, '_smartpay_amounts', true ) : '';
$settings_json = $post_id ? get_post_meta( $post_id, '_smartpay_settings', true ) : '';

if ( $post_id && is_preview() ) {
	$autosave = wp_get_post_autosave( $post_id, get_current_user_id() );
	if ( $autosave instanceof WP_Post ) {
		$body = $autosave->post_content;

		// Title lives on the autosave too — without this, a just-typed (not yet
		// saved) form title is missing from the preview even with "Show Form Title".
		if ( '' !== $autosave->post_title ) {
			$form_title = $autosave->post_title;
		}

		$as_amounts = get_post_meta( $autosave->ID, '_smartpay_amounts', true );
		if ( $as_amounts ) {
			$amounts_json = $as_amounts;
		}

		$as_settings = get_post_meta( $autosave->ID, '_smartpay_settings', true );
		if ( $as_settings ) {
			// Shallow-merge instead of replacing outright: an autosave request that
			// was already in flight when the editor's "Update" button was clicked
			// can land AFTER the update completes, persisting an autosave revision
			// that is newer by timestamp but reflects an older in-memory settings
			// snapshot (missing keys added moments earlier). Autosave values still
			// win per-key — this only stops a stale/incomplete autosave from
			// silently dropping settings that are already live and published.
			$live_settings     = json_decode( $settings_json, true );
			$autosave_settings = json_decode( $as_settings, true );
			if ( is_array( $live_settings ) && is_array( $autosave_settings ) ) {
				$settings_json = wp_json_encode( array_merge( $live_settings, $autosave_settings ) );
			} else {
				$settings_json = $as_settings;
			}
		}
	}
}

$amounts = array();
if ( $amounts_json ) {
	$decoded = json_decode( $amounts_json, true );
	if ( is_array( $decoded ) ) {
		$amounts = $decoded;
	}
}
if ( empty( $amounts ) ) {
	$amounts = array(
		array(
			'key'          => 'default',
			'label'        => '',
			'amount'       => '0.00',
			'billing_type' => 'One Time',
		),
	);
}

$settings = array();
if ( $settings_json ) {
	$decoded = json_decode( $settings_json, true );
	if ( is_array( $decoded ) ) {
		$settings = $decoded;
	}
}

// Default-on: the toggle only hides the title when explicitly set to false,
// matching the editor sidebar ( settings.show_title !== false ).
$show_title = ! isset( $settings['show_title'] ) || false !== $settings['show_title'];
$is_preview = is_preview();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'Form Preview', 'smartpay' ); ?></title>
<?php wp_head(); ?>
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body {
	margin: 0;
	padding: 0;
	background: #f5f5f5;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	min-height: 100vh;
}
.sp-preview-wrap {
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
}
.sp-preview-inner {
	width: 100%;
	max-width: 980px;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,.10), 0 4px 16px rgba(0,0,0,.06);
	padding: 32px;
}
.sp-form-title {
	margin: 0 0 20px;
	font-size: 22px;
	font-weight: 600;
	color: #111827;
	line-height: 1.3;
}
.sp-preview-badge {
	display: inline-block;
	font-size: 11px;
	font-weight: 600;
	letter-spacing: .05em;
	text-transform: uppercase;
	color: #6b7280;
	background: #f3f4f6;
	border-radius: 4px;
	padding: 3px 8px;
	margin-bottom: 20px;
}
</style>
</head>
<body>
<div class="sp-preview-wrap">
	<div class="sp-preview-inner">
		<?php if ( $is_preview ) : ?>
			<span class="sp-preview-badge"><?php esc_html_e( 'Preview', 'smartpay' ); ?></span>
		<?php endif; ?>
		<?php if ( $show_title && $form_title ) : ?>
			<h2 class="sp-form-title"><?php echo esc_html( $form_title ); ?></h2>
		<?php endif; ?>
		<?php
		if ( $post_id ) {
			$template = plugin_dir_path( SMARTPAY_PLUGIN_FILE ) . 'resources/views/native-form-embed.php';
			if ( file_exists( $template ) ) {
				// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
				include $template;
			}
		} else {
			echo '<p>' . esc_html__( 'Form not found.', 'smartpay' ) . '</p>';
		}
		?>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
