<?php

use PHPUnit\Framework\TestCase;

class QrDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    public function testDynamicQrcodeCRUD(): void
    {
        $db = getDbInstance();
        $uniqueName = 'test_dynamic_' . time();
        $dataToDb = [
            'filename' => $uniqueName,
            'format' => 'png',
            'identifier' => substr(md5(time()), 0, 6),
            'link' => 'https://example.com/test',
            'qrcode' => $uniqueName . '.png',
            'scan' => 0,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'logo_company' => ''
        ];
        $qrId = $db->insert('dynamic_qrcodes', $dataToDb);
        $this->assertGreaterThan(0, $qrId, 'QR should be created');

        $db = getDbInstance();
        $db->where('id', $qrId);
        $result = $db->getOne('dynamic_qrcodes');
        $this->assertEquals($uniqueName, $result['filename']);
        $this->assertEquals(0, (int)$result['scan']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->update('dynamic_qrcodes', ['state' => 'disable']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $updated = $db->getOne('dynamic_qrcodes');
        $this->assertEquals('disable', $updated['state']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->delete('dynamic_qrcodes');

        $db = getDbInstance();
        $db->where('id', $qrId);
        $deleted = $db->getOne('dynamic_qrcodes');
        $this->assertNull($deleted);
    }

    public function testStaticQrcodeCRUD(): void
    {
        $db = getDbInstance();
        $uniqueName = 'test_static_' . time();
        $dataToDb = [
            'filename' => $uniqueName,
            'format' => 'png',
            'type' => 'text',
            'content' => 'Test content',
            'qrcode' => $uniqueName . '.png',
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'logo_company' => ''
        ];
        $qrId = $db->insert('static_qrcodes', $dataToDb);
        $this->assertGreaterThan(0, $qrId);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $result = $db->getOne('static_qrcodes');
        $this->assertEquals($uniqueName, $result['filename']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->delete('static_qrcodes');

        $db = getDbInstance();
        $db->where('id', $qrId);
        $deleted = $db->getOne('static_qrcodes');
        $this->assertNull($deleted);
    }

    public function testLogosCRUD(): void
    {
        $db = getDbInstance();
        $uniqueName = 'test_logo_' . time();
        $dataToDb = [
            'name' => $uniqueName,
            'filename' => 'logo_' . time() . '.png',
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

    public function testScanCounterIncrement(): void
    {
        $db = getDbInstance();
        $uniqueName = 'test_scan_' . time();
        $dataToDb = [
            'filename' => $uniqueName,
            'format' => 'png',
            'identifier' => substr(md5(time()), 0, 6),
            'link' => 'https://example.com',
            'qrcode' => $uniqueName . '.png',
            'scan' => 0,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $qrId = $db->insert('dynamic_qrcodes', $dataToDb);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->update('dynamic_qrcodes', ['scan' => $db->inc(1)]);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $result = $db->getOne('dynamic_qrcodes');
        $this->assertEquals(1, (int)$result['scan']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->delete('dynamic_qrcodes');
    }

    public function testDynamicQrWithLogoCompany(): void
    {
        $db = getDbInstance();
        $uniqueName = 'test_with_logo_' . time();
        $dataToDb = [
            'filename' => $uniqueName,
            'format' => 'png',
            'identifier' => substr(md5(time()), 0, 6),
            'link' => 'https://example.com',
            'qrcode' => $uniqueName . '.png',
            'scan' => 0,
            'state' => 'enable',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'logo_company' => '1'
        ];
        $qrId = $db->insert('dynamic_qrcodes', $dataToDb);
        $this->assertGreaterThan(0, $qrId);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $result = $db->getOne('dynamic_qrcodes');
        $this->assertEquals('1', $result['logo_company']);

        $db = getDbInstance();
        $db->where('id', $qrId);
        $db->delete('dynamic_qrcodes');
    }
}
