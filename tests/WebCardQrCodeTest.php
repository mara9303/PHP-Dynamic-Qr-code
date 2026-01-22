<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/lib/WebCardQrcode/WebCardQrcode.php';

class WebCardQrCodeTest extends TestCase
{
    private WebCardQrcode $webCardQrcode;
    private static int $createdQrIdWithoutLogo = 0;
    private static int $createdQrIdWithLogo = 0;

    protected function setUp(): void
    {
        $this->webCardQrcode = new WebCardQrcode();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    /**
     * Test 1: Create a Web Card QR without logo
     */
    public function testCreateWebCardQrcodeWithoutLogo(): void
    {
        $uniqueName = 'test_webcard_no_logo_' . time();
        $uniqueIdentifier = 'wc' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'identifier' => $uniqueIdentifier,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'organization' => 'Test Company',
            'work_phone' => '+1234567890',
            'mobile_phone' => '+0987654321',
            'email' => 'john.doe@test.com',
            'website' => 'https://test.com',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'post_code' => '12345',
            'state' => 'TS',
            'country' => 'Test Country',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');

        $this->assertNotNull($result, 'Web Card QR code should be created in database');
        $this->assertEquals($uniqueName, $result['filename']);
        $this->assertEquals('John', $result['first_name']);
        $this->assertEquals('Doe', $result['last_name']);
        
        self::$createdQrIdWithoutLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'Web Card QR code image file should exist');
    }

