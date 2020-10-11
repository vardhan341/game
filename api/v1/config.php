<?php

$global_config["root"]["server"] = $env_details["server"];
$global_config["root"]["site_root"] = $env_details["site_root"];
$global_config["environment"] = $env_details["environment"];
$global_config["root"]["api_url"] = $global_config["root"]["server"] . "api/v1/";
$global_config["root"]["api_path"] = $global_config["root"]["site_root"] . "api/v1/";
$global_config["default_avatar"] = "https://cdn3.iconfinder.com/data/icons/gaming-glyph/32/gaming-esport-game-playing_16-512.png";
$global_config["plugins_path"] = $global_config["root"]["site_root"] . "plugins/";
$global_config["error_files_path"] = $global_config["root"]["site_root"] . "error_files/";
$global_config["error_files_url"] = $global_config["root"]["server"] . "error_files/";
$global_config["file_upload_path"] = $global_config["root"]["site_root"] . "uploads/";
$global_config["file_upload_url"] = $global_config["root"]["server"] . "uploads/";
