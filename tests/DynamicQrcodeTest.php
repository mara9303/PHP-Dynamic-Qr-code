<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/lib/DynamicQrcode/DynamicQrcode.php';

class DynamicQrcodeTest extends TestCase
{
    private DynamicQrcode $dynamicQrcode;
    private static int $createdQrIdWithoutLogo = 0;
    private static int $createdQrIdWithLogo = 0;

    protected function setUp(): void
    {
        $this->dynamicQrcode = new DynamicQrcode();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    /**
     * Test 1: Create a Dynamic QR without logo
     */
    public function testCreateDynamicQrcodeWithoutLogo(): void
    {
        $uniqueName = 'test_dynamic_no_logo_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/test-no-logo',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        $db = getDbInstance();
        $initialCount = $db->getValue('dynamic_qrcodes', 'count(*)');

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit(), so we catch it here
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');

        $this->assertNotNull($result, 'QR code should be created in database');
        $this->assertEquals($uniqueName, $result['filename']);
        $this->assertEmpty($result['logo_company'], 'Logo company should be empty');
        
        self::$createdQrIdWithoutLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'QR code image file should exist');
    }

    /**
     * Test 2: Edit a Dynamic QR without logo
     * @depends testCreateDynamicQrcodeWithoutLogo
     */
    public function testEditDynamicQrcodeWithoutLogo(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set from previous test');

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $existingQr = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing QR should be found');

        $newLink = 'https://example.com/updated-no-logo';
        $inputData = [
            'id' => self::$createdQrIdWithoutLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'link' => $newLink,
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithoutLogo);
        $updatedQr = $db->getOne('dynamic_qrcodes');

        $this->assertEquals($newLink, $updatedQr['link'], 'Link should be updated');
    }

    /**
     * Test 3: Create a Dynamic QR with logo
     */
    public function testCreateDynamicQrcodeWithLogo(): void
    {
        $db = getDbInstance();
        $db->where('state', 'enable');
        $logo = $db->getOne('logos');
        
        if (!$logo) {
            $this->markTestSkipped('No logos available in database for testing');
        }

        $uniqueName = 'test_dynamic_with_logo_' . time();
        
        $_POST['logo'] = $logo['id'];
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/test-with-logo',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => '',
            'logo' => $logo['id']
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');

        $this->assertNotNull($result, 'QR code with logo should be created');
        $this->assertEquals($logo['id'], $result['logo_company'], 'Logo company should match');
        
        self::$createdQrIdWithLogo = (int)$result['id'];

        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $result['qrcode'];
        $this->assertFileExists($qrFilePath, 'QR code with logo image should exist in logo folder');
    }

