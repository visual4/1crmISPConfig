<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 10:38
 */

require_once __DIR__ . '/ISPConfig.php';

require_once __DIR__ . '/classes/ISPConfig/SoapClient.php';
require_once __DIR__ . '/classes/ISPCUtilities.php';

$record = array_get_default($_REQUEST, 'record', '');
try {

    ISPConfig::syncWebsite($record);

} catch (Exception $e) {
    add_flash_message($e->getMessage());
}

return array('perform', array('module' => 'ISPConfig', 'action' => 'DetailView', 'record' => $record, 'record_perform' => 'view'));