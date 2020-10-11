<?php
require_once 'database.inc.php';

defined('USER') ? '' : define('USER', 'user');
defined('GAMES_LIST') ? '' : define('GAMES_LIST', 'games_list');
defined('MATCH_LOG') ? '' : define('MATCH_LOG', 'match_log');
defined('TOKEN') ? '' : define('TOKEN', 'token');


$main_dbconn = new Database(
    $env_details['db']['host'],
    $env_details['db']['uname'],
    $env_details['db']['pass'],
    $env_details['db']['name'],
    $env_details['db']['port']
);
