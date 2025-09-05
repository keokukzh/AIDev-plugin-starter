<?php
/**
 * Uninstall cleanup for AIDev Plugin Starter.
 *
 * @package AIDevPluginStarter
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'aidev-plugin-starter_message' );
