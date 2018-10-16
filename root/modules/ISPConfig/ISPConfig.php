<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 10:23
 */


require_once __DIR__ . '/ISPConfig.php';
require_once __DIR__ . '/classes/ISPConfig/SoapClient.php';
require_once('data/SugarBean.php');

class ISPConfig extends SugarBean
{


    public function getAllPHP($type = "")
    {
        $php = self::getPHP();
        $isp_config_id = array_get_default($_REQUEST, 'record', '');
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $server_id = $ispconfig->getField("server_id");
                $ispcServer = ListQuery::quick_fetch('ISPConfigServer', $server_id);
                if ($ispcServer != null) {
                    $type = $ispcServer->getField("server_type");
                }
            }
        }
        if ($type)
            return $php[$type];

        foreach ($php as $types) {
            foreach ($types as $type => $name) {
                $allphp[$type] = $name;
            }
        }
        return $allphp;
    }

    public function getPHP($type = "")
    {
        $return = array();
        $return["nginx"]["no"] = "Disabled";
        $return["nginx"]["php-fpm"] = "PHP-FPM";
        $return["nginx"]["hhvm"] = "HHVM";

        $return["nginx"]["no"] = "Disabled";
        $return["apache"]["php-fpm"] = "PHP-FPM";
        $return["apache"]["hhvm"] = "HHVM";
        $return["apache"]["fast-cgi"] = "Fast-CG";
        $return["apache"]["cgi"] = "CGI";
        $return["apache"]["mod"] = "Mod-PHP";
        $return["apache"]["suphp"] = "SuPHP";
        if ($type)
            return $return[$type];
        return $return;
    }

    public function getServerList()
    {
        $ispcServer = ListQuery::quick_fetch_all('ISPConfigServer');

        if ($ispcServer->total_count == 0) {
            self::syncServerList();
            $ispcServer = ListQuery::quick_fetch_all('ISPConfigServer');
        }
        $allphp = self::getPHP();
        foreach ($ispcServer->getRows() as $row) {
            $server[$row['server_id']]['name'] = $row["server_name"];
            $server[$row['server_id']]['type'] = $row["server_type"];
            $server[$row['server_id']]['php'] = array_merge(array("no" => "Disabled"), $allphp[$row["server_type"]]);
        }
        asort($server);
        return $server;

    }


    public function getIPList()
    {

        $isp_config_id = array_get_default($_REQUEST, 'record', '');
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $server_id = $ispconfig->getField("server_id");
            }
        }

        $ips["*"] = "*";
        $ispcServer = ListQuery::quick_fetch_all('ISPConfigServer');

        if ($ispcServer->total_count == 0) {
            self::syncServerList();
            $ispcServer = ListQuery::quick_fetch_all('ISPConfigServer');
        }
        foreach ($ispcServer->getRows() as $row) {
            if ((!$server_id || $server_id == $row["server_id"]) && $row['server_ip'] != "127.0.1.1" && $row['server_ip'] != "127.0.0.1") {
                $ips[$row['server_ip']]['name'] = $row["server_ip"];
                $ips[$row['server_ip']]['server_id'] = $row["server_id"];
            }
        }

        $serverIPs = ListQuery::quick_fetch_all('ISPConfigServerIPs');

        if ($serverIPs->total_count == 0) {
            self::syncServerList();
            $serverIPs = ListQuery::quick_fetch_all('ISPConfigServerIPs');
        }


        foreach ($serverIPs->getRows() as $row) {
            if ((!$server_id || $server_id == $row["server_id"]) && $row['server_ip'] != "127.0.1.1" && $row['server_ip'] != "127.0.0.1") {
                $ips[$row['server_ip']]['name'] = $row["server_ip"];
                $ips[$row['server_ip']]['server_id'] = $row["server_id"];
            }
        }
        ksort($ips);
        return $ips;

    }

    public static function createZugangsdaten($isp_config_id, $type = array("ssh", "mysql"), $password = "")
    {
        global $mod_strings;
        if (!$password) {
            $password = ISPCUtilities::generateStrongPassword();
            $_REQUEST['password'] = $password;
        }

        if (is_array($type)) {
            foreach ($type as $t) {
                ISPConfig::createZugangsdaten($isp_config_id, $t, $password);
            }
        }
        if (!in_array($type, array("ssh", "mysql"))) {
            return;
        }
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch_row('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {

                $account_id = $ispconfig['account_id'];
                $service_subcontract_id = $ispconfig['service_contract_id'];
                if (!$service_subcontract_id) {
                    throw new Exception("Kein Subcontract hinterlegt");
                }
                $username = ISPConfig::getDefaultUsernames($isp_config_id);
                global $current_user;

                $userId = $current_user->id;

                $lq = new ListQuery('v4Zugangsdaten', array('id'));
//                $lq->addSimpleFilter('service_subcontract_id', $service_subcontract_id);
                $lq->addSimpleFilter('isp_config_id ', $isp_config_id);
                $lq->addSimpleFilter('type', $type);
                $lq->addSimpleFilter('username', $username[$type]['username']);
                $row = $lq->runQuerySingle();
                $result = $lq->fetchResultCount();
                if ($result > 0) {
//                    $id = $row->getField('id');
//                    $existingObject = ListQuery::quick_fetch('v4Zugangsdaten', $id);
//
//                    $encrypted = $existingObject->getField('password');
//                    $password = v4Zugangsdaten::decryptAES($encrypted, v4Zugangsdaten::getSalt());
//                    $_REQUEST['password'] = $password;
//
//                    $rowUpdate = RowUpdate::for_result($existingObject);
//                    $rowUpdate->set("isp_config_id", $isp_config_id);
//                    $rowUpdate->set("service_subcontract_id", $service_subcontract_id);
//                    $rowUpdate->save();
                    add_flash_message(sprintf($mod_strings['LBL_CREATE_ZUGANGSDATEN_EXIST'], $type, $username[$type]['username']));
                    return;
                }
                $rowUpdate = RowUpdate::blank_for_model('v4Zugangsdaten');
                $params = [
                    'assigned_user_id' => $userId,
                    'account_id' => $account_id,
                    'service_subcontract_id' => $service_subcontract_id,
                    'type' => $type,
                    'server' => $username[$type]['server'],
                    'username' => $username[$type]['username'],
                    'pw_form' => $password,
                    'isp_config_id' => $isp_config_id,
                    'isp_config_enabled' => "1",
                    'isp_config_isactive' => "1",
                ];
                if ($type == "mysql") {
                    $params['isp_config_db_enabled'] = 1;
                    $params['isp_config_db_name'] = $username[$type]['dbname'];
                    $params['isp_config_db_active'] = 1;
                }

                $rowUpdate->set($params);
                $rowUpdate->save();

            }
        }
    }

    public function getAccountFields()
    {
        return [
            'ispc_client_id',
        ];
    }

    public static function account_has_updates(RowUpdate $rowUpdate)
    {

        if (defined('inScheduler')) return false;


        if (!\AppConfig::setting('ispconfig.enabled')
            || !\AppConfig::setting('ispconfig.host')
            || !\AppConfig::setting('ispconfig.user')
            || !\AppConfig::setting('ispconfig.password')
        ) return false;


        $active = $rowUpdate->getField('ispc_client_isactive');
        $ispc_client_id = $rowUpdate->getField('ispc_client_id');
        $ispc_username = $rowUpdate->getField('ispc_username');
        $ispc_password = $rowUpdate->getField('ispc_password');
        if ($active && (!$ispc_client_id || !$ispc_username || !$ispc_password))
            return true;
        $changes = $rowUpdate->getChanges();
        if (key_exists('ispc_client_isactive', $changes)
            || key_exists('ispc_client_id', $changes)
            || key_exists('ispc_username', $changes)
            || key_exists('ispc_password', $changes)
        ) {
            if ($active)
                return true;
        }

        return false;

    }

    public static function getDefaultUsernames($isp_config_id)
    {
        if (!empty($isp_config_id)) {
            $ispconfig = ListQuery::quick_fetch_row('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $account = ListQuery::quick_fetch_row('Account', $ispconfig['account_id']);

//                $domainShort = preg_replace('~[._-]~', '', $ispconfig['domain_name']);
                $domainExpl = explode(".", $ispconfig['domain_name']);
                $domainShort = str_replace("-","",substr($domainExpl[0], 0, 10));


                $ssh_prefix = $account['ispc_username'];
                //$ssh_username = $ssh_prefix . "_" . $domainShort . "_shell";
                $ssh_username = $ssh_prefix . "_shell";

                $mysql_prefix = "c" . $account['ispc_client_id'];
                $mysql_username = $mysql_prefix . "_" . $domainShort;

                $serverlist = self::getServerList();
                $servername = $serverlist[$ispconfig['server_id']] ? $serverlist[$ispconfig['server_id']]['name'] : $ispconfig['domain_name'];
                $ssh_server = $ispconfig['domain_name'];
                $mysql_server = "https://myadmin." . $servername;
                $return['ssh']['prefix'] = $ssh_prefix;
                $return['ssh']['username'] = $ssh_username;
                $return['ssh']['server'] = $ssh_server;

                $return['mysql']['prefix'] = $mysql_prefix;
                $return['mysql']['username'] = $mysql_username;
                $return['mysql']['server'] = $mysql_server;
                $return['mysql']['dbprefix'] = $mysql_prefix;
                $return['mysql']['dbname'] = $mysql_username;

                return $return;
            }
        }

    }

    public function syncServerList()
    {

        try {

            $soapClient = new \v4\ISPConfig\SoapClient(
                AppConfig::setting('ispconfig.host'),
                AppConfig::setting('ispconfig.user'),
                AppConfig::setting('ispconfig.password')
            );


            $count = 0;


            $res = $soapClient->serverGetAll();
            if (!$res) {
                throw new Exception($soapClient->getLastException());
            }

            global $db;
            $query = "DELETE FROM ispconfig_server_ips";
            $db->query($query, true);
            $query = "DELETE FROM ispconfig_server";
            $db->query($query, true);
            foreach ($res as $ispcServer) {

                $server_id = $ispcServer["server_id"];
                $serverips = $soapClient->serverIpGet(array("server_id" => $server_id));
                foreach ($serverips as $ipdata) {

                    $existingObject = ListQuery::quick_fetch_key('ISPConfigServerIPs', 'server_ip_id', $ipdata['server_ip_id']);
                    if ($existingObject == null) {
                        $rowUpdate = RowUpdate::blank_for_model('ISPConfigServerIPs');
                    } else {
                        $rowUpdate = RowUpdate::for_result($existingObject);
                    }
                    $rowUpdate->set(
                        array(
                            'server_ip_id' => $ipdata['server_ip_id'],
                            'server_id' => $ipdata['server_id'],
                            'server_ip' => $ipdata['ip_address'],

                        )
                    );
                    $rowUpdate->save();

                }

                $serverDetails = $soapClient->serverGet($server_id);
                $server[$server_id] = $ispcServer["server_name"] . " (" . $serverDetails['server']['hostname'] . ")";

                $existingObject = ListQuery::quick_fetch_key('ISPConfigServer', 'server_id', $server_id);

                if ($existingObject == null) {
                    $rowUpdate = RowUpdate::blank_for_model('ISPConfigServer');
                } else {
                    $rowUpdate = RowUpdate::for_result($existingObject);
                }

                $soapClient->serverGet($server_id);
                $server_type = $serverDetails['web']['server_type'];


                $rowUpdate->set(
                    array(
                        'server_id' => $ispcServer["server_id"],
                        'server_ip' => $serverDetails['server']['ip_address'],
                        'server_name' => $ispcServer["server_name"],
                        'server_hostname' => $serverDetails['server']['hostname'],
                        'server_type' => $server_type,

                    )
                );
                if (!$rowUpdate->validate()) {
                    add_flash_message($rowUpdate->getErrors(true));
                } else {
                    $rowUpdate->save();
                    $count++;
                }
            }

            add_flash_message(sprintf(translate('ISPCONFIG_SERVER_SYNC_SUCCESS', 'ISPConfig'), $count), 'info');
        } catch (\Exception $e) {
            add_flash_message("Get Remote Server Error: " . $e->getMessage(), 'info');
        }

        return $server;
    }

    public function getCRMWebsite($isp_config_id)
    {
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch_row('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                return $ispconfig;
            }
        }
    }

    public function syncWebsite($isp_config_id)
    {
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $domain_id = $ispconfig->getField("domain_id");
                if ($domain_id) {
                    try {
                        $soapClient = new \v4\ISPConfig\SoapClient(
                            AppConfig::setting('ispconfig.host'),
                            AppConfig::setting('ispconfig.user'),
                            AppConfig::setting('ispconfig.password')
                        );

                        $webdomain = $soapClient->sitesWebDomainGet($domain_id);

                        if ($webdomain) {
                            $params = [
                                "hd_quota" => $webdomain['hd_quota'],
                                "traffic_quota" => $webdomain['traffic_quota'],
                                "active" => $webdomain['active'] == "y" ? 1 : 0,
                                "domain" => $webdomain['domain'],
                                "ip_address" => $webdomain['ip_address'],
                                'php' => $webdomain['php'],
                                'php_version' => $webdomain['fastcgi_php_version'],
                                'backup_interval' => $webdomain['backup_interval'],
                                'backup_copies' => $webdomain['backup_copies'] - 1,
                                'backup_excludes' => $webdomain['backup_excludes'],
                                'lets_encrypt' => $webdomain['ssl_letsencrypt'] == "y" ? 1 : 0,
                                'apache_directives' => $webdomain['nginx_directives'] ? $webdomain['nginx_directives'] : $webdomain['apache_directives'],
                            ];
                            $existingObject = ListQuery::quick_fetch_key('ISPConfig', 'domain_id', $domain_id);
                            if ($existingObject != null) {
                                $ispUpdate = RowUpdate::for_result($existingObject);
                            }
                            $ispUpdate->set($params);

                            if (!$ispUpdate->validate()) {
                                $errors[] = ($ispUpdate->getErrors(true));
                            } else {
                                $ispUpdate->save();
                            }
                        }
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    if (sizeof($errors)) {
                        throw new Exception(implode(",", $errors));
                    }
                }
            }
        }
    }

    public function syncCronjobs($isp_config_id)
    {
        if ($isp_config_id) {
            $ispconfig = ListQuery::quick_fetch('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $domain_id = $ispconfig->getField("domain_id");
                if ($domain_id) {
                    try {
                        $soapClient = new \v4\ISPConfig\SoapClient(
                            AppConfig::setting('ispconfig.host'),
                            AppConfig::setting('ispconfig.user'),
                            AppConfig::setting('ispconfig.password')
                        );

                        $webdomain = $soapClient->sitesWebDomainGet($domain_id);

                        if ($webdomain) {

                            $cronjobs = $soapClient->sitesCronGet(array("parent_domain_id" => $domain_id));
                            $cronIDs = [];
                            if (sizeof($cronjobs)) {

                                foreach ($cronjobs as $cronjob) {
                                    $existingObject = ListQuery::quick_fetch_key('ISPConfigCron', 'cron_id', $cronjob['id']);
                                    if ($existingObject == null) {
                                        $cronUpdate = RowUpdate::blank_for_model('ISPConfigCron');
                                        global $current_user;
                                        $userId = $current_user->id;
                                    } else {
                                        $cronUpdate = RowUpdate::for_result($existingObject);
                                        $userId = $cronUpdate->getField('assigned_user_id');

                                    }
                                    $cronUpdate->set(
                                        array(
                                            'assigned_user_id' => $userId,
                                            'isp_config_id' => $isp_config_id,
                                            'domain_id' => $domain_id,
                                            'cron_id' => $cronjob['id'],
                                            'server_id' => $cronjob['server_id'],
                                            'run_min' => $cronjob['run_min'],
                                            'run_hour' => $cronjob['run_hour'],
                                            'run_mday' => $cronjob['run_mday'],
                                            'run_month' => $cronjob['run_month'],
                                            'run_wday' => $cronjob['run_wday'],
                                            'command' => $cronjob['command'],
                                            'active' => $cronjob['active'] == "y" ? 1 : 0,
                                            'log' => $cronjob['log'] == "y" ? 1 : 0,
                                        )
                                    );
                                    if ($cronUpdate->validate()) {
                                        $cronUpdate->save();
                                        $cronIDs[] = $cronjob['id'];
                                    } else {
                                        $errors[] = ($cronUpdate->getErrors(true));
                                    }
                                }
                            }
                            $lq = new ListQuery('ISPConfigCron');
                            $lq->addSimpleFilter('isp_config_id', $isp_config_id);
                            $result = $lq->fetchAll();
                            if ($result != null) {
                                foreach ($result->getRows() as $row) {
                                    $tmp_cron_id = $row['cron_id'];
                                    if (!in_array($row['cron_id'], $cronIDs)) {
                                        $existingCron = ListQuery::quick_fetch_key('ISPConfigCron', 'id', $row['id']);
                                        $cron_update = RowUpdate::for_result($existingCron);
                                        $cron_update->markDeleted();
                                    }
                                }

                            }

                        }
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    if (sizeof($errors)) {
                        throw new Exception(implode(",", $errors));
                    }
                }
            }
        }
    }

    public function syncAccountWebsites($account_id, $clientId = "", $newonly = false)
    {


        if (!$clientId) {
            $account = ListQuery::quick_fetch('Account', $account_id);
            $clientId = $account->getField("ispc_client_id");
        }

        if ($clientId) {

            try {
                $soapClient = new \v4\ISPConfig\SoapClient(
                    AppConfig::setting('ispconfig.host'),
                    AppConfig::setting('ispconfig.user'),
                    AppConfig::setting('ispconfig.password')
                );

                $sys_groupid = $soapClient->clientGetGroupid($clientId);
                $websites = $soapClient->sitesWebDomainGet(array("sys_groupid" => $sys_groupid, "parent_domain_id" => 0));
            } catch (Exception $e) {
                throw new IAHActionAbort($e->getMessage());
            }
            if (sizeof($websites)) {
                $count = 0;
                $domains = [];
                $newdomains = [];
                foreach ($websites as $website) {

                    if (!$website['domain'])
                        continue;

                    $existingObject = ListQuery::quick_fetch_key('ISPConfig', 'domain_id', $website['domain_id']);
                    if ($existingObject == null) {
                        $ispUpdate = RowUpdate::blank_for_model('ISPConfig');
                        global $current_user;
                        $userId = $current_user->id;

                    } else {
                        $ispUpdate = RowUpdate::for_result($existingObject);
                        $userId = $ispUpdate->getField('assigned_user_id');
                    }


                    $ispUpdate->set(
                        array(
                            'assigned_user_id' => $userId,
                            'domain_id' => $website['domain_id'],
                            'server_id' => $website['server_id'],
                            'name' => $website['domain'],
                            'domain_name' => $website['domain'],
                            'document_root' => $website['document_root'],
                            'ip_address' => $website['ip_address'],
                            'account_id' => $account_id,
                            'active' => ($website['active'] == "y" ? 1 : 0),
                            'php' => $website['php'],
                            'php_version' => $website['fastcgi_php_version'],
                            'backup_interval' => $website['backup_interval'],
                            'backup_copies' => ($website['backup_copies'] - 1),
                            'backup_excludes' => $website['backup_excludes'],
                            'lets_encrypt' => $website['ssl_letsencrypt'] == "y" ? 1 : 0,
                            'ssl' => $website['ssl'] == "y" ? 1 : 0,
                            'apache_directives' => $website['nginx_directives'] ? $website['nginx_directives'] : $website['apache_directives']
                        )
                    );
                    if (!$ispUpdate->validate()) {
                        $errors[] = ($ispUpdate->getErrors(true));
                    } else {
                        $ispUpdate->save();
                        $domains = $website['domain'];
                        if ($existingObject == null) {
                            $count++;
                            $newdomains[] = $website['domain'];
                        }

                    }
                }
            }

        }

        $return = [
            "count" => $count,
            "newdomains" => $newdomains,
            "domains" => $domains,
        ];
        if (sizeof($errors))
            $return['errors'] = $errors;

        return $return;
    }


}