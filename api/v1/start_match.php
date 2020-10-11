<?php

require_once 'common.php';

$data = $where = array();
$where['status'] = 'in_progress';
$where['user_id'] = $session_user_id;
$inprogress_match = $game_db->get_games_list($data, $where);
if ($inprogress_match != false) {
    $end_time = strtotime($inprogress_match[0]['ends_at']);
    $current_time = strtotime(gmdate('Y-m-d H:i:s'));
    if ($end_time > $current_time) {
        $result->status = 406;
        $result->message = "Match is in progress. You can't start another match";
        set_output($result);
        return;
    } else {
        $commentatory = json_decode($inprogress_match[0]['commentatory'], TRUE);
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
}

$match_id = uniqid('MATCH');
$commentatory = $data = $where = [];
$commentatory = ['match began!'];
$data['match_id'] = $match_id;
$data['user_id'] = $session_user_id;
$data['status'] = 'in_progress';
$data['start_at'] = date("Y-m-d H:i:s", strtotime(gmdate('Y-m-d H:i:s')));
$data['ends_at'] = date("Y-m-d H:i:s", strtotime(gmdate('Y-m-d H:i:s')) + 60);
$data['record_date'] = gmdate('Y-m-d H:i:s');
$data['commentatory'] = json_encode($commentatory);
$data['data'] = json_encode(['user_health' => 100, 'monster_health' => 100]);
$add_match = $game_db->add_games_list($data);
if ($add_match == false) {
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later';
    set_output($result);
    return;
}

$return_data = [];
$return_data['user_health'] = 100;
$return_data['monster_health'] = 100;
$return_data['finish'] = false;
$return_data['match_id'] = $match_id;

$result->status = 200;
$result->message = 'Match Started!';
$result->data = $return_data;
$result->commentatory = $commentatory;
set_output($result);
return;
