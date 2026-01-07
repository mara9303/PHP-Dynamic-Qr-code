<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/lib/WebCardQrcode/WebCardQrcode.php';

class WebCardQrCodeTest extends TestCase {
    private $qrcode;

    protected function setUp(): void {
        $this->qrcode = new WebCardQrCode();
    }

    public function testCreateQrcode() {
        $input_data = [
            'filename' => 'test',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->qrcode->createQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }

    public function testEditQrcode() {
        $input_data = [
            'filename' => 'test',
            'link' => 'https://example.com',
            'format' => 'png'
        ];

        $this->qrcode->editQrcode($input_data);
        $this->assertTrue(true); // Add more assertions as needed
    }

    public function testDeleteQrcode() {
        $id = 1;

        $this->qrcode->deleteQrcode($id);
        $this->assertTrue(true); // Add more assertions as needed
    }
}

?>
