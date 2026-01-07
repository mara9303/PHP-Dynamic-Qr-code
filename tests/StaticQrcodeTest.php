<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/lib/StaticQrcode/StaticQrcode.php';

class StaticQrcodeTest extends TestCase {
    private $staticQrcode;

    protected function setUp(): void {
        $this->staticQrcode = new StaticQrcode();
    }

    public function testCreateStaticQrcode() {
        $input_data = [
            'filename' => 'test_static',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->staticQrcode->createStaticQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }

    public function testEditStaticQrcode() {
        $input_data = [
            'filename' => 'test_static',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->staticQrcode->editStaticQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }
}