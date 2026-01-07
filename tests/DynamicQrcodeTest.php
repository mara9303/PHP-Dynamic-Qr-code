<?php

use PHPUnit\Framework\TestCase;
require_once dirname(dirname(__FILE__)) . '/src/lib/DynamicQrcode/DynamicQrcode.php';

class DynamicQrcodeTest extends TestCase {
    private $dynamicQrcode;

    protected function setUp(): void {
        $this->dynamicQrcode = new DynamicQrcode();
    }

    public function testCreateDynamicQrcode() {
        $input_data = [
            'filename' => 'test_dynamic',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->dynamicQrcode->createDynamicQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }

    public function testEditDynamicQrcode() {
        $input_data = [
            'filename' => 'test_dynamic',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->dynamicQrcode->editDynamicQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }
}

