<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 10:00
 */

$record = array_get_default($_REQUEST, 'record', '');
$serviceContract = ListQuery::quick_fetch('Contract', $record);
if ($serviceContract == null) exit;

$account = ListQuery::quick_fetch('Account', $serviceContract->getField('account_id'));

require_once __DIR__ . '/classes/models/ISPCSoapClient.php';
require_once __DIR__ . '/classes/repositories/ClientRepository.php';

$client = new \v4\ispconfig\Client();

$id = $serviceContract->getField('ispconfig_id');
$client->setId($id)
    ->setCustomerNo($serviceContract->getField('contract_no'))
    ->setCompanyName($account->getField('name'))
    ->setContactName($account->getField('name'))
    ->setEmail($account->getField('email1'));

$soapClient = new \v4\ispconfig\ISPCSoapClient();
$soapClient->login(
    AppConfig::setting('ispconfig.user'),
    AppConfig::setting('ispconfig.password'),
    AppConfig::setting('ispconfig.host')
);
$repository = new \v4\ispconfig\ClientRepository($soapClient);

$newId = $repository->saveToISPC($client);
add_flash_message('ISPC Client saved');
if (empty($id) && $newId) {
    $rowUpdate = RowUpdate::for_result($serviceContract);
    $rowUpdate->set(array(
        'ispconfig_id' => $newId,
    ));
    If ($rowUpdate->validate()) {
        $rowUpdate->save();
    } else {
        add_flash_message($rowUpdate->getErrors(true));
    }
}
return array('perform', array('module' => 'Service', 'action' => 'DetailView', 'record' => $record, 'record_perform' => 'view'));