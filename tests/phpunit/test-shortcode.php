<?php
use PHPUnit\Framework\TestCase;

class ShortcodeTest extends TestCase {

public function setUp(): void {
parent::setUp();
if ( function_exists( 'update_option' ) ) {
update_option( 'aidev-plugin-starter_message', 'Hello <b>World</b>' );
}
}

public function test_add_function(): void {
$this->assertSame( 5, aidev_plugin_starter_add( 2, 3 ) );
}

public function test_shortcode_escapes(): void {
$out = do_shortcode( '[aidev_message]' );
$this->assertStringContainsString( 'Hello World', wp_strip_all_tags( $out ) );
$this->assertStringNotContainsString( '<b>', $out );
}
}