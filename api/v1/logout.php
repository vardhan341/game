<?php
require_once 'common.php';

$where = [];
$where['token'] = $access_token;
$delete_auth = $game_db->delete_auth_data($where);
if ($delete_auth == false) {
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}

$result->status = 200;
$result->message = 'succsess';
set_output($result);
return;
