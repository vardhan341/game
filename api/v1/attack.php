<?php

require_once 'common.php';

$json_data = json_decode(file_get_contents('php://input'), true);

$request_type = isset($json_data['request_type']) ? $json_data['request_type'] : 'normal';
$match_id = isset($json_data['match_id']) ? $json_data['match_id'] : '';

if (empty($match_id) || empty($request_type)) {
    $result->status = 406;
    $result->message = "Invalid request";
    goto OUT;
}

$return_data = [];

$data = $where = array();
$where['status'] = 'in_progress';
$where['user_id'] = $session_user_id;
$where['match_id'] = $match_id;
$inprogress_match = $game_db->get_games_list($data, $where);
if ($inprogress_match == false) {
    $result->status = 404;
    $result->message = "Invalid request";
    goto OUT;
}

$commentatory = json_decode($inprogress_match[0]['commentatory'], TRUE);
$inprogress_match_data = json_decode($inprogress_match[0]['data'], TRUE);
$user_health = intval($inprogress_match_data['user_health']);
$monster_health = intval($inprogress_match_data['monster_health']);

$end_time = strtotime($inprogress_match[0]['ends_at']);
$current_time = strtotime(gmdate('Y-m-d H:i:s'));

if ($request_type == 'surrender') {
    $commentatory[] = 'You surrended the game!';
    $return_data = [];
    $return_data['user_health'] = 0;
    $return_data['monster_health'] = 0;
    $return_data['finish'] = true;
    $return_data['commentatory'] = $commentatory;
    $result->status = 200;
    $result->message = 'You Lost!';
    $result->data = $return_data;
    goto OUT;
}

if ($end_time < $current_time) {

    $commentatory[] = 'match ended due to time laps';
    $return_data = [];
    $return_data['user_health'] = 0;
    $return_data['monster_health'] = 0;
    $return_data['finish'] = true;
    $return_data['commentatory'] = $commentatory;

    $result->status = 208;
    $result->message = 'Match ended due to time laps';
    $result->data = $return_data;
    goto OUT;
}


$user_attack = rand(1, 10);
$monster_attack = rand(1, 10);

if ($request_type == 'power') {
    $user_attack = rand(1, 10);
    $monster_attack = rand(1, $monster_health);

    $user_health = $user_health - $user_attack;
    $monster_health = $monster_health - $monster_attack;

    $commentatory[] = 'Power attack on monstar!';
    $commentatory[] = '[' . $session_user_name . '] Power attack the monstar by ' . $monster_attack;
    $commentatory[] = 'Monstar attack the [' . $session_user_name . '] by ' . $user_attack;
} else if ($request_type == 'healing') {
    $healing_health = 100 - $user_health;
    $user_health = $user_health + $healing_health - $user_attack;
    $monster_health = $monster_health - $monster_attack;

    $commentatory[] = 'Healing health of [' . $session_user_name . '] by ' . $healing_health;
    $commentatory[] = '[' . $session_user_name . '] attack the monstar by ' . $monster_attack;
    $commentatory[] = 'Monstar attack the [' . $session_user_name . '] by ' . $user_attack;
} else {
    $user_health = $user_health - $user_attack;
    $monster_health = $monster_health - $monster_attack;

    $commentatory[] = '[' . $session_user_name . '] Power attack the monstar by ' . $monster_attack;
    $commentatory[] = 'Monstar attack the [' . $session_user_name . '] by ' . $user_attack;
}

$user_health = $user_health <= 0 ? 0 : $user_health;
$monster_health = $monster_health <= 0 ? 0 : $monster_health;

$return_data = [];
$return_data['user_health'] = $user_health;
$return_data['monster_health'] = $monster_health;
$return_data['finish'] = false;

if ($monster_health <= 0 && $user_health <= 0) {
    $commentatory[] = 'Match tied!';
    $return_data['finish'] = true;
    $result->status = 200;
    $result->message = 'Match tied!';
    $result->data = $return_data;
    goto OUT;
}

if ($monster_health <= 0) {
    $commentatory[] = '[' . $session_user_name . '] Win!';
    $return_data['finish'] = true;
    $result->status = 200;
    $result->message = 'You Win!';
    $result->data = $return_data;
    goto OUT;
}

if ($user_health <= 0) {
    $commentatory[] = '[' . $session_user_name . '] Lost!';
    $return_data['finish'] = true;
    $result->status = 200;
    $result->message = 'You Lost!';
    $result->data = $return_data;
    goto OUT;
}

$result->status = 200;
$result->message = 'continue';
$result->data = $return_data;

OUT:

$start_transaction = $game_db->start_transaction();

if (isset($commentatory)) {
    $match_data = $data = $where = [];
    $data = [];
    $data['commentatory'] = json_encode($commentatory);
    if (isset($return_data['finish']) && $return_data['finish'] == true) {
        $data['status'] = 'finished';
    }
    if (isset($user_health) && isset($monster_health)) {
        $match_data['user_health'] = $user_health;
        $match_data['monster_health'] = $monster_health;
        $data['data'] = json_encode($match_data);
    }
    $data['updated_on'] = gmdate('Y-m-d H:i:s');
    $where['match_id'] = $match_id;
    $where['user_id'] = $session_user_id;
    $update_match = $game_db->update_games_list($data, $where);
    if ($update_match == false) {
        $rollback_transaction = $game_db->rollback_transaction();
        $result->status = 500;
        $result->message = 'Oops! something went wrong. Try again later-1';
        set_output($result);
        return;
    }
    $result->commentatory = $commentatory;
}

//logging

$match_data = $data = [];
$data['action'] = $request_type;
$data['match_id'] = $match_id;
$data['user_id'] = $session_user_id;
if (isset($user_health) && isset($monster_health)) {
    $match_data['user_health'] = $user_health;
    $match_data['monster_health'] = $monster_health;
    $data['data'] = json_encode($match_data);
}
$data['record_date'] = gmdate('Y-m-d H:i:s');
$add_log = $game_db->add_game_log($data);
if ($add_log == false) {
    $rollback_transaction = $game_db->rollback_transaction();
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later-2';
    set_output($result);
    return;
}

$commit_transaction = $game_db->commit_transaction();
if ($commit_transaction == false) {
    $rollback_transaction = $game_db->rollback_transaction();
    $result->status = 500;
    $result->message = 'Oops! something went wrong. Try again later-3';
    set_output($result);
    return;
}

set_output($result);
return;
