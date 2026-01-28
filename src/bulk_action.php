<?php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDbInstance();
    $json = json_decode(file_get_contents('php://input'), true);

    $params = $json['params'];
    $files = [];

    if (isset($json['type'])) {
        $type = filter_var($json['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        echo json_encode([
            'data' => 'Type action field in the request.',
            'status' => 400
        ]);
        exit();
    }

    if (count($params) == 0) {
        echo json_encode([
            'data' => 'No qrcodes were selected.',
            'status' => 400
        ]);
        exit();
    }

    foreach ($params as $param) {
        $db = getDbInstance();
        $db->where('id', $param);
        $row = $db->getOne("{$type}_qrcodes");
        if ($row) {
            $hasLogo = !empty($row['logo_company']);
            $baseDir = $hasLogo ? SAVED_QRCODE_DIRECTORY_LOGO : SAVED_QRCODE_DIRECTORY;
            $files[] = $baseDir . $row['qrcode'];
        }
    }

    $zip = new ZipArchive();
    $uniqid = uniqid();
    $zipDir = SAVED_QRCODE_DIRECTORY . 'zip/';
    if (!is_dir($zipDir)) {
        mkdir($zipDir, 0755, true);
    }
    $zipPath = $zipDir . 'qrcodes_'. $uniqid .'.zip';
    @unlink($zipPath);
    $url_path = SAVED_QRCODE_URL . 'zip/qrcodes_'. $uniqid .'.zip';
    $zip->open($zipPath, ZipArchive::CREATE);

    foreach ($files as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, basename($file));
        }
    }

    $zip->close();
    
    echo json_encode([
        'data' => $url_path,
        'status' => 200
    ]);
    exit();
} else {
    exit('Direct access to this script not allowed.');
}
?>