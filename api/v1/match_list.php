<?php
require_once 'common.php';

$data = $where = array();
$data = ['match_id', 'status', 'data', 'commentatory', 'record_date'];
$where['user_id'] = $session_user_id;
$match_list = $game_db->get_games_list($data, $where);
if ($match_list == false) {
    $result->status = 208;
    $result->message = 'empty match list found';
    set_output($result);
    return;
}

$final_result = [];
$i = 0;
foreach ($match_list as $record) {
    $final_result[$i] = $record;
    $final_result[$i]['commentatory'] = json_decode($record['commentatory'], TRUE);
    $final_result[$i]['data'] = json_decode($record['data'], TRUE);
    $i++;
}

$result->status = 200;
$result->match_data = $final_result;
set_output($result);
return;
