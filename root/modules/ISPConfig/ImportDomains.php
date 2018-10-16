<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 10:38
 */

require_once 'classes/ispcClient.php';

$client = new ispcClient();

$client->login();

$liste = $client->listSites();



foreach($liste as $key => $webdomain){
    $existingObject = ListQuery::quick_fetch_key('ISPConfig', 'domain_id', $webdomain['domain_id']);
    if ($existingObject == null){
        $rowUpdate = RowUpdate::blank_for_model('ISPConfig');
    }else{
        $rowUpdate = RowUpdate::for_result($existingObject);
    }
    $userId = $rowUpdate->getField('assigned_user_id');
    if (!$userId) $userId = 1;

    $domain = $client->getDomainDetails($webdomain['domain_id']);


    $rowUpdate->set(
        array(
            'document_root' => $webdomain['document_root'],
            'domain_id' => $webdomain['domain_id'],
            'name' => $webdomain['domain'],
            'active' => $webdomain['active'] == 'y' ? 1 : 0,
            'assigned_user_id' => $userId,
            'client_id' => $domain['sys_groupid'],
            'ip_address' => $domain['ip_address'],
            'server_id' => $domain['server_id'],
            'hd_quota' => $domain['hd_quota'],
            'ip_address' => $domain['ip_address'],
            'traffic_quota' => $domain['traffic_quota'],
        )
    );
    if (!$rowUpdate->validate()){
        add_flash_message($rowUpdate->getErrors(true));
    }else{
        $rowUpdate->save();

    }
}