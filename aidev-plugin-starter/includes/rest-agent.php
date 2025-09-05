<?php
/**
 * REST route: POST /aidev/v1/agent { message }
 */
use AIDev\Agent;

add_action( 'rest_api_init', function () {
register_rest_route(
'aidev/v1',
'/agent',
array(
'methods'             => 'POST',
'permission_callback' => '__return_true',
'args'                => array(
'message' => array(
'type'     => 'string',
'required' => true,
),
),
'callback'            => function ( WP_REST_Request  ) {
 = ->get_param( 'message' );
return rest_ensure_response( Agent::chat(  ) );
},
)
);
} );
