<?php
/**
 * Donation form blocks.
 *
 * @package SmartPay
 */

namespace SmartPay\Modules\Campaign;

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Payment;

/**
 * Server rendering for the donation blocks (anonymous, comment, tribute,
 * step break) and the normalisation of what the donor entered into
 * the payment's `extra.donation` JSON.
 *
 * The blocks are dynamic (save() is empty) so the markup lives here, like the
 * Goal Progress block. Their inputs post as `smartpay_form[donation][…]`; on
 * payment they are moved out of `extra.form_data` into `extra.donation` so
 * donor fields never mix with custom form fields.
 *
 * Recurring gifts come from the Pricing block: a subscription option, or a
 * custom amount with a donor-chosen period (see NativeForm::guard_custom_billing).
 */
class DonationFields {

	/**
	 * Hook the blocks in.
	 */
	public function __construct() {
		$blocks = array(
			'donation-anonymous' => 'render_anonymous',
			'donation-comment'   => 'render_comment',
			'donation-tribute'   => 'render_tribute',
			'step-break'         => 'render_step_break',
		);

		foreach ( $blocks as $name => $method ) {
			add_filter( 'render_block_smartpay-form/' . $name, array( $this, $method ), 10, 2 );
		}

		add_filter( 'smartpay_prepare_payment_data', array( $this, 'store_donation_fields' ), 20, 2 );
		add_action( 'smartpay_after_payment_receipt_data', array( $this, 'receipt_rows' ) );
		add_action( 'smartpay_after_payment_receipt', array( $this, 'receipt_summary' ) );
	}

	/**
	 * Donation context for a payment, or null when it is not a gift.
	 *
	 * @param object $payment Payment model.
	 * @return array|null { donation, campaign (array|null), progress (array|null) }
	 */
	public static function payment_context( $payment ): ?array {
		$data    = is_array( $payment->data ?? null ) ? $payment->data : array();
		$form_id = (int) ( $data['form_id'] ?? 0 );

		if ( ! $form_id || ! smartpay_is_donation_form( $form_id ) ) {
			return null;
		}

		$campaign_id = smartpay_get_form_campaign_id( $form_id );
		$campaign    = $campaign_id ? smartpay_get_campaign( $campaign_id ) : null;

		return array(
			'donation' => smartpay_get_payment_donation( $payment->extra ?? array() ),
			'campaign' => $campaign,
			'progress' => $campaign ? smartpay_calculate_campaign_progress( $campaign['id'] ) : null,
		);
	}

	/**
	 * Thank-you page: campaign + frequency rows in the receipt table.
	 *
	 * @param object $payment Payment model.
	 */
	public function receipt_rows( $payment ): void {
		$ctx = self::payment_context( $payment );
		if ( ! $ctx ) {
			return;
		}

		$rows = array();
		if ( $ctx['campaign'] ) {
			$rows[ __( 'Campaign', 'smartpay' ) ] = $ctx['campaign']['title'];
		}
		$rows[ __( 'Frequency', 'smartpay' ) ] = 'monthly' === $ctx['donation']['frequency'] ? __( 'Monthly', 'smartpay' ) : __( 'One-time', 'smartpay' );

		foreach ( $rows as $label => $value ) {
			printf(
				'<tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:10px 0;color:#6b7280;width:40%%;">%1$s</td><td style="padding:10px 0;font-weight:500;">%2$s</td></tr>',
				esc_html( $label ),
				esc_html( $value )
			);
		}
	}

