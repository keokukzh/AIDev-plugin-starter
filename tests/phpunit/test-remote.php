<?php
use PHPUnit\Framework\TestCase;

class RemoteFetchTest extends TestCase {

public function setUp(): void {
parent::setUp();
update_option( 'aidev-plugin-starter_remote_url', 'https://example.test/api' );

// Mock HTTP via pre_http_request to avoid real network.
add_filter(
'pre_http_request',
function( $pre, $args, $url ) {
if ( false !== strpos( $url, 'example.test/api' ) ) {
return array(
'headers'  => array(),
'body'     => json_encode( array( 'title' => 'Mocked Title' ) ),
'response' => array( 'code' => 200, 'message' => 'OK' ),
'cookies'  => array(),
'filename' => null,
);
}
return $pre;
},
10,
3
);
}

public function test_remote_fetch_is_cached(): void {
delete_transient( 'aidev_ps_remote_cache' );

$data1 = aidev_ps_get_remote_data();
$this->assertIsArray( $data1 );
$this->assertSame( 'Mocked Title', $data1['title'] );

// Should be returned from cache now.
update_option( 'aidev-plugin-starter_remote_url', 'https://example.test/changed' );
$data2 = aidev_ps_get_remote_data();
$this->assertEquals( $data1, $data2 );
}
}