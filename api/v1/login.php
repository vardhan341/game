<?php
$no_session_check = true;
require_once 'common.php';

$json_data = json_decode(file_get_contents('php://input'), true);

$user_name = isset($json_data['user_name']) ? $json_data['user_name'] : '';
$password = isset($json_data['password']) ? $json_data['password'] : '';

if (empty($user_name) || empty($password)) {
    $result->status = 406;
    $result->message = 'All * mark fields are mandatory';
    set_output($result);
    return;
}

$data = $where = array();
$where['user_name'] = $user_name;
$where['password'] = md5(md5(md5($password)));
$user_details = $game_db->get_user($data, $where);
if ($user_details == false) {
    $result->status = 404;
    $result->message = 'Invalid username/password. Please try again';
    set_output($result);
    return;
}

$return_data = $session_data = [];
$session_data['user_id'] = $user_details[0]['user_id'];
$return_data['user_name'] = $session_data['user_name'] = $user_details[0]['user_name'];
$session_data['email'] = $user_details[0]['email'];
$session_data['name'] = $user_details[0]['name'];

$hash_string = $user_details[0]['user_name'] . $user_details[0]['user_id'] . $user_details[0]['email'] . date("YmdHis") . uniqid();
$token = md5(md5(md5($hash_string)));

$data = [];
$data['token'] = $token;
$data['user_id'] = $user_details[0]['user_id'];
$data['record_date'] = gmdate('Y-m-d H:i:s');
$data['data'] = json_encode($session_data);
$add_token = $game_db->add_token($data);
if ($add_token = false) {
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}

$result->status = 200;
$result->token = $token;
$result->data = $return_data;
set_output($result);
return;
