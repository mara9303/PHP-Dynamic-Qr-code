<?php

use PHPUnit\Framework\TestCase;

require_once '/var/www/html/lib/Logo/Logo.php';

class LogoTest extends TestCase
{
    private Logo $logo;
    private static string $testImagePath = '';

    protected function setUp(): void
    {
        $this->logo = new Logo();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    public static function setUpBeforeClass(): void
    {
        self::$testImagePath = sys_get_temp_dir() . '/test_logo_' . time() . '.png';
        $img = imagecreatetruecolor(600, 193);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        imagepng($img, self::$testImagePath);
        imagedestroy($img);
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::$testImagePath)) {
            unlink(self::$testImagePath);
        }
    }

    public function testValidateImageDimensionsCorrect(): void
    {
        $result = $this->logo->validateImageDimensions(self::$testImagePath);
        $this->assertTrue($result['valid']);
        $this->assertEquals(600, $result['width']);
        $this->assertEquals(193, $result['height']);
    }

    public function testValidateImageDimensionsIncorrect(): void
    {
        $wrongImagePath = sys_get_temp_dir() . '/test_wrong_' . time() . '.png';
        $img = imagecreatetruecolor(100, 100);
        imagepng($img, $wrongImagePath);
        imagedestroy($img);

        $result = $this->logo->validateImageDimensions($wrongImagePath);
        $this->assertFalse($result['valid']);
        
        unlink($wrongImagePath);
    }

    public function testValidateExtensionValid(): void
    {
        $this->assertTrue($this->logo->validateExtension('test.png'));
        $this->assertTrue($this->logo->validateExtension('test.jpg'));
        $this->assertTrue($this->logo->validateExtension('test.jpeg'));
    }

    public function testValidateExtensionInvalid(): void
    {
        $this->assertFalse($this->logo->validateExtension('test.gif'));
        $this->assertFalse($this->logo->validateExtension('test.bmp'));
    }

    public function testGetRequiredDimensions(): void
    {
        $dimensions = Logo::getRequiredDimensions();
        $this->assertEquals(600, $dimensions['width']);
        $this->assertEquals(193, $dimensions['height']);
    }

    public function testGetAllLogos(): void
    {
        $logos = $this->logo->getAllLogos();
        $this->assertIsArray($logos);
    }

    public function testGetLogoUrlContainsCacheBusting(): void
    {
        $url = $this->logo->getLogoUrl('test.png');
        $this->assertStringContainsString('?v=', $url);
    }

    public function testSetOrderingValues(): void
    {
        $ordering = $this->logo->setOrderingValues();
        $this->assertIsArray($ordering);
        $this->assertArrayHasKey('id', $ordering);
        $this->assertArrayHasKey('name', $ordering);
    }

    public function testCreateAndDeleteLogo(): void
    {
        $db = getDbInstance();
        $uniqueName = 'Test Logo ' . time();
        $dataToDb = [
            'name' => $uniqueName,
            'filename' => 'test_' . time() . '.png',
            'original_filename' => 'test.png',
            'width' => 600,
            'height' => 193,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $logoId = $db->insert('logos', $dataToDb);
        $this->assertGreaterThan(0, $logoId);

        $db = getDbInstance();
        $db->where('id', $logoId);
        $result = $db->getOne('logos');
        $this->assertEquals($uniqueName, $result['name']);

        $db = getDbInstance();
        $db->where('id', $logoId);
        $db->delete('logos');

        $db = getDbInstance();
        $db->where('id', $logoId);
        $deleted = $db->getOne('logos');
        $this->assertNull($deleted);
    }
}
