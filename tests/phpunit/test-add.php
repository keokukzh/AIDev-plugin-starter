<?php
use PHPUnit\Framework\TestCase;

final class AIDev_Add_Test extends TestCase {
    public function test_add() : void {
        $this->assertSame(7, aidev_plugin_starter_add(3, 4));
    }
}
