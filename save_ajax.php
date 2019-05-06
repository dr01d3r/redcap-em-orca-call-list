<?php
/** @var \ORCA\OrcaCallList\OrcaCallList $module */
$username = USERID;
$call_list_selections = json_decode(file_get_contents("php://input"),true);
$message = "call_list_selections_save";
$parameters = $call_list_selections["checkboxSelections"] + $call_list_selections["dropdownSelections"];
try {
    $logId = $module->saveLogs($message, $parameters);
    $module->removeLogs("username = '$username' and log_id != $logId");
}
catch (Exception $ex) {
    $result = $ex->getMessage();
}