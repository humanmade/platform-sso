<?php
/**
 * Altis SSO WordPress OAuth.
 *
 * @package altis/sso
 */

namespace Altis\SSO\WordPress;

use Altis;
use HM\Delegated_Auth;

/**
 * Set up action hooks.
 *
 * @return void
 */
function bootstrap() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
	add_filter( 'delegated_oauth.login.button_markup', __NAMESPACE__ . '\\override_button_markup', 0 );
}

/**
 * Load Oauth2 plugin.
 *
 * @return void
 */
function load_plugin() {
	$config = wp_parse_args(
		Altis\get_config()['modules']['sso']['wordpress'],
		[
			'server-rest-base' => '',
			'oauth2-client-id' => '',
			'sync-roles'       => '',
			'cookie'           => true,
		]
	);

	if ( ! empty( $config['server-rest-base'] ) ) {
		define( 'HM_DELEGATED_AUTH_REST_BASE', $config['server-rest-base'] );
	}

	if ( ! empty( $config['oauth2-client-id'] ) && ! empty( $config['cookie'] ) ) {
		define( 'HM_DELEGATED_AUTH_CLIENT_ID', $config['oauth2-client-id'] );
	}

	if ( ! defined( 'HM_DELEGATED_AUTH_LOGIN_TEXT' ) ) {
		define( 'HM_DELEGATED_AUTH_LOGIN_TEXT', __( 'Log in with WordPress SSO', 'altis' ) );
	}

	add_filter( 'delegated_oauth.sync-roles', ( empty( $config['sync-roles'] ) || ! $config['sync-roles'] ) ? '__return_false' : '__return_true' );

	require_once Altis\ROOT_DIR . '/vendor/humanmade/delegated-oauth/plugin.php';

	// Remove built-in login form UI.
	remove_action( 'login_form', 'HM\\Delegated_Auth\\Cookie\\on_login_form' );
}

/**
 * Show SSO login link in login form
 *
 * @action login_form
 */
function render_login_link() : void {
	Delegated_Auth\Cookie\on_login_form();
}

/**
 * Get the log in button markup, overriding Delegated OAuth's.
 *
 * @return string
 */
function override_button_markup() : string {
	return '<p class="altis-sso-wordpress"><a class="button button-hero" href="%1$s">%2$s</a></p>';
}
