<?php
/**
 * Function to generate random string.
 * The function takes an integer n as input and generates a string by concatenating n characters chosen randomly from a domain.

N.B. In our case the integer n is randomly chosen between a range of 5 and 8. I chose this "short" range to not overdo the length of the identifier
 */
function randomString($n) {

	$generated_string = "";

	$domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

	$len = strlen($domain);

	// Loop to create random string
	for ($i = 0; $i < $n; $i++) {
		// Generate a random index to pick characters
		$index = rand(0, $len - 1);

		// Concatenating the character
		// in resultant string
		$generated_string = $generated_string . $domain[$index];
	}

	return $generated_string;
}

/**
 *
 */
function getSecureRandomToken() {
	$token = bin2hex(openssl_random_pseudo_bytes(16));
	return $token;
}

/**
 * Clear Auth Cookie
 */
function clearAuthCookie() {

	unset($_COOKIE['series_id']);
	unset($_COOKIE['remember_token']);
	setcookie('series_id', null, -1, '/');
	setcookie('remember_token', null, -1, '/');
}
/**
 *
 */
function clean_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function paginationLinks($current_page, $total_pages, $base_url) {

	if ($total_pages <= 1) {
		return false;
	}

	$html = '';

	if (!empty($_GET)) {
		// We must unset $_GET[page] if previously built by http_build_query function
		unset($_GET['page']);
		// To keep the query sting parameters intact while navigating to next/prev page,
		$http_query = "?" . http_build_query($_GET);
	} else {
		$http_query = "?";
	}

	$html = '<ul class="pagination pagination-sm m-0 float-right">';

	if ($current_page == 1) {

		$html .= '<li class="page-item disabled"><a class="page-link">First</a></li>';
	} else {
		$html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $http_query . '&page=1">First</a></li>';
	}

	// Show pagination links

	//var i = (Number(data.page) > 5 ? Number(data.page) - 4 : 1);

	if ($current_page > 5) {
		$i = $current_page - 4;
	} else {
		$i = 1;
	}

	for (; $i <= ($current_page + 4) && ($i <= $total_pages); $i++) {
		($current_page == $i) ? $li_class = ' class="active"' : $li_class = '';

		$link = $base_url . $http_query;

		$html = $html . '<li class="page-item"' . $li_class . '><a class="page-link" href="' . $link . '&page=' . $i . '">' . $i . '</a></li>';

		if ($i == $current_page + 4 && $i < $total_pages) {

			$html = $html . '<li class="page-item disabled"><a class="page-link">...</a></li>';

		}

	}

	if ($current_page == $total_pages) {
		$html .= '<li class="page-item disabled"><a class="page-link">Last</a></li>';
	} else {

		$html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $http_query . '&page=' . $total_pages . '">Last</a></li>';
	}

	$html = $html . '</ul>';

	return $html;
}

function base_url() {
    require_once(__DIR__ . '/../config/environment.php');
    if (defined('BASE_URL') && BASE_URL !== null) {
        return BASE_URL;
    } else {
        return sprintf(
            "%s://%s:%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT']
        );
    }
}

function current_url() {
	return sprintf(
		"%s://%s%s",
		isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME'],
		$_SERVER['REQUEST_URI']
	);
}

function parse_web_card_object($data) {
	$response = [];
	foreach($data as $key => $value){
		$response[$key] = $value ?? "";
	}
	return $response;
}

function read_key_array($data, $key, $default) {
	return array_key_exists($key, $data) && !empty($data[$key]) ? $data[$key] : $default;
}

function get_logo($logoId, $size) {
	$logo = "";
	if (isset($logoId) && !empty($logoId) && is_numeric($logoId)) {
		$logoData = get_logo_from_db((int)$logoId);
		if ($logoData) {
			$logo = LOGOS_PATH . $logoData['filename'];
		}
	}
	return $logo;
}

function get_logo_path($logoId, $size) {
	$logoPath = "";
	if (isset($logoId) && !empty($logoId) && is_numeric($logoId)) {
		$logoData = get_logo_from_db((int)$logoId);
		if ($logoData) {
			$logoPath = LOGOS_DIRECTORY . $logoData['filename'];
		}
	}
	return $logoPath;
}

function get_logo_from_db($logoId) {
	static $cache = [];
	
	if (isset($cache[$logoId])) {
		return $cache[$logoId];
	}
	
	$db = getDbInstance();
	$db->where('id', $logoId);
	$db->where('state', 'enable');
	$result = $db->getOne('logos');
	
	$cache[$logoId] = $result;
	return $result;
}

function get_logo_name_by_id($logoId) {
	if (empty($logoId) || !is_numeric($logoId)) {
		return '';
	}
	$logoData = get_logo_from_db((int)$logoId);
	return $logoData ? $logoData['name'] : '';
}

function get_country_code_from_number($number, $codeLength) {
	return substr($number, 0, $codeLength);
}

function get_address_web_card($data) {
	$location_keys = ["post_code", "address", "city", "state", "country"];
	$address = "";
	foreach($location_keys as $key){
		$value = read_key_array($data, $key, "");
		if(!empty($value)){
			if(!empty($address)){
				$address .= ", ";
			}
			$address .= $value;
		}
	}
	return $address;
}
