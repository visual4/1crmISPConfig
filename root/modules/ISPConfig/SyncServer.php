<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once 'ISPConfig.php';
global $mod_strings;



try {
    ISPConfig::syncServerList();
} catch (Exception $e) {
    add_flash_message("Sync Server Error: ".$e->getMessage(), 'error');
}


return array('perform', array('module' => 'Configurator', 'action' => 'EditView', 'layout' => "ISPConfig"));

