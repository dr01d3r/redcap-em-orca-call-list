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
}