    /**
     * Test 2: Edit a Web Card QR without logo
     * @depends testCreateWebCardQrcodeWithoutLogo
     */
    public function testEditWebCardQrcodeWithoutLogo(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set from previous test');

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $existingQr = $db->getOne('web_card_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing Web Card QR should be found');

        $inputData = [
            'id' => self::$createdQrIdWithoutLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'organization' => 'Updated Company',
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $updatedQr = $db->getOne('web_card_qrcodes');

        $this->assertEquals('Jane', $updatedQr['first_name'], 'First name should be updated');
        $this->assertEquals('Smith', $updatedQr['last_name'], 'Last name should be updated');
    }

    /**
     * Test 3: Create a Web Card QR with logo
     */
    public function testCreateWebCardQrcodeWithLogo(): void
    {
        $db = getDbInstance();
        $db->where('state', 'enable');
        $logo = $db->getOne('logos');
        
        if (!$logo) {
            $this->markTestSkipped('No logos available in database for testing');
        }

        $uniqueName = 'test_webcard_with_logo_' . time();
        $uniqueIdentifier = 'wcl' . time();
        
        $_POST['logo'] = $logo['id'];
        $inputData = [
            'filename' => $uniqueName,
            'identifier' => $uniqueIdentifier,
            'first_name' => 'Logo',
            'last_name' => 'Test',
            'organization' => 'Logo Company',
            'work_phone' => '+1234567890',
            'email' => 'logo@test.com',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => '',
            'logo' => $logo['id']
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');

        $this->assertNotNull($result, 'Web Card QR code with logo should be created');
        $this->assertEquals($logo['id'], $result['logo_company'], 'Logo company should match');
        
        self::$createdQrIdWithLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'Web Card QR code with logo should exist in logo folder');
    }

    /**
     * Test 4: Edit a Web Card QR with logo
     * @depends testCreateWebCardQrcodeWithLogo
     */
    public function testEditWebCardQrcodeWithLogo(): void
    {
        if (self::$createdQrIdWithLogo === 0) {
            $this->markTestSkipped('No Web Card QR with logo was created');
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $existingQr = $db->getOne('web_card_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing Web Card QR with logo should be found');

        $inputData = [
            'id' => self::$createdQrIdWithLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'first_name' => 'Updated',
            'last_name' => 'Logo',
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $updatedQr = $db->getOne('web_card_qrcodes');

        $this->assertEquals('Updated', $updatedQr['first_name'], 'First name should be updated');
        
        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $updatedQr['qrcode'];
        $this->assertFileExists($qrFilePath, 'Web Card QR code image should still exist after edit');
    }

    /**
     * Test 5: Read/Get Web Card QR codes
     * @depends testCreateWebCardQrcodeWithoutLogo
     */
    public function testReadWebCardQrcode(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set');

        $qrcode = $this->webCardQrcode->getQrcode(self::$createdQrIdWithoutLogo);

        $this->assertNotNull($qrcode, 'Web Card QR code should be readable');
        $this->assertArrayHasKey('id', $qrcode);
        $this->assertArrayHasKey('filename', $qrcode);
        $this->assertArrayHasKey('first_name', $qrcode);
        $this->assertArrayHasKey('last_name', $qrcode);
        $this->assertEquals(self::$createdQrIdWithoutLogo, $qrcode['id']);
    }

    /**
     * Test 6: Delete Web Card QR codes
     * @depends testEditWebCardQrcodeWithoutLogo
     * @depends testEditWebCardQrcodeWithLogo
     */
    public function testDeleteWebCardQrcode(): void
    {
        if (self::$createdQrIdWithoutLogo > 0) {
            try {
                $this->webCardQrcode->deleteQrcode(self::$createdQrIdWithoutLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithoutLogo);
            $result = $db->getOne('web_card_qrcodes');
            
            $this->assertNull($result, 'Web Card QR without logo should be deleted from database');
        }

        if (self::$createdQrIdWithLogo > 0) {
            try {
                $this->webCardQrcode->deleteQrcode(self::$createdQrIdWithLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithLogo);
            $result = $db->getOne('web_card_qrcodes');
            
            $this->assertNull($result, 'Web Card QR with logo should be deleted from database');
        }
    }

    /**
     * Test 7: Verify unique identifier is generated for Web Card
     */
    public function testUniqueIdentifierGenerated(): void
    {
        $uniqueName = 'test_wc_identifier_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'first_name' => 'Test',
            'last_name' => 'Identifier',
            'organization' => 'Test Org',
            'email' => 'test@test.com',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');

        $this->assertNotNull($result);
        $this->assertNotEmpty($result['identifier'], 'Identifier should be generated');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('web_card_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }

    /**
     * Test 8: Verify vCard data is properly stored
     */
    public function testVCardDataStored(): void
    {
        $uniqueName = 'test_vcard_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'first_name' => 'VCard',
            'last_name' => 'Test',
            'organization' => 'VCard Company',
            'work_phone' => '+1234567890',
            'mobile_phone' => '+0987654321',
            'email' => 'vcard@test.com',
            'website' => 'https://vcard.test',
            'address' => '123 VCard Street',
            'city' => 'VCard City',
            'post_code' => '12345',
            'country' => 'VCard Country',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');

        $this->assertNotNull($result);
        $this->assertEquals('VCard', $result['first_name']);
        $this->assertEquals('Test', $result['last_name']);
        $this->assertEquals('VCard Company', $result['organization']);
        $this->assertEquals('+1234567890', $result['work_phone']);
        $this->assertEquals('vcard@test.com', $result['email']);

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('web_card_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }

    /**
     * Test 9: Verify file is deleted when Web Card QR is deleted
     */
    public function testFileDeletedWithWebCardQr(): void
    {
        $uniqueName = 'test_wc_file_delete_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'first_name' => 'File',
            'last_name' => 'Delete',
            'email' => 'delete@test.com',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');
        
        $this->assertNotNull($result);
        $filePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($filePath, 'File should exist before deletion');

        $qrId = $result['id'];

        try {
            $this->webCardQrcode->deleteQrcode($qrId);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $this->assertFileDoesNotExist($filePath, 'File should be deleted with Web Card QR');
    }

    /**
     * Test 10: Web Card QR state can be toggled
     */
    public function testWebCardQrStateToggle(): void
    {
        $uniqueName = 'test_wc_state_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'first_name' => 'State',
            'last_name' => 'Test',
            'email' => 'state@test.com',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('web_card_qrcodes');
        
        $this->assertEquals('enable', $result['state'], 'Default state should be enable');

        // Disable the QR
        $editData = [
            'id' => $result['id'],
            'filename' => $uniqueName,
            'old_filename' => $uniqueName,
            'first_name' => 'State',
            'last_name' => 'Test',
            'state' => 'disable',
            'id_owner' => ''
        ];

        try {
            $this->webCardQrcode->editQrcode($editData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', $result['id']);
        $updated = $db->getOne('web_card_qrcodes');
        
        $this->assertEquals('disable', $updated['state'], 'State should be disabled');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('web_card_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }
}
