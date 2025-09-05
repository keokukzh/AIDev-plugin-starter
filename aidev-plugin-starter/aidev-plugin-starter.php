<?php
/**
 * Plugin Name: AIDev Plugin Starter
 * Plugin URI:  https://aidevelopment.example/aidev-plugin-starter
 * Description: Starter-Plugin für AIDevelopment mit CI/CD, Tests, i18n und Best Practices.
 * Version:     0.1.0
 * Author:      AIDevelopment
 * Author URI:  https://aidevelopment.example
 * License:     GPLv2 or later
 * Text Domain: aidev-plugin-starter
 * Domain Path: /languages
 *
 * @package AIDevPluginStarter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIDEV_PS_VERSION', '0.1.0' );
define( 'AIDEV_PS_SLUG', 'aidev-plugin-starter' );
define( 'AIDEV_PS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIDEV_PS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load translations.
 *
 * @return void
 */
add_action(
	'plugins_loaded',
	function (): void {
		load_plugin_textdomain( 'aidev-plugin-starter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

/**
 * Register settings page under "Settings".
 *
 * @return void
 */
add_action(
	'admin_menu',
	function (): void {
		add_options_page(
			__( 'AIDev Starter', 'aidev-plugin-starter' ),
			__( 'AIDev Starter', 'aidev-plugin-starter' ),
			'manage_options',
			'aidev-plugin-starter',
			'aidev_ps_render_settings'
		);
	}
);

/**
 * Render settings page.
 *
 * @return void
 */
function aidev_ps_render_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['aidev_ps_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aidev_ps_nonce'] ) ), 'aidev-plugin-starter_save' ) ) {
		$val = isset( $_POST['aidev_ps_message'] ) ? sanitize_text_field( wp_unslash( $_POST['aidev_ps_message'] ) ) : '';
		update_option( 'aidev-plugin-starter_message', $val );
		echo '<div class="updated"><p>' . esc_html__( 'Saved.', 'aidev-plugin-starter' ) . '</p></div>';
	}

	$msg = get_option( 'aidev-plugin-starter_message', '' );

	echo '<div class="wrap"><h1>' . esc_html__( 'AIDev Starter Settings', 'aidev-plugin-starter' ) . '</h1>';
	echo '<form method="post">';
	wp_nonce_field( 'aidev-plugin-starter_save', 'aidev_ps_nonce' );
	echo '<label for="aidev_ps_message">' . esc_html__( 'Message', 'aidev-plugin-starter' ) . '</label> ';
	echo '<input type="text" id="aidev_ps_message" name="aidev_ps_message" value="' . esc_attr( $msg ) . '" class="regular-text" /> ';
	submit_button();
	echo '</form></div>';
}

/**
 * Small example function for unit tests.
 *
 * @param int $a First number.
 * @param int $b Second number.
 * @return int
 */
function aidev_plugin_starter_add( int $a, int $b ): int {
	return (int) $a + (int) $b;
}
