<?php

require_once '../env.php';
require_once 'config.php';
require_once 'database.php';
require_once 'model.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
header('Content-Type: application/json');

if ($global_config["environment"] == "development" || $global_config["environment"] == "local") {
    ini_set("display_errors", 1);
}

global $main_dbconn;

$result = new stdClass();

$authorization = isset($authorization) && !empty($authorization) ? $authorization : get_header("Authorization");
$cokkiee = '';
if (isset($_COOKIE["accessauth"])) {
    $cokkiee = $_COOKIE["accessauth"];
}
$access_token = (isset($authorization) && !empty($authorization)) ? $authorization : $cokkiee;


if ((!isset($access_token) || empty($access_token)) && !isset($no_session_check)) {
    invalid_session();
    die();
}

$game_db = new GameDB($main_dbconn, $global_config);
global $game_db;

/* Check if cookie is valid */
if (!empty($access_token) && !isset($no_session_check)) {
    $where = array();
    $data = array("data");
    $where["token"] = $access_token;
    $res = $game_db->get_token($data, $where);
    $session_data = json_decode($res[0]["data"], TRUE);
    if (!$session_data) {
        invalid_session();
        die();
    }
} else {
    if (!isset($no_session_check)) {
        invalid_session();
        die();
    }
}

if (!isset($no_session_check) && !empty($session_data)) {
    $session_user_id = isset($session_data['user_id']) ? $session_data['user_id'] : '';
    $session_user_name = isset($session_data['user_name']) ? $session_data['user_name'] : '';
}

if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                /* do some nasty string manipulations to restore the original letter case
                this should work in most cases */
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return ($arh);
    }
}

function set_output($result)
{
    $response = json_encode($result);
    echo $response;
}


function get_all_headers()
{
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}
function get_header($pHeaderKey)
{
    if (!function_exists('getallheaders')) {
        $test = get_all_headers();
    } else {
        $test = getallheaders();
    }
    if (array_key_exists($pHeaderKey, $test)) {
        $headerValue = $test[$pHeaderKey];
        return $headerValue;
    }
    return false;
}



function invalid_session()
{
    if (isset($_COOKIE["accessauth"])) {
        if (isset($_SESSION['id']))
            unset($_SESSION['id']);
        if (isset($_COOKIE['accessauth']))
            unset($_COOKIE['accessauth']);
        if (isset($_COOKIE['url']))
            unset($_COOKIE['url']);
        setcookie('accessauth', null, -1, '/');
        setcookie('url', null, -1, '/');
    }

    $result = new stdClass();
    $result->status = 401;
    $result->message = "Access denied";
    set_output($result);
    return;
}
