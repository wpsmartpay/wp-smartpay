<?php

namespace SmartPay\Modules\NativeForm;

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Form as LegacyForm;

/**
 * One-way, idempotent migrator: legacy smartpay_forms row → smartpay_form CPT post.
 *
 * Real legacy bodies contain stale field-input blocks (self-closing, no HTML) that the
 * current block save() no longer matches, plus stray theme patterns. The migrator parses
 * block-comment attributes to rebuild each recognised field block in the current format,
 * drops junk, and synthesises a pricing block from the stored amounts JSON if no pricing
 * block exists in the body.
 *
 * Safe to re-run: uses SOURCE_META to detect and update an already-migrated post.
 */
final class LegacyFormMigrator {

	/** Post meta key that links a CPT post back to its legacy row. */
	const SOURCE_META = '_smartpay_migrated_from_legacy_id';

	/**
	 * Migrate one legacy form. Idempotent — re-running updates the existing CPT post.
	 *
	 * @param LegacyForm $form    Legacy form model.
	 * @param bool       $dry_run Report-only; no DB writes.
	 * @return int|\WP_Error      Migrated/updated CPT post ID, or WP_Error.
	 */
	public function migrate( LegacyForm $form, bool $dry_run = false ) {
		$existing = get_posts(
			array(
				'post_type'   => 'smartpay_form',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => self::SOURCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- migration-link lookup, runs only during migration.
				'meta_value'  => (string) $form->id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- migration-link lookup, runs only during migration.
			)
		);
		$post_id = $existing[0] ?? 0;

		$amounts = $this->translate_amounts( (array) $form->amounts );

		if ( $dry_run ) {
			return $post_id ?: 0;
		}

		$post_status    = ( 'publish' === $form->status ) ? 'publish' : 'draft';
		$normalized_body = $this->normalize_body( (string) $form->body, $amounts );

		$postarr = array(
			'ID'           => $post_id,
			'post_type'    => 'smartpay_form',
			'post_title'   => sanitize_text_field( (string) $form->title ),
			'post_content' => $normalized_body,
			'post_status'  => $post_status,
			'post_author'  => (int) ( isset( $form->created_by ) ? $form->created_by : get_current_user_id() ),
		);

		$post_id = $post_id ? wp_update_post( $postarr, true ) : wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_smartpay_amounts', wp_json_encode( $amounts ) );
		update_post_meta( $post_id, '_smartpay_settings', wp_json_encode( (array) ( $form->settings ?? array() ) ) );
		update_post_meta( $post_id, self::SOURCE_META, (string) $form->id );

		// Also patch the source legacy row body with the normalized markup so the
		// legacy form builder (#/N/edit) shows valid blocks instead of "invalid content".
		global $wpdb;
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prefix . 'smartpay_forms',
			array( 'body' => $normalized_body ),
			array( 'id'   => (int) $form->id ),
			array( '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after a legacy form is successfully migrated to a CPT post.
		 *
		 * @param int        $post_id Migrated CPT post ID.
		 * @param LegacyForm $form    Source legacy form model.
		 */
		do_action( 'smartpay_legacy_form_migrated', $post_id, $form );
		return $post_id;
	}

	// -------------------------------------------------------------------------
	// Body normalisation
	// -------------------------------------------------------------------------

	/**
	 * Parse a legacy body, rebuild recognised smartpay-form/* blocks in the
	 * current format, drop junk, and add missing pricing/submit blocks.
	 *
	 * @param string $body    Raw legacy block markup.
	 * @param array  $amounts Already-translated amounts (for synthesising pricing).
	 * @return string         Block markup compatible with the current block bundle.
	 */
	private function normalize_body( string $body, array $amounts = array() ): string {
		$blocks      = parse_blocks( $body );
		$clean       = array();
		$has_pricing = false;
		$has_submit  = false;

		foreach ( $blocks as $b ) {
			$name = $b['blockName'] ?? '';
			// Keep only smartpay-form/* blocks; drop core/group theme patterns etc.
			if ( 0 !== strpos( (string) $name, 'smartpay-form/' ) ) {
				continue;
			}
			if ( 'smartpay-form/pricing' === $name ) {
				$has_pricing = true;
			}
			if ( 'smartpay-form/submit-button' === $name ) {
				$has_submit = true;
			}

			$rebuilt = $this->rebuild_block( $b, $amounts );
			if ( '' !== $rebuilt ) {
				$clean[] = $rebuilt;
			}
		}

		// Synthesise a pricing block from the amounts column if body had none.
		if ( ! $has_pricing && ! empty( $amounts ) ) {
			$clean[] = $this->build_pricing_block( $amounts );
		}

		// Always need a submit button.
		if ( ! $has_submit ) {
			$clean[] = $this->build_submit_button_block();
		}

		return $clean ? implode( "\n", $clean ) : $this->default_template_markup( $amounts );
	}

	/**
	 * Route a parsed block to the correct rebuilder.
	 *
	 * @param array $b       Parsed block (from parse_blocks()).
	 * @param array $amounts Translated amounts (for re-synthesising pricing children).
	 * @return string        Rebuilt block markup.
	 */
	private function rebuild_block( array $b, array $amounts = array() ): string {
		$name  = $b['blockName'] ?? '';
		$attrs = $b['attrs']      ?? array();
		$inner = $b['innerBlocks'] ?? array();

		switch ( $name ) {
			case 'smartpay-form/name':
				return $this->build_name_block( $inner );

			case 'smartpay-form/email':
				return $this->build_email_block( $inner );

			case 'smartpay-form/pricing':
				// Rebuild from inner pricing-option blocks; fall back to amounts.
				$option_blocks = array_filter(
					$inner,
					fn( $ib ) => ( $ib['blockName'] ?? '' ) === 'smartpay-form/pricing-option'
				);
				$src = ! empty( $option_blocks )
					? array_map( fn( $ib ) => $ib['attrs'] ?? array(), array_values( $option_blocks ) )
					: $amounts;
				return $this->build_pricing_block( $src );

			case 'smartpay-form/submit-button':
				return $this->build_submit_button_block();

			default:
				// Other smartpay-form/* blocks (text-input, checkbox, etc.) —
				// re-serialize as-is; they may already be in the current format.
				return $this->serialize_raw_block( $b );
		}
	}

	// -------------------------------------------------------------------------
	// Block HTML builders — output matches the current block save() format
	// -------------------------------------------------------------------------

	/** Name block (first/middle/last) rebuilt from legacy innerBlocks. */
	private function build_name_block( array $name_field_blocks ): string {
		if ( empty( $name_field_blocks ) ) {
			// No inner blocks: seed the standard three-field template.
			return $this->default_name_block();
		}

		$fields_html = '';
		foreach ( $name_field_blocks as $nf ) {
			if ( ( $nf['blockName'] ?? '' ) !== 'smartpay-form/name-field' ) {
				continue;
			}
			$fields_html .= $this->build_name_field( $nf['innerBlocks'] ?? array(), $nf['attrs'] ?? array() );
		}

		if ( '' === $fields_html ) {
			return $this->default_name_block();
		}

		return sprintf(
			"<!-- wp:smartpay-form/name -->\n<div class=\"wp-block-smartpay-form-name form-element row\">%s</div>\n<!-- /wp:smartpay-form/name -->",
			$fields_html
		);
	}

	/** One name-field (label + input pair). */
	private function build_name_field( array $inner, array $field_attrs ): string {
		$label_text = '';
		$field_name = $field_attrs['fieldType'] ?? '';
		$placeholder = '';
		$is_required = false;

		foreach ( $inner as $ib ) {
			$bn = $ib['blockName'] ?? '';
			if ( 'smartpay-form/name-label' === $bn ) {
				$label_text = $ib['attrs']['text'] ?? '';
			}
			if ( 'smartpay-form/name-input' === $bn ) {
				$placeholder = $ib['attrs']['placeholder'] ?? '';
				$is_required = ! empty( $ib['attrs']['isRequired'] );
				if ( ! empty( $ib['attrs']['fieldName'] ) ) {
					$field_name = $ib['attrs']['fieldName'];
				}
			}
		}

		// Infer fieldName from label text when missing.
		if ( '' === $field_name ) {
			$field_name = $this->infer_name_field_key( $label_text );
		}
		if ( '' === $label_text ) {
			$label_text = ucwords( str_replace( '_', ' ', $field_name ) );
		}
		if ( '' === $placeholder ) {
			$placeholder = $label_text;
		}

		$field_name  = sanitize_key( $field_name );
		$label_text  = esc_html( $label_text );
		$placeholder = esc_attr( $placeholder );
		$req_attr    = $is_required ? ' required/' : '/';
		$field_attr  = 'first_name' === $field_name
			? ''  // first_name is the block default; omit from attrs comment
			: sprintf( '"fieldName":"%s",', esc_js( $field_name ) );
		$req_json    = $is_required ? ',"isRequired":true' : '';
		$label_block = sprintf(
			'<!-- wp:smartpay-form/name-label {"text":"%s"} -->%s<label class="wp-block-smartpay-form-name-label">%s</label>%s<!-- /wp:smartpay-form/name-label -->',
			esc_js( $label_text ),
			"\n",
			$label_text,
			"\n"
		);
		$input_block = sprintf(
			'<!-- wp:smartpay-form/name-input {%s"placeholder":"%s"%s} -->%s<input class="wp-block-smartpay-form-name-input form-control" type="text" id="%s" name="smartpay_form[name][%s]" placeholder="%s"%s>%s<!-- /wp:smartpay-form/name-input -->',
			$field_attr,
			$placeholder,
			$req_json,
			"\n",
			esc_attr( $field_name ),
			esc_attr( $field_name ),
			$placeholder,
			$req_attr,
			"\n"
		);

		$field_type_attr = sprintf( '"label":"%s","fieldType":"%s"', esc_js( $label_text ), esc_js( $field_name ) );

		return sprintf(
			'<!-- wp:smartpay-form/name-field {%s} -->%s<div class="wp-block-smartpay-form-name-field col">%s%s</div>%s<!-- /wp:smartpay-form/name-field -->%s',
			$field_type_attr,
			"\n",
			$label_block,
			"\n" . $input_block,
			"\n",
			"\n"
		);
	}

	/** Standard three-column name block when no inner block data survives. */
	private function default_name_block(): string {
		$fields = array(
			array( 'label' => 'First Name', 'key' => 'first_name', 'required' => true ),
			array( 'label' => 'Middle Name', 'key' => 'middle_name', 'required' => false ),
			array( 'label' => 'Last Name', 'key' => 'last_name', 'required' => false ),
		);
		$inner = '';
		foreach ( $fields as $f ) {
			$inner .= $this->build_name_field(
				array(
					array( 'blockName' => 'smartpay-form/name-label', 'attrs' => array( 'text' => $f['label'] ), 'innerBlocks' => array() ),
					array( 'blockName' => 'smartpay-form/name-input', 'attrs' => array( 'fieldName' => $f['key'], 'placeholder' => $f['label'], 'isRequired' => $f['required'] ), 'innerBlocks' => array() ),
				),
				array( 'fieldType' => $f['key'] )
			);
		}
		return sprintf(
			"<!-- wp:smartpay-form/name -->\n<div class=\"wp-block-smartpay-form-name form-element row\">%s</div>\n<!-- /wp:smartpay-form/name -->",
			$inner
		);
	}

	/** Infer the fieldName key from a label string. */
	private function infer_name_field_key( string $label ): string {
		$map = array(
			'first'  => 'first_name',
			'middle' => 'middle_name',
			'last'   => 'last_name',
		);
		$lower = strtolower( $label );
		foreach ( $map as $needle => $key ) {
			if ( false !== strpos( $lower, $needle ) ) {
				return $key;
			}
		}
		return sanitize_key( $label ) ?: 'first_name';
	}

	/** Email block rebuilt from legacy innerBlocks. */
	private function build_email_block( array $inner ): string {
		$label_text  = 'Email';
		$field_name  = 'email';
		$placeholder = 'Email';
		$is_required = true;

		foreach ( $inner as $ib ) {
			$bn = $ib['blockName'] ?? '';
			if ( 'smartpay-form/email-label' === $bn ) {
				$label_text = $ib['attrs']['text'] ?? 'Email';
			}
			if ( 'smartpay-form/email-input' === $bn ) {
				$field_name  = $ib['attrs']['fieldName']  ?? 'email';
				$placeholder = $ib['attrs']['placeholder'] ?? 'Email';
				$is_required = isset( $ib['attrs']['isRequired'] ) ? (bool) $ib['attrs']['isRequired'] : true;
			}
		}

		$label_text  = esc_html( $label_text );
		$placeholder = esc_attr( $placeholder );
		$req_attr    = $is_required ? ' required/' : '/';

		// email-label and email-input default attrs omit text/fieldName when default.
		$label_attrs = ( 'Email' === $label_text ) ? '' : sprintf( ' {"text":"%s"}', esc_js( $label_text ) );
		$label_block = sprintf(
			'<!-- wp:smartpay-form/email-label%s -->%s<label class="wp-block-smartpay-form-email-label">%s</label>%s<!-- /wp:smartpay-form/email-label -->',
			$label_attrs,
			"\n",
			$label_text,
			"\n"
		);
		$input_block = sprintf(
			'<!-- wp:smartpay-form/email-input -->%s<input class="wp-block-smartpay-form-email-input form-control" type="email" id="%s" name="smartpay_form[%s]" placeholder="%s"%s>%s<!-- /wp:smartpay-form/email-input -->',
			"\n",
			esc_attr( $field_name ),
			esc_attr( $field_name ),
			$placeholder,
			$req_attr,
			"\n"
		);

		return sprintf(
			"<!-- wp:smartpay-form/email -->\n<div class=\"wp-block-smartpay-form-email form-element\">%s%s</div>\n<!-- /wp:smartpay-form/email -->",
			$label_block . "\n",
			$input_block
		);
	}

	/**
	 * Pricing block from an array of amount entries (each having key/label/amount/
	 * billing_type/etc.).
	 *
	 * @param array $amounts Either translated legacy amounts or pricing-option attrs.
	 * @return string Block markup.
	 */
	private function build_pricing_block( array $amounts ): string {
		$symbol    = html_entity_decode( (string) smartpay_get_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$css_sym   = esc_attr( $symbol );
		$opts_html = '';
		$opts_blocks = '';

		foreach ( $amounts as $a ) {
			$key          = sanitize_key( $a['key'] ?? 'opt-' . wp_generate_password( 9, false, false ) );
			$label        = esc_html( $a['label'] ?? 'Plan' );
			$description  = esc_html( $a['description'] ?? '' );
			$amount       = (float) ( $a['amount'] ?? 0 );
			$billing_type = sanitize_text_field( $a['billing_type'] ?? 'One Time' );
			$is_sub       = 'Subscription' === $billing_type;
			$period       = $is_sub ? sanitize_text_field( $a['billing_period'] ?? 'month' ) : '';

			// Build pricing-option block content (matches current save() output).
			$plan_desc   = ( '' !== $description )
				? sprintf( '<span class="plan-desc">%s</span>', $description )
				: '';
			$plan_cycle  = $is_sub && $period
				? sprintf( '<span class="slash">/</span><span class="plan-cycle">%s</span>', esc_html( $period ) )
				: '';
			$hidden_period = $is_sub && $period
				? sprintf( '<input type="hidden" name="_form_billing_period" value="%s"/>', esc_attr( $period ) )
				: '';

			$option_inner = sprintf(
				'<span class="plan-name" aria-hidden="true"><input type="radio" name="_form_amount" id="_form_amount_%s" class="radio" value="%s"/><span class="plan-info"><span class="plan-type">%s</span>%s</span></span><span class="plan-details" aria-hidden="true"><span class="plan-cost"><span class="plan-symbol"></span>%s%s</span></span><input type="hidden" name="_form_billing_type" value="%s"/><input type="hidden" name="_form_amount_key" value="%s"/>%s',
				esc_attr( $key ),
				esc_attr( (string) $amount ),
				$label,
				$plan_desc,
				esc_html( (string) $amount ),
				$plan_cycle,
				esc_attr( $billing_type ),
				esc_attr( $key ),
				$hidden_period
			);

			$option_html = sprintf(
				'<label class="wp-block-smartpay-form-pricing-option form-plan-card plan-amount">%s</label>',
				$option_inner
			);

			$option_attrs = wp_json_encode(
				array_filter(
					array(
						'key'            => $key,
						'label'          => $a['label'] ?? 'Plan',
						'description'    => $a['description'] ?? '',
						'amount'         => (string) $amount,
						'billing_type'   => $billing_type,
						'billing_period' => $period ?: null,
					),
					fn( $v ) => null !== $v && '' !== $v
				),
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);

			$opts_blocks .= sprintf(
				'<!-- wp:smartpay-form/pricing-option %s -->%s%s%s<!-- /wp:smartpay-form/pricing-option -->%s',
				$option_attrs,
				"\n",
				$option_html,
				"\n",
				"\n"
			);
			$opts_html   .= $option_html;
		}

		$pricing_inner = sprintf(
			'<div class="form-amounts"><div class="form-plan-grid">%s</div><input type="hidden" name="smartpay_form_billing_type" value="One Time"/><input type="hidden" name="smartpay_form_billing_period" value="month"/><input type="hidden" class="form-control form--custom-amount amount" name="smartpay_form_amount" value="0.00"/></div>',
			$opts_blocks
		);

		return sprintf(
			"<!-- wp:smartpay-form/pricing -->\n<div class=\"wp-block-smartpay-form-pricing form--amount-section smartpay-pricing is-style-grid\" style=\"--sp-currency:'%s'\">%s</div>\n<!-- /wp:smartpay-form/pricing -->",
			$css_sym,
			$pricing_inner
		);
	}

	/** Submit button block — coupon + pay-button children matching current save() format. */
	private function build_submit_button_block(): string {
		return "<!-- wp:smartpay-form/submit-button -->\n<!-- wp:smartpay-form/submit-coupon /-->\n<!-- wp:smartpay-form/submit-pay {\"label\":\"Pay Now\"} /-->\n<!-- /wp:smartpay-form/submit-button -->";
	}

	/**
	 * Fallback markup when nothing from the legacy body survives: seed the same
	 * default template the editor inserts for new forms (name + email + pricing +
	 * submit).
	 */
	private function default_template_markup( array $amounts = array() ): string {
		$parts = array(
			$this->default_name_block(),
			$this->build_email_block( array() ),
		);
		if ( ! empty( $amounts ) ) {
			$parts[] = $this->build_pricing_block( $amounts );
		}
		$parts[] = $this->build_submit_button_block();
		return implode( "\n", $parts );
	}

	// -------------------------------------------------------------------------
	// Amounts translation
	// -------------------------------------------------------------------------

	/**
	 * Translate legacy amounts array to the native schema (rename subscription keys).
	 *
	 * @param array $raw Legacy `amounts` JSON decoded.
	 * @return array     Native-schema amounts.
	 */
	private function translate_amounts( array $raw ): array {
		$out = array();
		foreach ( $raw as $a ) {
			if ( ! is_array( $a ) ) {
				continue;
			}
			$key = sanitize_key( $a['key'] ?? sanitize_title( $a['label'] ?? 'opt' ) );
			if ( '' === $key ) {
				$key = 'opt-' . wp_generate_password( 9, false, false );
			}

			$billing_type = sanitize_text_field( $a['billing_type'] ?? 'One Time' );
			$entry        = array(
				'key'          => $key,
				'label'        => sanitize_text_field( $a['label'] ?? 'Plan' ),
				'description'  => sanitize_text_field( $a['description'] ?? '' ),
				'amount'       => max( 0, (float) ( $a['amount'] ?? 0 ) ),
				'billing_type' => $billing_type,
			);

			if ( 'Subscription' === $billing_type ) {
				$entry['billing_period'] = sanitize_text_field( $a['billing_period'] ?? 'month' );
				// Legacy keys renamed in native model.
				$entry['setup_fee']     = max( 0, (float) ( $a['additional_charge'] ?? $a['setup_fee'] ?? 0 ) );
				$entry['billing_cycle'] = sanitize_text_field( (string) ( $a['total_billing_cycle'] ?? $a['billing_cycle'] ?? '' ) );
			}

			$out[] = $entry;
		}
		return $out;
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

	/**
	 * Pass through unrecognised smartpay-form/* blocks by re-serializing the
	 * parsed block array verbatim (keeps innerContent + innerHTML already stored).
	 */
	private function serialize_raw_block( array $block ): string {
		if ( function_exists( 'serialize_block' ) ) {
			return serialize_block( $block );
		}
		// Fallback: emit the block comment without inner HTML (renders nothing, safe).
		$name  = $block['blockName'] ?? '';
		$attrs = $block['attrs'] ?? array();
		$json  = empty( $attrs ) ? '' : ' ' . wp_json_encode( $attrs );
		return "<!-- wp:{$name}{$json} /-->";
	}
}
