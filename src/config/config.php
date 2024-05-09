<?php
//Note: This file should be included first in every php page.
require_once ('environment.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', dirname(dirname(__FILE__)));
define('CURRENT_PAGE', basename($_SERVER['REQUEST_URI']));
define('SCRIPT_NAME', ltrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

if(SCRIPT_NAME === "")
    define('SCRIPT_FOLDER', "");
else
    define('SCRIPT_FOLDER', "/" . SCRIPT_NAME);

require_once BASE_PATH . '/lib/MysqliDb/MysqliDb.php';
require_once BASE_PATH . '/helpers/helpers.php';

/* SAVED QR CODES */
//You can change the folder where the qr code will be saved
define('SAVED_QRCODE_FOLDER', './saved_qrcode/');
define('SAVED_QRCODE_LOGO_FOLDER', './saved_qrcode/logo/');
define('SAVED_QRCODE_DIRECTORY', BASE_PATH.'/saved_qrcode/');
define('SAVED_QRCODE_DIRECTORY_LOGO', BASE_PATH.'/saved_qrcode/logo/');
define('SAVED_QRCODE_URL', base_url(). SCRIPT_FOLDER .'/saved_qrcode/');

//You can change the page name for the redirect and the search parameter (the default is "id")
define('READ_PATH', base_url().'/read.php?id=');
define('READ_WEB_CARD_PATH', base_url().'/read_web_card.php?id=');

//LOGO_QR
define('LOGO_QR', base_url().'/dist/img/prueba-2.png');
define('LOGOS_PATH', base_url().'/dist/img/');

//QR LEVEL
define('QR_LEVEL', 'H');

/**
 * Get instance of DB object
 */
function getDbInstance() {
    return new MysqliDb (Array (
        'host' => DATABASE_HOST,
        'username' => DATABASE_USER,
        'password' => DATABASE_PASSWORD,
        'db'=> DATABASE_NAME,
        'port' => DATABASE_PORT,
        'prefix' => DATABASE_PREFIX,
        'charset' => DATABASE_CHARSET));
}
