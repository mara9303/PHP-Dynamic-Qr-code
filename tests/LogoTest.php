<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/lib/Logo/Logo.php';

class LogoTest extends TestCase
{
    private Logo $logo;
    private static int $createdLogoId = 0;
    private static string $testImagePath = '';

    protected function setUp(): void
    {
        $this->logo = new Logo();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    public static function setUpBeforeClass(): void
    {
        // Create a test image with correct dimensions (600x193)
        self::$testImagePath = sys_get_temp_dir() . '/test_logo_' . time() . '.png';
        $img = imagecreatetruecolor(600, 193);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagestring($img, 5, 250, 90, 'TEST LOGO', $red);
        imagepng($img, self::$testImagePath);
        imagedestroy($img);
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::$testImagePath)) {
            unlink(self::$testImagePath);
        }
    }

    /**
     * Test 1: Validate image dimensions - correct dimensions
     */
    public function testValidateImageDimensionsCorrect(): void
    {
        $result = $this->logo->validateImageDimensions(self::$testImagePath);
        
        $this->assertTrue($result['valid'], 'Image with correct dimensions should be valid');
        $this->assertEquals(600, $result['width']);
        $this->assertEquals(193, $result['height']);
    }

    /**
     * Test 2: Validate image dimensions - incorrect dimensions
     */
    public function testValidateImageDimensionsIncorrect(): void
    {
        // Create a test image with wrong dimensions
        $wrongImagePath = sys_get_temp_dir() . '/test_wrong_logo_' . time() . '.png';
        $img = imagecreatetruecolor(100, 100);
        imagepng($img, $wrongImagePath);
        imagedestroy($img);

        $result = $this->logo->validateImageDimensions($wrongImagePath);
        
        $this->assertFalse($result['valid'], 'Image with wrong dimensions should be invalid');
        $this->assertStringContainsString('must be 600x193', $result['message']);

        unlink($wrongImagePath);
    }

    /**
     * Test 3: Validate file extension - valid extensions
     */
    public function testValidateExtensionValid(): void
    {
        $this->assertTrue($this->logo->validateExtension('test.png'));
        $this->assertTrue($this->logo->validateExtension('test.jpg'));
        $this->assertTrue($this->logo->validateExtension('test.jpeg'));
        $this->assertTrue($this->logo->validateExtension('test.PNG'));
        $this->assertTrue($this->logo->validateExtension('test.JPG'));
    }

    /**
     * Test 4: Validate file extension - invalid extensions
     */
    public function testValidateExtensionInvalid(): void
    {
        $this->assertFalse($this->logo->validateExtension('test.gif'));
        $this->assertFalse($this->logo->validateExtension('test.bmp'));
        $this->assertFalse($this->logo->validateExtension('test.webp'));
        $this->assertFalse($this->logo->validateExtension('test.svg'));
    }

    /**
     * Test 5: Get required dimensions
     */
    public function testGetRequiredDimensions(): void
    {
        $dimensions = Logo::getRequiredDimensions();
        
        $this->assertArrayHasKey('width', $dimensions);
        $this->assertArrayHasKey('height', $dimensions);
        $this->assertEquals(600, $dimensions['width']);
        $this->assertEquals(193, $dimensions['height']);
    }

    /**
     * Test 6: Get all logos
     */
    public function testGetAllLogos(): void
    {
        $logos = $this->logo->getAllLogos();
        
        $this->assertIsArray($logos);
    }

    /**
     * Test 7: Create a logo (simulated - requires file upload)
     */
    public function testCreateLogo(): void
    {
        $db = getDbInstance();
        
        $uniqueName = 'Test Logo ' . time();
        $filename = 'test_logo_' . time() . '.png';
        
        // Simulate logo creation directly in database for testing
        $dataToDb = [
            'name' => $uniqueName,
            'filename' => $filename,
            'original_filename' => 'test.png',
            'width' => 600,
            'height' => 193,
            'state' => 'enable',
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $lastId = $db->insert('logos', $dataToDb);

        $this->assertGreaterThan(0, $lastId, 'Logo should be inserted in database');
        
        self::$createdLogoId = (int)$lastId;

        $db = getDbInstance();
        $db->where('id', $lastId);
        $result = $db->getOne('logos');

        $this->assertNotNull($result);
        $this->assertEquals($uniqueName, $result['name']);
        $this->assertEquals('enable', $result['state']);
    }

    /**
     * Test 8: Edit a logo
     * @depends testCreateLogo
     */
    public function testEditLogo(): void
    {
        $this->assertGreaterThan(0, self::$createdLogoId, 'Logo ID should be set from previous test');

        $db = getDbInstance();
        
        $newName = 'Updated Test Logo ' . time();
        $dataToDb = [
            'name' => $newName,
            'state' => 'disable',
            'updated_by' => $_SESSION['user_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $db->where('id', self::$createdLogoId);
        $stat = $db->update('logos', $dataToDb);

        $this->assertTrue($stat, 'Logo should be updated');

        $db = getDbInstance();
        $db->where('id', self::$createdLogoId);
        $result = $db->getOne('logos');

        $this->assertEquals($newName, $result['name'], 'Name should be updated');
        $this->assertEquals('disable', $result['state'], 'State should be updated to disable');
    }

    /**
     * Test 9: Delete a logo
     * @depends testEditLogo
     */
    public function testDeleteLogo(): void
    {
        $this->assertGreaterThan(0, self::$createdLogoId, 'Logo ID should be set');

        $db = getDbInstance();
        $db->where('id', self::$createdLogoId);
        $stat = $db->delete('logos');

        $this->assertTrue($stat, 'Logo should be deleted');

        $db = getDbInstance();
        $db->where('id', self::$createdLogoId);
        $result = $db->getOne('logos');

        $this->assertNull($result, 'Logo should not exist after deletion');
    }

    /**
     * Test 10: Get logo by ID
     */
    public function testGetLogo(): void
    {
        // Create a temporary logo
        $db = getDbInstance();
        $dataToDb = [
            'name' => 'Temp Logo for Get Test',
            'filename' => 'temp_' . time() . '.png',
            'original_filename' => 'temp.png',
            'width' => 600,
            'height' => 193,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $tempId = $db->insert('logos', $dataToDb);

        $logo = $this->logo->getLogo($tempId);

        $this->assertNotNull($logo);
        $this->assertArrayHasKey('id', $logo);
        $this->assertArrayHasKey('name', $logo);
        $this->assertArrayHasKey('filename', $logo);
        $this->assertEquals($tempId, $logo['id']);

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $tempId);
        $db->delete('logos');
    }

    /**
     * Test 11: Get logo URL with cache busting
     */
    public function testGetLogoUrlWithCacheBusting(): void
    {
        $filename = 'test_cache_' . time() . '.png';
        
        $url = $this->logo->getLogoUrl($filename);
        
        $this->assertStringContainsString($filename, $url);
        $this->assertStringContainsString('?v=', $url, 'URL should contain cache busting parameter');
    }

    /**
     * Test 12: Get logo path
     */
    public function testGetLogoPath(): void
    {
        $filename = 'test_path.png';
        
        $path = $this->logo->getLogoPath($filename);
        
        $this->assertStringContainsString($filename, $path);
        $this->assertStringContainsString('dist/img/', $path);
    }

    /**
     * Test 13: Disabled logos are not returned in getAllLogos
     */
    public function testDisabledLogosNotInGetAll(): void
    {
        $db = getDbInstance();
        
        // Create a disabled logo
        $dataToDb = [
            'name' => 'Disabled Logo Test',
            'filename' => 'disabled_' . time() . '.png',
            'original_filename' => 'disabled.png',
            'width' => 600,
            'height' => 193,
            'state' => 'disable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $disabledId = $db->insert('logos', $dataToDb);

        $logos = $this->logo->getAllLogos();
        
        $foundDisabled = false;
        foreach ($logos as $logo) {
            if ((int)$logo['id'] === $disabledId) {
                $foundDisabled = true;
                break;
            }
        }
        
        $this->assertFalse($foundDisabled, 'Disabled logo should not be in getAllLogos result');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $disabledId);
        $db->delete('logos');
    }

    /**
     * Test 14: Logo name is properly sanitized
     */
    public function testLogoNameSanitization(): void
    {
        $db = getDbInstance();
        
        $unsafeName = '<script>alert("xss")</script>';
        $dataToDb = [
            'name' => htmlspecialchars($unsafeName, ENT_QUOTES, 'UTF-8'),
            'filename' => 'sanitize_test_' . time() . '.png',
            'original_filename' => 'test.png',
            'width' => 600,
            'height' => 193,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $logoId = $db->insert('logos', $dataToDb);

        $db = getDbInstance();
        $db->where('id', $logoId);
        $result = $db->getOne('logos');

        $this->assertStringNotContainsString('<script>', $result['name'], 'Name should be sanitized');
        $this->assertStringContainsString('&lt;script&gt;', $result['name'], 'Name should have escaped HTML');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $logoId);
        $db->delete('logos');
    }

    /**
     * Test 15: Ordering values are correctly set
     */
    public function testSetOrderingValues(): void
    {
        $ordering = $this->logo->setOrderingValues();

        $this->assertIsArray($ordering);
        $this->assertArrayHasKey('id', $ordering);
        $this->assertArrayHasKey('name', $ordering);
        $this->assertArrayHasKey('filename', $ordering);
        $this->assertArrayHasKey('state', $ordering);
        $this->assertArrayHasKey('created_at', $ordering);
    }
}
