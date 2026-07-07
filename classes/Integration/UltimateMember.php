<?php

namespace JDITC\Wordfence_2FA_for_Ultimate_Member\Integration;

if ( ! defined( 'ABSPATH' ) ) exit;

class UltimateMember {
	/**
	 * Wordfence login security WP_Error codes that UM should surface directly.
	 *
	 * @var string[]
	 */
	private $wordfence_error_codes = array(
		'wfls_twofactor_required',
		'wfls_twofactor_failed',
		'wfls_twofactor_blocked',
		'wfls_captcha_verify',
		'wfls_captcha_expired',
		'wfls_captcha_required',
		'wfls_email_verified',
		'wfls_email_not_verified',
	);

	public function __construct() {
		add_filter( 'um_custom_authenticate_error_codes', array( $this, 'add_wordfence_auth_error_codes' ) );
		add_action( 'um_after_login_fields', array( $this, 'render_wordfence_2fa_fields' ), 20 );
	}

	/**
	 * Allow UM to display Wordfence's own 2FA/auth error messages.
	 *
	 * @param array $codes Existing third-party error codes.
	 * @return array
	 */
	public function add_wordfence_auth_error_codes( $codes ) {
		if ( ! is_array( $codes ) ) {
			$codes = array();
		}

		$codes = array_merge( $codes, $this->wordfence_error_codes );
		$codes = array_values( array_unique( $codes ) );
		return $codes;
	}

	/**
	 * Render Wordfence 2FA fields on UM login forms.
	 */
	public function render_wordfence_2fa_fields() {
		if ( ! $this->is_wordfence_login_security_available() ) {
			return;
		}

		$field_id = 'wfls-token-' . wp_generate_uuid4();
		?>
		<div class="um-field" data-key="wfls-token">
			<div class="um-field-label">
				<label for="<?php echo esc_attr( $field_id ); ?>">
					<?php esc_html_e( 'Wordfence 2FA Code (if required)', 'wordfence-2fa-for-ultimate-member' ); ?>
				</label>
			</div>
			<div class="um-field-area">
				<input
					type="text"
					name="wfls-token"
					id="<?php echo esc_attr( $field_id ); ?>"
					class="um-form-field"
					autocomplete="one-time-code"
					inputmode="numeric"
					placeholder="<?php esc_attr_e( '123456', 'wordfence-2fa-for-ultimate-member' ); ?>"
				>
				<div class="um-field-checkbox" style="margin-top:8px;">
					<label>
						<input type="checkbox" name="wfls-remember-device" value="1">
						<?php esc_html_e( 'Remember this device for 30 days', 'wordfence-2fa-for-ultimate-member' ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Detect whether Wordfence Login Security is available.
	 *
	 * @return bool
	 */
	private function is_wordfence_login_security_available() {
		return defined( 'WORDFENCE_LS_VERSION' ) || class_exists( '\\WordfenceLS\\Controller_WordfenceLS' );
	}
}
