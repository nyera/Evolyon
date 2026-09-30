<?php
/**
 * Account element — links to the frontend dashboard once the Evolyon Toolkit
 * plugin provides one (via the evolyon/account/url filter), and falls back
 * to core WordPress login/profile URLs so it always works standalone.
 *
 * @package Evolyon
 */

defined( 'ABSPATH' ) || exit;

final class Evolyon_Element_Account extends Evolyon_Header_Element {

	public $id = 'account';

	public function __construct() {
		$this->label = __( 'Account', 'evolyon' );
	}

	private function url() {
		$logged_in = is_user_logged_in();

		/**
		 * Point the Account element at a real dashboard page instead of the
		 * default wp-login.php/profile.php — the Evolyon Toolkit plugin's
		 * User Dashboard module hooks in here once it exists.
		 *
		 * @param string $url
		 * @param bool   $logged_in
		 */
		$default_url = $logged_in ? get_edit_profile_url() : wp_login_url( get_permalink() );

		return apply_filters( 'evolyon/account/url', $default_url, $logged_in );
	}

	public function render( $settings ) {
		$logged_in = is_user_logged_in();
		?>
		<a class="evln-icon-btn evln-account-link" href="<?php echo esc_url( $this->url() ); ?>">
			<span class="screen-reader-text">
				<?php echo $logged_in ? esc_html__( 'Your account', 'evolyon' ) : esc_html__( 'Log in', 'evolyon' ); ?>
			</span>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 3.5-6 8-6s8 2 8 6"></path></svg>
		</a>
		<?php
	}
}
