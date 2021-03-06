<?php

namespace ORCA\OrcaCallList;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

require_once 'vendor/autoload.php';
require_once 'traits/REDCapUtils.php';
require_once 'traits/ModuleLogUtils.php';

class OrcaCallList extends AbstractExternalModule  {
    use REDCapUtils;
    use ModuleLogUtils;

    private static $smarty;
    const DEFAULT_EMPTY_PAGESTATE = ["call_list_selections" => [
        "dropdownSelections" => [],
        "checkboxSelections" => []
    ]];

    public function __construct()
    {
        parent::__construct();
        define("MODULE_DOCROOT", $this->getModulePath());
    }

    function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        $this->display_user_fullname();
    }

    public function redcap_module_link_check_display($project_id, $link) {
        return true;
    }

    function  display_user_fullname() {
        ?>
        <script type='text/javascript'>
            $(function() {
                let user_fullname_pattern = '[user_fullname]';
                let user_fullname = '<?=$this->getCurrentUserFullName();?>';

                if((user_fullname).length > 0) {
                    $('body :not(script)').contents()
                        .filter(function() {
                            return this.nodeType === 3;
                        })
                        .replaceWith(function() {
                            return this.nodeValue.replace(user_fullname_pattern, user_fullname);
                        });
                }
            });
        </script>
        <?php
    }

    public function initializeSmarty() {
        self::$smarty = new \Smarty();
        self::$smarty->setTemplateDir(MODULE_DOCROOT . 'templates');
        self::$smarty->setCompileDir(MODULE_DOCROOT . 'templates_c');
        self::$smarty->setConfigDir(MODULE_DOCROOT . 'configs');
        self::$smarty->setCacheDir(MODULE_DOCROOT . 'cache');
    }

    public function setTemplateVariable($key, $value) {
        self::$smarty->assign($key, $value);
    }

    public function displayTemplate($template) {
        self::$smarty->display($template);
    }

    public function getStoredPageState(){
        $pageStateParam = 'page_state';
        // get all the logs for this user.  in this case, there should only be 1 - the call list page state
        $module_logs = $this->getLogs([$pageStateParam], "username = '".USERID."'");
        $returnValue = [];

        function cmpIds($a, $b)
        {
            if(!is_numeric($a)) {
                return -1;
            } elseif(!is_numeric($b)) {
                return 1;
            }

            $a_id = intval($a);
            $b_id = intval($b);
            return $a_id - $b_id;
        }
        uksort($module_logs, "cmpIds");

        //for every log record
        $foundParam = false;
        foreach ($module_logs as $logID => $logRecord) {
            //for every parameter value in that log record
            foreach ($logRecord["parameters"] as $param => $val) {
                //if we have the param value we want in this record
                if($param === $pageStateParam){
                    $foundParam = true;
                    //combine existing fetched data and new data we just found
                    $returnValue = json_decode($val, true);
                }
            }

            if($foundParam) {
                break;
            }
        }

        $returnValue = empty($returnValue) ? OrcaCallList::DEFAULT_EMPTY_PAGESTATE : $returnValue;

        return $returnValue;
    }

    //saves a piece of data to the module logs, using a named parameter
    //optionally gives ability to remove all old log entries (when the module doesnt care about multiple log entries for a user)
    public function saveDataWithName($data, $name, $removeOldLogEntries = true) {
        try {
            $logId = $this->saveLogs('save module data - '.$name, [$name => json_encode($data)]);

            if($removeOldLogEntries){
                $this->removeLogs("username = '" . USERID . "' and log_id != $logId");
            }

            return true;
        }
        catch (Exception $ex) {
            $msg = $ex->getMessage();
            return false;
        }
    }

    //get data saved to logs, via a name/key
    public function getSavedDataByName($name){
        // get all the logs for this user.  in this case, there should only be 1 - the call list page state
        $module_logs = $this->getLogs([$name], "username = '".USERID."'");
        $returnValue = [];

        //sort log entries based on id, so we get the latest record in case there is multiple
        function cmpIds($a, $b) {
            if(!is_numeric($a)) {
                return is_numeric($b) ? -1 : 0;
            } elseif(!is_numeric($b)) {
                return 1;
            }

            $a_id = intval($a);
            $b_id = intval($b);
            return $a_id - $b_id;
        }
        uksort($module_logs, "cmpIds");

        //for every log record
        $foundParam = false;
        foreach ($module_logs as $logID => $logRecord) {
            //for every parameter value in that log record
            foreach ($logRecord["parameters"] as $param => $val) {
                //if we have the param value we want in this record
                if($param === $name){
                    $foundParam = true;
                    //save the returned data, as our search is over (as we have the latest due to the ordering by id)
                    $returnValue = json_decode($val, true);
                    break;
                }
            }

            if($foundParam) {
                break;
            }
        }

        $returnValue = empty($returnValue) ? [] : $returnValue;//ensure we always return an array if value is empty for some reason
        return $returnValue;
    }

    public function PreoutVarInfo($var, $title = '', $returnStringInstead = false) {
        ob_start();
        var_dump($var);
        if(!empty($title)) {
            $title .= ': ';
        }
        $str = $title.ob_get_clean();

        if($returnStringInstead === true) {
            return $str;
        }
        $this->Preout($str);
    }

    public function IsPostRequest() {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }
}