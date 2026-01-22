<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/lib/StaticQrcode/StaticQrcode.php';

class StaticQrcodeTest extends TestCase
{
    private StaticQrcode $staticQrcode;
    private static int $createdQrIdWithoutLogo = 0;
    private static int $createdQrIdWithLogo = 0;

    protected function setUp(): void
    {
        $this->staticQrcode = new StaticQrcode();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    /**
     * Test 1: Create a Static QR without logo
     */
    public function testCreateStaticQrcodeWithoutLogo(): void
    {
        $uniqueName = 'test_static_no_logo_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'type' => 'text',
            'content' => 'Test content without logo',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->staticQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('static_qrcodes');

        $this->assertNotNull($result, 'Static QR code should be created in database');
        $this->assertEquals($uniqueName, $result['filename']);
        $this->assertEmpty($result['logo_company'], 'Logo company should be empty');
        
        self::$createdQrIdWithoutLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'Static QR code image file should exist');
    }

    /**
     * Test 2: Edit a Static QR without logo
     * @depends testCreateStaticQrcodeWithoutLogo
     */
    public function testEditStaticQrcodeWithoutLogo(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set from previous test');

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $existingQr = $db->getOne('static_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing Static QR should be found');

        $inputData = [
            'id' => self::$createdQrIdWithoutLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->staticQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $updatedQr = $db->getOne('static_qrcodes');

        $this->assertNotNull($updatedQr, 'Static QR should still exist after edit');
    }

    /**
     * Test 3: Create a Static QR with logo
     */
    public function testCreateStaticQrcodeWithLogo(): void
    {
        $db = getDbInstance();
        $db->where('state', 'enable');
        $logo = $db->getOne('logos');
        
        if (!$logo) {
            $this->markTestSkipped('No logos available in database for testing');
        }

        $uniqueName = 'test_static_with_logo_' . time();
        
        $_POST['logo'] = $logo['id'];
        $inputData = [
            'filename' => $uniqueName,
            'type' => 'text',
            'content' => 'Test content with logo',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => '',
            'logo' => $logo['id']
        ];

        try {
            $this->staticQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('static_qrcodes');

        $this->assertNotNull($result, 'Static QR code with logo should be created');
        $this->assertEquals($logo['id'], $result['logo_company'], 'Logo company should match');
        
        self::$createdQrIdWithLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'Static QR code with logo should exist in logo folder');
    }

    /**
     * Test 4: Edit a Static QR with logo
     * @depends testCreateStaticQrcodeWithLogo
     */
    public function testEditStaticQrcodeWithLogo(): void
    {
        if (self::$createdQrIdWithLogo === 0) {
            $this->markTestSkipped('No Static QR with logo was created');
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $existingQr = $db->getOne('static_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing Static QR with logo should be found');

        $inputData = [
            'id' => self::$createdQrIdWithLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->staticQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $updatedQr = $db->getOne('static_qrcodes');

        $this->assertNotNull($updatedQr, 'Static QR with logo should still exist after edit');
        
        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $updatedQr['qrcode'];
        $this->assertFileExists($qrFilePath, 'Static QR code image should still exist after edit');
    }

    /**
     * Test 5: Read/Get Static QR codes
     * @depends testCreateStaticQrcodeWithoutLogo
     */
    public function testReadStaticQrcode(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set');

        $qrcode = $this->staticQrcode->getQrcode(self::$createdQrIdWithoutLogo);

        $this->assertNotNull($qrcode, 'Static QR code should be readable');
        $this->assertArrayHasKey('id', $qrcode);
        $this->assertArrayHasKey('filename', $qrcode);
        $this->assertArrayHasKey('type', $qrcode);
        $this->assertEquals(self::$createdQrIdWithoutLogo, $qrcode['id']);
    }

    /**
     * Test 6: Delete Static QR codes
     * @depends testEditStaticQrcodeWithoutLogo
     * @depends testEditStaticQrcodeWithLogo
     */
    public function testDeleteStaticQrcode(): void
    {
        if (self::$createdQrIdWithoutLogo > 0) {
            try {
                $this->staticQrcode->deleteQrcode(self::$createdQrIdWithoutLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithoutLogo);
            $result = $db->getOne('static_qrcodes');
            
            $this->assertNull($result, 'Static QR without logo should be deleted from database');
        }

        if (self::$createdQrIdWithLogo > 0) {
            try {
                $this->staticQrcode->deleteQrcode(self::$createdQrIdWithLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithLogo);
            $result = $db->getOne('static_qrcodes');
            
            $this->assertNull($result, 'Static QR with logo should be deleted from database');
        }
    }
}