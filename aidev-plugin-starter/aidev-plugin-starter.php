<?php
/**
 * Plugin Name: AIDev Plugin Starter
 * Description: Starter-Plugin mit Shortcode, REST-API, HTTP-Fetch (cache) und Cron-Refresh ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œ inkl. Tests.
 * Version:     0.2.0
 * Author:      AIDevelopment
 * License:     GPLv2 or later
 * Text Domain: aidev-plugin-starter
 * Domain Path: /languages
 *
 * @package AIDevPluginStarter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Core constants */
define( 'AIDEV_PS_VERSION', '0.2.0' );
define( 'AIDEV_PS_SLUG', 'aidev-plugin-starter' );
define( 'AIDEV_PS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIDEV_PS_URL', plugin_dir_url( __FILE__ ) );

/** Option keys & transient */
const AIDEV_PS_OPTION_MESSAGE    = 'aidev-plugin-starter_message';
const AIDEV_PS_OPTION_REMOTE_URL = 'aidev-plugin-starter_remote_url';
const AIDEV_PS_TRANSIENT_REMOTE  = 'aidev_ps_remote_cache';
const AIDEV_PS_CRON_HOOK         = 'aidev_ps_refresh_cache';

/**
 * I18n.
 */
add_action(
	'plugins_loaded',
	function (): void {
		load_plugin_textdomain( 'aidev-plugin-starter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

/**
 * Admin: Settings page.
 */
add_action(
	'admin_menu',
	function (): void {
		add_options_page(
			__( 'AIDev Starter', 'aidev-plugin-starter' ),
			__( 'AIDev Starter', 'aidev-plugin-starter' ),
			'manage_options',
			AIDEV_PS_SLUG,
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

	// Save.
	if ( isset( $_POST['aidev_ps_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aidev_ps_nonce'] ) ), AIDEV_PS_SLUG . '_save' ) ) {
		$msg = isset( $_POST['aidev_ps_message'] ) ? sanitize_text_field( wp_unslash( $_POST['aidev_ps_message'] ) ) : '';
		update_option( AIDEV_PS_OPTION_MESSAGE, $msg );

		$url = isset( $_POST['aidev_ps_remote_url'] ) ? esc_url_raw( wp_unslash( $_POST['aidev_ps_remote_url'] ) ) : '';
		update_option( AIDEV_PS_OPTION_REMOTE_URL, $url );

		delete_transient( AIDEV_PS_TRANSIENT_REMOTE ); // Force refresh.
		echo '<div class="updated"><p>' . esc_html__( 'Saved.', 'aidev-plugin-starter' ) . '</p></div>';
	}

	$msg = get_option( AIDEV_PS_OPTION_MESSAGE, '' );
	$url = get_option( AIDEV_PS_OPTION_REMOTE_URL, 'https://jsonplaceholder.typicode.com/todos/1' );

	echo '<div class="wrap"><h1>' . esc_html__( 'AIDev Starter Settings', 'aidev-plugin-starter' ) . '</h1>';
	echo '<form method="post">';
	wp_nonce_field( AIDEV_PS_SLUG . '_save', 'aidev_ps_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr><th scope="row"><label for="aidev_ps_message">' . esc_html__( 'Message', 'aidev-plugin-starter' ) . '</label></th>';
	echo '<td><input type="text" id="aidev_ps_message" name="aidev_ps_message" value="' . esc_attr( $msg ) . '" class="regular-text" /></td></tr>';

	echo '<tr><th scope="row"><label for="aidev_ps_remote_url">' . esc_html__( 'Remote JSON URL (cached)', 'aidev-plugin-starter' ) . '</label></th>';
	echo '<td><input type="url" id="aidev_ps_remote_url" name="aidev_ps_remote_url" value="' . esc_attr( $url ) . '" class="regular-text code" /></td></tr>';

	echo '</tbody></table>';
	submit_button();
	echo '</form></div>';
}

/**
 * Shortcode: [aidev_message]
 * Outputs the stored message (escaped).
 *
 * @return string
 */
function aidev_ps_shortcode_message(): string {
	$msg = get_option( AIDEV_PS_OPTION_MESSAGE, '' );
	return esc_html( (string) $msg );
}
add_shortcode( 'aidev_message', 'aidev_ps_shortcode_message' );

/**
 * Remote fetch with transient cache (~10 min).
 *
 * @return array<string,mixed>|WP_Error
 */
function aidev_ps_get_remote_data() {
	$cached = get_transient( AIDEV_PS_TRANSIENT_REMOTE );
	if ( false !== $cached ) {
		return $cached;
	}

	$url = get_option( AIDEV_PS_OPTION_REMOTE_URL, '' );
	if ( empty( $url ) ) {
		return new WP_Error( 'aidev_no_url', __( 'No remote URL configured.', 'aidev-plugin-starter' ) );
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/json' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'aidev_bad_status', sprintf( 'HTTP %d', $code ) );
	}

	$body = (string) wp_remote_retrieve_body( $response );
	$json = json_decode( $body, true );
	if ( ! is_array( $json ) ) {
		return new WP_Error( 'aidev_bad_json', __( 'Invalid JSON', 'aidev-plugin-starter' ) );
	}

	set_transient( AIDEV_PS_TRANSIENT_REMOTE, $json, 10 * MINUTE_IN_SECONDS );
	return $json;
}

/**
 * REST API: /wp-json/aidev/v1/message
 * GET: returns message + remote
 * POST: updates message (nonce required) ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œ header X-WP-Nonce (wp_create_nonce('wp_rest')).
 */
add_action(
	'rest_api_init',
	function (): void {
		register_rest_route(
			'aidev/v1',
			'/message',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => function () {
						$data   = array(
							'message' => (string) get_option( AIDEV_PS_OPTION_MESSAGE, '' ),
						);
						$remote = aidev_ps_get_remote_data();
						if ( ! is_wp_error( $remote ) ) {
							$data['remote'] = $remote;
						}
						return rest_ensure_response( $data );
					},
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(
						'message' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
					'callback'            => function ( WP_REST_Request $req ) {
						// Core REST nonce check.
						$nonce = $req->get_header( 'X-WP-Nonce' );
						if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
							return new WP_Error( 'rest_forbidden', __( 'Invalid nonce.', 'aidev-plugin-starter' ), array( 'status' => 403 ) );
						}
						update_option( AIDEV_PS_OPTION_MESSAGE, (string) $req->get_param( 'message' ) );
						return rest_ensure_response(
							array(
								'ok'      => true,
								'message' => (string) get_option( AIDEV_PS_OPTION_MESSAGE, '' ),
							)
						);
					},
				),
			)
		);
	}
);

/**
 * CRON: refresh transient periodically.
 */
add_action(
	AIDEV_PS_CRON_HOOK,
	function (): void {
		$result = aidev_ps_get_remote_data(); // refresh sets transient.
		if ( is_wp_error( $result ) ) {
			delete_transient( AIDEV_PS_TRANSIENT_REMOTE ); // Keep safe.
		}
	}
);

/** Schedule on activation */
register_activation_hook(
	__FILE__,
	function (): void {
		if ( ! wp_next_scheduled( AIDEV_PS_CRON_HOOK ) ) {
			wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', AIDEV_PS_CRON_HOOK );
		}
	}
);

/** Clear on deactivation */
register_deactivation_hook(
	__FILE__,
	function (): void {
		wp_clear_scheduled_hook( AIDEV_PS_CRON_HOOK );
	}
);

/**
 * Example function kept for unit test sample.
 *
 * @param int $a First.
 * @param int $b Second.
 * @return int
 */
function aidev_plugin_starter_add( int $a, int $b ): int { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	return (int) $a + (int) $b;
}

/**
 * Public REST endpoint: GET /wp-json/aidev/v1/message
 *
 * @return void
 */
function aidev_ps_register_public_rest(): void {
	register_rest_route(
		'aidev/v1',
		'/message',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true', // Public.
			'callback'            => function () {
				$message = get_option( 'aidev-plugin-starter_message', 'Hello from AIDev' );
				return new \WP_REST_Response(
					array( 'message' => wp_strip_all_tags( (string) $message ) ),
					200
				);
			},
		)
	);
}
add_action( 'rest_api_init', 'aidev_ps_register_public_rest' );
<?php
// === AIDev Agent bootstrap (appended) ======================================
require_once __DIR__ . '/includes/class-aidev-agent.php';
require_once __DIR__ . '/includes/rest-agent.php';

add_action( 'admin_menu', function () {
add_options_page(
'AIDev Plugin Starter',
'AIDev Plugin',
'manage_options',
'aidev-plugin-starter',
'aidev_plugin_starter_render'
);
} );

function aidev_plugin_starter_render() {
    echo <<<HTML
<div class="wrap">
  <h1>AIDev Plugin Starter</h1>
  <p>Kurzer Agent-Test. Die Admin-Seite spricht mit <code>/wp-json/aidev/v1/agent</code>.</p>
  <textarea id="aidev-msg" rows="5" style="width:100%;">Sag Hallo!</textarea>
  <p><button class="button button-primary" id="aidev-send">Fragen</button></p>
  <pre id="aidev-reply" style="background:#111;color:#0f0;padding:12px;"></pre>
</div>
<script>
(function(){
  const btn = document.getElementById('aidev-send');
  btn.addEventListener('click', async () => {
    const msg = (document.getElementById('aidev-msg')).value;
    const url = (window.ajaxurl && window.ajaxurl.includes('admin-ajax.php'))
      ? window.ajaxurl.replace('admin-ajax.php','rest_route=/aidev/v1/agent')
      : (location.origin + '/?rest_route=/aidev/v1/agent');
    const r = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: msg})
    });
    const j = await r.json();
    document.getElementById('aidev-reply').textContent = JSON.stringify(j, null, 2);
  });
})();
</script>
HTML;
},
body: JSON.stringify({message: msg})
});
const j = await r.json();
document.getElementById('aidev-reply').textContent = JSON.stringify(j, null, 2);
});
})();
</script>
<?php
}