    /**
     * Test 4: Edit a Dynamic QR with logo
     * @depends testCreateDynamicQrcodeWithLogo
     */
    public function testEditDynamicQrcodeWithLogo(): void
    {
        if (self::$createdQrIdWithLogo === 0) {
            $this->markTestSkipped('No QR with logo was created');
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $existingQr = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($existingQr, 'Existing QR with logo should be found');

        $newLink = 'https://example.com/updated-with-logo';
        $inputData = [
            'id' => self::$createdQrIdWithLogo,
            'filename' => $existingQr['filename'],
            'old_filename' => $existingQr['filename'],
            'link' => $newLink,
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->editQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', self::$createdQrIdWithLogo);
        $updatedQr = $db->getOne('dynamic_qrcodes');

        $this->assertEquals($newLink, $updatedQr['link'], 'Link should be updated');
        
        $qrFilePath = SAVED_QRCODE_DIRECTORY_LOGO . $updatedQr['qrcode'];
        $this->assertFileExists($qrFilePath, 'QR code image should still exist after edit');
    }

    /**
     * Test 5: Read/Get Dynamic QR codes
     * @depends testCreateDynamicQrcodeWithoutLogo
     */
    public function testReadDynamicQrcode(): void
    {
        $this->assertGreaterThan(0, self::$createdQrIdWithoutLogo, 'QR ID should be set');

        $qrcode = $this->dynamicQrcode->getQrcode(self::$createdQrIdWithoutLogo);

        $this->assertNotNull($qrcode, 'QR code should be readable');
        $this->assertArrayHasKey('id', $qrcode);
        $this->assertArrayHasKey('filename', $qrcode);
        $this->assertArrayHasKey('link', $qrcode);
        $this->assertArrayHasKey('identifier', $qrcode);
        $this->assertEquals(self::$createdQrIdWithoutLogo, $qrcode['id']);
    }

    /**
     * Test 6: Delete Dynamic QR codes
     * @depends testEditDynamicQrcodeWithoutLogo
     * @depends testEditDynamicQrcodeWithLogo
     */
    public function testDeleteDynamicQrcode(): void
    {
        if (self::$createdQrIdWithoutLogo > 0) {
            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithoutLogo);
            $qr = $db->getOne('dynamic_qrcodes');
            
            try {
                $this->dynamicQrcode->deleteQrcode(self::$createdQrIdWithoutLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithoutLogo);
            $result = $db->getOne('dynamic_qrcodes');
            
            $this->assertNull($result, 'QR without logo should be deleted from database');
        }

        if (self::$createdQrIdWithLogo > 0) {
            try {
                $this->dynamicQrcode->deleteQrcode(self::$createdQrIdWithLogo);
            } catch (\Exception $e) {
                // Method calls exit()
            }

            $db = getDbInstance();
            $db->where('id', self::$createdQrIdWithLogo);
            $result = $db->getOne('dynamic_qrcodes');
            
            $this->assertNull($result, 'QR with logo should be deleted from database');
        }
    }

    /**
     * Test 7: Verify unique identifier is generated
     */
    public function testUniqueIdentifierGenerated(): void
    {
        $uniqueName = 'test_identifier_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/identifier-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');

        $this->assertNotNull($result);
        $this->assertNotEmpty($result['identifier'], 'Identifier should be generated');
        $this->assertGreaterThanOrEqual(5, strlen($result['identifier']), 'Identifier should be at least 5 characters');
        $this->assertLessThanOrEqual(8, strlen($result['identifier']), 'Identifier should be at most 8 characters');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('dynamic_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }

    /**
     * Test 8: Verify scan counter starts at zero
     */
    public function testScanCounterStartsAtZero(): void
    {
        $uniqueName = 'test_scan_counter_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/scan-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');

        $this->assertNotNull($result);
        $this->assertEquals(0, (int)$result['scan'], 'Scan counter should start at 0');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('dynamic_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }

    /**
     * Test 9: Verify file is deleted when QR is deleted
     */
    public function testFileDeletedWithQr(): void
    {
        $uniqueName = 'test_file_delete_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/file-delete-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($result);
        $filePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($filePath, 'File should exist before deletion');

        $qrId = $result['id'];

        try {
            $this->dynamicQrcode->deleteQrcode($qrId);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $this->assertFileDoesNotExist($filePath, 'File should be deleted with QR');
    }

    /**
     * Test 10: Verify file rename when filename changes
     */
    public function testFileRenamedOnEdit(): void
    {
        $uniqueName = 'test_rename_original_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/rename-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($result);
        $oldFilePath = SAVED_QRCODE_DIRECTORY . $result['qrcode'];
        $this->assertFileExists($oldFilePath);

        $newName = 'test_rename_updated_' . time();
        $editData = [
            'id' => $result['id'],
            'filename' => $newName,
            'old_filename' => $uniqueName,
            'link' => 'https://example.com/rename-test-updated',
            'state' => 'enable',
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->editQrcode($editData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $newFilePath = SAVED_QRCODE_DIRECTORY . $newName . '.png';
        
        $this->assertFileDoesNotExist($oldFilePath, 'Old file should not exist');
        $this->assertFileExists($newFilePath, 'New file should exist');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('dynamic_qrcodes');
        @unlink($newFilePath);
    }

    /**
     * Test 11: Admin user cannot delete QR of another user
     */
    public function testAdminCannotDeleteOtherUserQr(): void
    {
        // Create QR as user 1
        $uniqueName = 'test_permission_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/permission-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => '1'
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($result);

        // Switch to admin user (different user)
        $_SESSION['type'] = 'admin';
        $_SESSION['user_id'] = 999; // Different user ID

        try {
            $this->dynamicQrcode->deleteQrcode($result['id']);
        } catch (\Exception $e) {
            // Expected - should fail
        }

        // QR should still exist
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $stillExists = $db->getOne('dynamic_qrcodes');
        
        $this->assertNotNull($stillExists, 'QR should still exist - admin cannot delete other user QR');

        // Reset session and cleanup
        $_SESSION['type'] = 'super';
        $_SESSION['user_id'] = 1;
        
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('dynamic_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }

    /**
     * Test 12: QR state can be toggled (enable/disable)
     */
    public function testQrStateToggle(): void
    {
        $uniqueName = 'test_state_toggle_' . time();
        
        $_POST['logo'] = '';
        $inputData = [
            'filename' => $uniqueName,
            'link' => 'https://example.com/state-test',
            'format' => 'png',
            'foreground' => '#000000',
            'background' => '#ffffff',
            'size' => 600,
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->addQrcode($inputData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('filename', $uniqueName);
        $result = $db->getOne('dynamic_qrcodes');
        
        $this->assertEquals('enable', $result['state'], 'Default state should be enable');

        // Disable the QR
        $editData = [
            'id' => $result['id'],
            'filename' => $uniqueName,
            'old_filename' => $uniqueName,
            'link' => 'https://example.com/state-test',
            'state' => 'disable',
            'id_owner' => ''
        ];

        try {
            $this->dynamicQrcode->editQrcode($editData);
        } catch (\Exception $e) {
            // Method calls exit()
        }

        $db = getDbInstance();
        $db->where('id', $result['id']);
        $updated = $db->getOne('dynamic_qrcodes');
        
        $this->assertEquals('disable', $updated['state'], 'State should be disabled');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $result['id']);
        $db->delete('dynamic_qrcodes');
        @unlink(SAVED_QRCODE_DIRECTORY . $result['qrcode']);
    }
}

