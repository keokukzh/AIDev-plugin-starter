<?php
/**
 * Simple agent adapter with CI mock.
 */

namespace AIDev;

if ( ! class_exists( __NAMESPACE__ . '\\Agent' ) ) {
class Agent {
/**
 * Chat with the configured agent. In CI mock mode, returns a canned reply.
 *
 * @param string $message
 * @return array
 */
public static function chat( $message ) {
$mock = get_option( 'aidev_agent_mock', 0 );
if ( $mock ) {
return array(
'reply'    => 'mock: ' . $message,
'provider' => 'mock',
);
}

$provider = get_option( 'aidev_agent_provider', 'openai' );
$api_key  = get_option( 'aidev_openai_api_key', '' );
$model    = get_option( 'aidev_openai_model', 'gpt-4o-mini' );
$system   = get_option( 'aidev_system_prompt', 'You are a helpful WordPress assistant.' );

if ( 'openai' === $provider && ! empty( $api_key ) ) {
$payload = array(
'model'    => $model,
'messages' => array(
array( 'role' => 'system', 'content' => $system ),
array( 'role' => 'user', 'content' => (string) $message ),
),
);

$response = wp_remote_post(
'https://api.openai.com/v1/chat/completions',
array(
'headers' => array(
'Authorization' => 'Bearer ' . $api_key,
'Content-Type'  => 'application/json',
),
'body'    => wp_json_encode( $payload ),
'timeout' => 30,
)
);

if ( is_wp_error( $response ) ) {
return array( 'error' => $response->get_error_message() );
}
$body  = wp_remote_retrieve_body( $response );
$json  = json_decode( $body, true );
$reply = $json['choices'][0]['message']['content'] ?? '';

return array(
'reply'    => $reply,
'provider' => 'openai',
'model'    => $model,
);
}

return array( 'error' => 'No agent configured (set aidev_agent_mock=1 for CI or configure provider/api key).' );
}
}
}
