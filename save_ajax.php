<?php
/** @var \ORCA\OrcaCallList\OrcaCallList $module */
$username = USERID;
$page_state = json_decode(file_get_contents("php://input"),true);
$success = $module->saveDataWithName($page_state, 'page_state', true);