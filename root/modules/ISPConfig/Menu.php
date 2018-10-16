<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


global $mod_strings, $app_strings;

if(ACLController::checkAccess('ISPConfig', 'create', true) || ACLController::checkAccess('ISPConfigCron', 'create', true))$module_menu[]=Array("index.php?module=ISPConfig&action=EditView&return_module=ISPConfig&return_action=DetailView", $mod_strings['LNK_ISPC_NEW_SITE'],"CreateAccount", 'ISPConfig');
if(ACLController::checkAccess('ISPConfig', 'list', true)|| ACLController::checkAccess('ISPConfigCron', 'create', true))$module_menu[]=Array("index.php?module=ISPConfig&action=index&return_module=ISPConfig&return_action=DetailView", $mod_strings['LNK_ISPC_LIST_SITES'],"Account", 'ISPConfig');
if(ACLController::checkAccess('ISPConfigCron', 'create', true) || ACLController::checkAccess('ISPConfig', 'list', true)) $module_menu[]=Array("index.php?module=ISPConfigCron&action=EditView&return_module=ISPConfigCron&return_action=DetailView", $mod_strings['LNK_ISPC_NEW_CRON'],"CreateAccount", 'ISPConfigCron');
if(ACLController::checkAccess('ISPConfigCron', 'list', true) || ACLController::checkAccess('ISPConfig', 'list', true)) $module_menu[]=Array("index.php?module=ISPConfigCron&action=index&return_module=ISPConfigCron&return_action=DetailView", $mod_strings['LNK_ISPC_LIST_CRONS'],"Account", 'ISPConfigCron');

?>

