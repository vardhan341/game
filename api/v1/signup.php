<?php
$no_session_check = true;
require_once 'common.php';

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$avatar = isset($_FILES['avatar']) ? $_FILES['avatar'] : [];

if (empty($user_name) || empty($password) || empty($email) || empty($name) //|| empty($avatar)
) {
    $result->status = 406;
    $result->message = 'All * mark fields are mandatory';
    set_output($result);
    return;
}

$user_details = $game_db->get_user([], []);
if ($user_details != false) {
    $user_names = array_column($user_details, 'user_name');
    $emails = array_column($user_details, 'email');
    if (in_array($user_name, $user_names)) {
        $result->status = 406;
        $result->message = 'UserName Already exists';
        set_output($result);
        return;
    }
    if (in_array($email, $emails)) {
        $result->status = 406;
        $result->message = 'Email Already exists';
        set_output($result);
        return;
    }
}

/* you can follow any of the above or commented code to check user already exists or not */
/*
$data = $where = array();
$where['user_name'] = $user_name;
$user_details = $game_db->get_user($data, $where);
if ($user_details != false) {
    $result->status = 406;
    $result->message = 'UserName Already exists';
    set_output($result);
    return;
}

$data = $where = array();
$where['email'] = $email;
$user_details = $game_db->get_user($data, $where);
if ($user_details != false) {
    $result->status = 406;
    $result->message = 'Email Already exists';
    set_output($result);
    return;
}
*/

$start_transaction = $game_db->start_transaction();
if ($start_transaction == false) {
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}

$user_id = uniqid('USER');
$return_data = $session_data = $data = [];

$session_data['user_id'] = $data['user_id'] = $user_id;
$return_data['user_name']=$session_data['user_name'] = $data['user_name'] = $user_name;
$session_data['email'] = $data['email'] = $email;
$data['password'] = md5(md5(md5($password)));
$session_data['name'] = $data['name'] = $name;
$data['record_date'] =  gmdate('Y-m-d H:i:s');
$add_user = $game_db->add_user($data);
if ($add_token = false) {
    $rollback_transaction = $game_db->rollback_transaction();
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}


$hash_string = $user_name . $user_id . $email . date("YmdHis") . uniqid();
$token = md5(md5(md5($hash_string)));

$data = [];
$data['token'] = $token;
$data['user_id'] = $user_id;
$data['record_date'] = gmdate('Y-m-d H:i:s');
$data['data'] = json_encode($session_data);
$add_token = $game_db->add_token($data);
if ($add_token = false) {
    $rollback_transaction = $game_db->rollback_transaction();
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}

$commit_transaction = $game_db->commit_transaction();
if ($commit_transaction == false) {
    $rollback_transaction = $game_db->rollback_transaction();
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
