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
delete_option( 'aidev-plugin-starter_remote_url' );
delete_transient( 'aidev_ps_remote_cache' );