	/**
	 * Thank-you page: how the gift moved the campaign, how the donor appears,
	 * and a link to their giving history.
	 *
	 * @param object $payment Payment model.
	 */
	public function receipt_summary( $payment ): void {
		$ctx = self::payment_context( $payment );
		if ( ! $ctx ) {
			return;
		}

		$giving_url = function_exists( 'smartpay_dashboard_view_url' ) ? smartpay_dashboard_view_url( 'giving' ) : '';
		$customer   = $payment->customer ?? null;
		$extra      = $customer ? json_decode( (string) $customer->extra, true ) : array();
		$anonymous  = $ctx['donation']['anonymous'] || ! empty( $extra['donor']['hide_name'] );

		/**
		 * Whether this campaign shows a public donor wall (Pro).
		 *
		 * @param bool       $public   Default false: donor lists are private.
		 * @param array|null $campaign Normalised campaign.
		 */
		$wall_public = $ctx['campaign'] && apply_filters( 'smartpay_campaign_wall_is_public', false, $ctx['campaign'] );

		/**
		 * Whether this campaign's donor wall prints donor names (Pro).
		 *
		 * @param bool       $shows    Default true.
		 * @param array|null $campaign Normalised campaign.
		 */
		$anonymous = $anonymous || ! apply_filters( 'smartpay_campaign_wall_shows_names', true, $ctx['campaign'] );
		?>
		<div class="smartpay-receipt-donation" style="margin-top:24px;padding:16px 20px;border:1px solid #e5e7eb;border-radius:8px;">
			<?php if ( $ctx['campaign'] ) : ?>
				<p style="margin:0 0 10px;font-weight:700;"><?php esc_html_e( 'Where your gift sits', 'smartpay' ); ?></p>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- View escapes its own output.
				echo smartpay_view(
					'campaign.progress',
					array(
						'campaign' => $ctx['campaign'],
						'progress' => $ctx['progress'],
					)
				);
				wp_enqueue_style( 'smartpay-campaign' );
				?>
				<?php if ( $wall_public ) : ?>
					<p style="margin:12px 0 0;font-size:14px;">
						<?php if ( $anonymous ) : ?>
							<?php esc_html_e( 'Your name won’t appear on the donor wall.', 'smartpay' ); ?>
						<?php else : ?>
							<?php
							printf(
								/* translators: %s: donor's full name */
								esc_html__( 'You’ll appear on the donor wall as %s.', 'smartpay' ),
								'<strong>' . esc_html( smartpay_donor_public_name( (string) ( $customer->first_name ?? '' ), (string) ( $customer->last_name ?? '' ) ) ) . '</strong>'
							);
							?>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( $giving_url && is_user_logged_in() ) : ?>
				<p style="margin:12px 0 0;"><a href="<?php echo esc_url( $giving_url ); ?>"><?php esc_html_e( 'View my giving history', 'smartpay' ); ?></a></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Block attribute with a default.
	 *
	 * @param array  $block   Parsed block.
	 * @param string $key     Attribute.
	 * @param string $fallback Default.
	 * @return string
	 */
	private function attr( array $block, string $key, string $fallback ): string {
		$value = $block['attrs'][ $key ] ?? '';
		return '' !== trim( (string) $value ) ? (string) $value : $fallback;
	}

	/**
	 * Give anonymously.
	 *
	 * @param string $content Rendered content (empty).
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public function render_anonymous( $content, $block ): string {
		ob_start();
		?>
		<div class="smartpay-donation-field smartpay-donation-anonymous">
			<label>
				<input type="checkbox" name="smartpay_form[donation][anonymous]" value="1" />
				<span><?php echo esc_html( $this->attr( $block, 'label', __( 'Give anonymously — your name won’t appear publicly', 'smartpay' ) ) ); ?></span>
			</label>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Public message to leave with the gift.
	 *
	 * @param string $content Rendered content (empty).
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public function render_comment( $content, $block ): string {
		$id = wp_unique_id( 'smartpay-donation-comment-' );

		ob_start();
		?>
		<div class="smartpay-donation-field smartpay-donation-comment">
			<label class="smartpay-donation-field__label" for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $this->attr( $block, 'label', __( 'Leave a message (optional)', 'smartpay' ) ) ); ?>
			</label>
			<textarea id="<?php echo esc_attr( $id ); ?>" name="smartpay_form[donation][comment]" rows="3" maxlength="1000"
				placeholder="<?php echo esc_attr( $this->attr( $block, 'placeholder', '' ) ); ?>"></textarea>
			<small class="smartpay-donation-field__help">
				<?php echo esc_html( $this->attr( $block, 'help', __( 'Shown on the campaign’s donor wall if the organiser made it public.', 'smartpay' ) ) ); ?>
			</small>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Dedicate the gift (in honor / in memory of someone).
	 *
	 * @param string $content Rendered content (empty).
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public function render_tribute( $content, $block ): string {
		$id = wp_unique_id( 'smartpay-donation-tribute-' );

		ob_start();
		?>
		<details class="smartpay-donation-field smartpay-donation-tribute">
			<summary><?php echo esc_html( $this->attr( $block, 'label', __( 'Dedicate this gift (in honor / in memory)', 'smartpay' ) ) ); ?></summary>
			<div class="smartpay-donation-tribute__body">
				<label><input type="radio" name="smartpay_form[donation][tribute][type]" value="honor" checked /> <?php esc_html_e( 'In honor of', 'smartpay' ); ?></label>
				<label><input type="radio" name="smartpay_form[donation][tribute][type]" value="memory" /> <?php esc_html_e( 'In memory of', 'smartpay' ); ?></label>
				<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Name', 'smartpay' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="smartpay_form[donation][tribute][name]" maxlength="200"
					placeholder="<?php esc_attr_e( 'Name', 'smartpay' ); ?>" />
			</div>
		</details>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Step break: the fields before it are one step. The front-end script turns
	 * the form into steps with Continue / Back; without JS the form stays whole.
	 *
	 * @param string $content Rendered content (empty).
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public function render_step_break( $content, $block ): string {
		return sprintf(
			'<div class="smartpay-step-break" data-next-label="%1$s" data-step-title="%2$s" aria-hidden="true"></div>',
			esc_attr( $this->attr( $block, 'nextLabel', __( 'Continue', 'smartpay' ) ) ),
			esc_attr( $this->attr( $block, 'title', '' ) )
		);
	}

	/**
	 * Move the donor's entries into `extra.donation` (sanitised) on donation forms.
	 *
	 * Frequency is taken from the billing type that was actually charged, so
	 * the record never claims "monthly" for a one-time payment. The Charity
	 * template's older "Make my donation anonymous" checkbox still counts.
	 *
	 * @param array $data Prepared payment data.
	 * @param array $raw  Raw posted data.
	 * @return array
	 */
	public function store_donation_fields( array $data, array $raw ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Filter signature.
		if ( 'form_payment' !== ( $data['payment_type'] ?? '' ) ) {
			return $data;
		}

		$form_id = (int) ( $data['payment_data']['form_id'] ?? 0 );
		$form    = (array) ( $data['extra']['form_data'] ?? array() );
		$posted  = is_array( $form['donation'] ?? null ) ? $form['donation'] : array();
		unset( $form['donation'] );
		$data['extra']['form_data'] = $form;

		if ( ! $form_id || ! smartpay_is_donation_form( $form_id ) ) {
			return $data;
		}

		// No subscription engine without Pro: a posted subscription is charged once.
		if ( ! smartpay_is_pro_active() && Payment::BILLING_TYPE_SUBSCRIPTION === ( $data['payment_data']['billing_type'] ?? '' ) ) {
			$data['payment_data']['billing_type'] = Payment::BILLING_TYPE_ONE_TIME;
			unset( $data['billing_type'], $data['billing_period'], $data['payment_data']['billing_period'] );
		}

		$legacy_options = (array) ( $form['donation_options'] ?? array() );
		$tribute        = is_array( $posted['tribute'] ?? null ) ? $posted['tribute'] : array();
		$tribute_name   = sanitize_text_field( wp_unslash( (string) ( $tribute['name'] ?? '' ) ) );

		$data['extra']['donation'] = array(
			'anonymous' => ! empty( $posted['anonymous'] ) || in_array( 'make-my-donation-anonymous', $legacy_options, true ),
			'comment'   => mb_substr( sanitize_textarea_field( wp_unslash( (string) ( $posted['comment'] ?? '' ) ) ), 0, 1000 ),
			'frequency' => Payment::BILLING_TYPE_SUBSCRIPTION === ( $data['payment_data']['billing_type'] ?? '' ) ? 'monthly' : 'one_time',
			'tribute'   => '' !== $tribute_name
				? array(
					'type' => 'memory' === ( $tribute['type'] ?? '' ) ? 'memory' : 'honor',
					'name' => mb_substr( $tribute_name, 0, 200 ),
				)
				: array(),
			'hidden'    => false,
		);

		return $data;
	}
}
