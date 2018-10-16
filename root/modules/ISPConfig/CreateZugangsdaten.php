<?php
/**
 * Created by PhpStorm.
 * User: thomas_stauch
 * Date: 10.10.2017
 * Time: 14:22
 */

require_once __DIR__ . '/ISPConfig.php';

require_once __DIR__ . '/classes/ISPConfig/SoapClient.php';
require_once __DIR__ . '/classes/ISPCUtilities.php';
require_once 'modules/v4Zugangsdaten/v4Zugangsdaten.php';

$record = array_get_default($_REQUEST, 'record', '');
try {

    ISPConfig::createZugangsdaten($record);

} catch (Exception $e) {
    add_flash_message($e->getMessage());
}

return array('perform', array('module' => 'ISPConfig', 'action' => 'DetailView', 'record' => $record, 'record_perform' => 'view'));