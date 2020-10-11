<?php

require_once 'common.php';

$json_data = json_decode(file_get_contents('php://input'), true);

$match_id = isset($json_data['match_id']) ? $json_data['match_id'] : '';

if (empty($match_id)) {
    $result->status = 406;
    $result->message = "Invalid Request";
    set_output($result);
    return;
}

$data = $where = array();
$data = ['match_id', 'status', 'ends_at', 'data', 'commentatory', 'record_date'];
$where['match_id'] = $match_id;
$where['user_id'] = $session_user_id;
$inprogress_match = $game_db->get_games_list($data, $where);
if ($inprogress_match == false) {
    $result->status = 404;
    $result->message = "Match not found";
    set_output($result);
    return;
}

$inprogress_match_data = json_decode($inprogress_match[0]['data'], TRUE);

$commentatory = $return_data = [];
$return_data['user_health'] = isset($inprogress_match_data['user_health']) ? $inprogress_match_data['user_health'] : 100;
$return_data['monster_health'] = isset($inprogress_match_data['monster_health']) ? $inprogress_match_data['monster_health'] : 100;
$return_data['finish'] = false;

$commentatory = json_decode($inprogress_match[0]['commentatory'], TRUE);

$end_time = strtotime($inprogress_match[0]['ends_at']);
$current_time = strtotime(gmdate('Y-m-d H:i:s'));
if ($end_time > $current_time) {
    $result->status = 200;
    $result->message = "Match is in progress.";
    $result->data = $return_data;
    $result->commentatory = $commentatory;
    set_output($result);
    return;
} else {
    $commentatory[] = 'match ended due to time laps';
    $data = [];
    $data['commentatory'] = json_encode($commentatory);
    $data['updated_on'] = gmdate('Y-m-d H:i:s');
    $data['status'] = 'finished';
    $where['match_id'] = $inprogress_match[0]['match_id'];
    $update_match = $game_db->update_games_list($data, $where);
    if ($update_match == false) {
        $result->status = 500;
        $result->message = 'Oops! something went wrong. Try again later';
        set_output($result);
        return;
    }
}

$result->status = 208;
set_output($result);
return;
