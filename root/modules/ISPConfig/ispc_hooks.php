<?php

/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 16:03
 */


require_once __DIR__ . '/ISPConfig.php';
require_once __DIR__ . '/classes/ISPConfig/SoapClient.php';
require_once __DIR__ . '/classes/ISPCUtilities.php';

class ispc_hooks
{


    static function before_save(RowUpdate $rowUpdate)
    {
        global $mod_strings;

        if (!\AppConfig::setting('ispconfig.enabled')
            || !\AppConfig::setting('ispconfig.host')
            || !\AppConfig::setting('ispconfig.user')
            || !\AppConfig::setting('ispconfig.password')
        ) return;

        if ($rowUpdate->source == null) {
            return;
        }
        $changes = $rowUpdate->getChanges();
        $domain_name = $rowUpdate->getField('domain_name');
        $domain_name = ISPCUtilities::clean_domain_name($domain_name);

        $valid = ISPCUtilities::is_valid_domain_name($domain_name);

        if (!$valid) {
            throw new IAHActionAbort(sprintf($mod_strings['MSG_DOMAIN_INVALID'], $domain_name));
        }
        $server_id = $rowUpdate->getField('server_id');

        $rowUpdate->set(array(
            'domain_name' => $domain_name
        ));


        $account_id = $rowUpdate->getField("account_id");
        $contract_id = $rowUpdate->getField("service_contract_id");

        $domain_id = key_exists("domain_id", $changes) ? $changes['domain_id'] : $rowUpdate->getField("domain_id");
        $ip_address = key_exists("ip_address", $changes) ? $changes['ip_address'] : $rowUpdate->getField("ip_address");
        $hd_quota = $rowUpdate->getField("hd_quota");
        if ($hd_quota == null) {
            $hd_quota = "-1";
        }
        $traffic_quota = $rowUpdate->getField("traffic_quota");
        if ($traffic_quota == null) {
            $traffic_quota = "-1";
        }

        $php = $rowUpdate->getField("php");
        $php_version = $rowUpdate->getField("php_version");
        $backup_interval = $rowUpdate->getField("backup_interval");
        $backup_copies = $rowUpdate->getField("backup_copies");
        $backup_excludes = $rowUpdate->getField("backup_excludes");
        $lets_encrypt = $rowUpdate->getField("lets_encrypt") == 1 ? "y" : "n";
        $apache_directives = $rowUpdate->getField("apache_directives");
        $active = $rowUpdate->getField("active") ? "y" : "n";

        $account = ListQuery::quick_fetch('Account', $account_id);
        $clientId = $account->getField("ispc_client_id");

        try {

            if (!$clientId) {
                throw new Exception("ISPConfig - Kunde ist nicht angelegt");
            }


            $soapClient = new \v4\ISPConfig\SoapClient(
                \AppConfig::setting('ispconfig.host'),
                \AppConfig::setting('ispconfig.user'),
                \AppConfig::setting('ispconfig.password')
            );

            $ispc_client = $soapClient->clientGet($clientId);
            if (!$ispc_client) {
                throw new Exception("ISPConfig - ClientID $clientId nicht gefunden");
            }
            $sys_groupid = $soapClient->clientGetGroupid($clientId);

            if ($domain_id)
                $webdomain = $soapClient->sitesWebDomainGet($domain_id);

            if ($webdomain) {
                $params = [
                    "hd_quota" => $hd_quota,
                    "traffic_quota" => $traffic_quota,
                    "active" => $active,
                    "domain" => $domain_name,
                    "ip_address" => $ip_address,
                    'php' => $php,
//                    'fastcgi_php_version' => $php_version,
                    'backup_interval' => $backup_interval,
                    'backup_copies' => $backup_copies + 1,
                    'backup_excludes' => $backup_excludes,
                    'ssl_letsencrypt' => $lets_encrypt,
                    'apache_directives' => $apache_directives,
                    'nginx_directives' => $apache_directives,
                ];
                if ($lets_encrypt == "y") {
                    $params['ssl'] = "y";
                    $params['rewrite_to_https'] = "y";
                }

                $update = $soapClient->sitesWebDomainUpdate($clientId, $domain_id, $params);
                if ($update) {
                    add_flash_message("ISPConfig - Seite $domain_name erfolgreich aktualisiert", "info");
                }



            } else {
                $serverInfo = $soapClient->serverGet($server_id);
                $ip_address = $ip_address ? $ip_address : $serverInfo['server']['ip_address'];
                if (!$ip_address) {
                    $ip_address = "*";
                }
                $domain_id = $soapClient->sitesWebDomainAdd($clientId, $domain_name, $server_id, $ip_address, "www", $hd_quota, $traffic_quota, $php, $backup_interval, $backup_copies + 1, $backup_excludes, $lets_encrypt, $apache_directives);
                if (!$domain_id) {
                    $existingDomain = $soapClient->sitesWebDomainGet(array("domain" => $domain_name));
                    if (!empty($existingDomain) && $existingDomain[0]['domain_id']) {
                        if ($existingDomain[0]['system_group'] == "client" . $clientId) {
                            $domain_id = $existingDomain[0]['domain_id'];


                        } else {
                            throw new Exception("Die Website $domain_name ist einer anderen Firma zugeordnet.");
                        }
                    } else {
                        add_flash_message("ISPConfig Website Create Error: " . $soapClient->getLastException()->getMessage(), "error");
                        return;
                    }
                } else {
                    add_flash_message("ISPConfig - Seite erfolgreich angelegt", "info");
                }
            }

            $webdomain = $soapClient->sitesWebDomainGet($domain_id);
            $rowUpdate->set(array(
                'domain_id' => $domain_id,
                'document_root' => $webdomain['document_root'],
                'ip_address' => $webdomain['ip_address'],
                'active' => ($webdomain['active'] == "y" ? 1 : 0),
                'hd_quota' => $hd_quota,
                'traffic_quota' => $traffic_quota,
                'php' => $webdomain['php'],
                'php_version' => $webdomain['fastcgi_php_version'],
                'backup_interval' => $webdomain['backup_interval'],
                'backup_copies' => $webdomain['backup_copies'] - 1,
                'backup_excludes' => $webdomain['backup_excludes'],
                'lets_encrypt' => $webdomain['ssl_letsencrypt'] == "y" ? 1 : 0,
                'apache_directives' => $webdomain['apache_directives'],
            ));



        } catch (Exception $e) {
            throw new IAHActionAbort($e->getMessage());
        }
    }



    static function after_save(RowUpdate $rowUpdate) {
        if (!\AppConfig::setting('ispconfig.enabled')
            || !\AppConfig::setting('ispconfig.host')
            || !\AppConfig::setting('ispconfig.user')
            || !\AppConfig::setting('ispconfig.password')
        ) return;

        try {
            $isp_config_id = $rowUpdate->getField("id");
            $result = ISPConfig::syncCronjobs($isp_config_id);
        } catch (Exception $e) {
            add_flash_message($e->getMessage(),"error");
        }

    }
    static function zugangsdaten_before_save(RowUpdate $rowUpdate)
    {



        if (!\AppConfig::setting('ispconfig.enabled')
            || !\AppConfig::setting('ispconfig.host')
            || !\AppConfig::setting('ispconfig.user')
            || !\AppConfig::setting('ispconfig.password')
        ) return;

        $changes = $rowUpdate->getChanges();
        $isp_config_enabled = key_exists('isp_config_enabled', $changes) ? $changes["isp_config_enabled"] : $rowUpdate->getField("isp_config_enabled");
        $isp_config_id = key_exists('isp_config_id', $changes) ? $changes["isp_config_id"] : $rowUpdate->getField("isp_config_id");


        $type = key_exists('type', $changes) ? $changes["type"] : $rowUpdate->getField("type");

        $allowedTypes = array("ssh", "mysql");
        if ($isp_config_enabled && !in_array($type, $allowedTypes)) {
            $rowUpdate->set('isp_config_enabled', 0);
            return;
        }

        if (in_array($rowUpdate->orig_row["type"], $allowedTypes) && $rowUpdate->orig_row["type"] != $type) {
            throw new IAHActionAbort("ISPConfig - Type \"" . $rowUpdate->orig_row["type"] . "\" kann nicht mehr geändert werden");
        }
        if (!$isp_config_enabled || !$isp_config_id) {
            return;
        }


        try {
            $ispconfig = ListQuery::quick_fetch_row('ISPConfig', $isp_config_id);
            if ($ispconfig != null) {
                $account_id = $ispconfig['account_id'];
                $domain_id = $ispconfig['domain_id'];
                $server_id = $ispconfig['server_id'];
                $hd_quota = $ispconfig['hd_quota'] ? $ispconfig['hd_quota'] : "-1";
            }

            $account = ListQuery::quick_fetch_row('Account', $account_id);
            if ($account!= null) {
                $clientId = $account['ispc_client_id'];
                $ispc_client_isactive = $account['ispc_client_isactive'];
            }


            if (!$clientId) {
                throw new Exception("ISPConfig - Kunde ist nicht angelegt");
            }
            if (!$ispc_client_isactive) {
                throw new Exception("ISPConfig - Kunde ist nicht aktiv");
            }
            if (!$domain_id) {
                throw new Exception("ISPConfig - Keine Domainid angegeben");
            }


            $soapClient = new \v4\ISPConfig\SoapClient(
                \AppConfig::setting('ispconfig.host'),
                \AppConfig::setting('ispconfig.user'),
                \AppConfig::setting('ispconfig.password')
            );

            $ispc_client = $soapClient->clientGet($clientId);
            if (!$ispc_client) {
                throw new Exception("ISPConfig - ClientID $clientId nicht gefunden");
            }
            $sys_groupid = $soapClient->clientGetGroupid($clientId);

            $webdomain = $soapClient->sitesWebDomainGet($domain_id);

            if ($webdomain == null) {
                throw new Exception("ISPConfig - Domainid $domain_id nicht gefunden");
            }

            $username = key_exists('username', $changes) ? $changes['username'] : $rowUpdate->getField("username");
            $encrypted = key_exists('password', $changes) ? $changes['password'] : $rowUpdate->getField("password");
            $isp_config_elemid = key_exists('isp_config_elemid', $changes) ? $changes['isp_config_elemid'] : $rowUpdate->getField("isp_config_elemid");
            $isp_config_isactive = key_exists('isp_config_isactive', $changes) ? $changes['isp_config_isactive'] : $rowUpdate->getField("isp_config_isactive");

            $password = v4Zugangsdaten::decryptAES($encrypted, v4Zugangsdaten::getSalt());

            $isp_config_db_enabled = key_exists('isp_config_db_enabled', $changes) ? $changes["isp_config_db_enabled"] : $rowUpdate->getField("isp_config_db_enabled");
            $isp_config_db_name = key_exists('isp_config_db_name', $changes) ? $changes["isp_config_db_name"] : $rowUpdate->getField("isp_config_db_name");
            $isp_config_db_id = key_exists('isp_config_db_id', $changes) ? $changes["isp_config_db_id"] : $rowUpdate->getField("isp_config_db_id");
            $isp_config_db_active = key_exists('isp_config_db_active', $changes) ? $changes["isp_config_db_active"] : $rowUpdate->getField("isp_config_db_active");
            $isp_config_sshkey = key_exists('isp_config_sshkey', $changes) ? $changes["isp_config_sshkey"] : $rowUpdate->getField("isp_config_sshkey");

            /*******
             * Shellbenutzer
             */

            $defaultUsernames = ISPConfig::getDefaultUsernames($ispconfig);

            if ($rowUpdate->new_record) {
                $lq = new ListQuery('v4Zugangsdaten', array('id'));
                $lq->addSimpleFilter('type', $type);
                $lq->addSimpleFilter('username', $username);
                $row = $lq->runQuerySingle();
                $result = $lq->fetchResultCount();
                if ($result > 0) {
                    throw new Exception(sprintf(translate('LBL_CREATE_ZUGANGSDATEN_EXIST',"ISPConfig"), $type, $username));
                }
            }

            if ($type == "ssh") {


                if (!$password)
                    $password = ISPCUtilities::generateStrongPassword();



                // Prüfen ob Shellbenutzer mit ID existiert
                if ($isp_config_elemid) {
                    $shelluser = $soapClient->sitesShellUserGet($isp_config_elemid);
                    if ($shelluser) {
                        $shelluser['password'] = $password;
                    } else {
                        $isp_config_elemid = "";
                    }
                }
                // Shellbenutzer in der Domain suchen
                if (!$shelluser && $username) {

                    $username = trim($defaultUsernames['ssh']['prefix'] . str_replace($defaultUsernames['ssh']['prefix'], "", $username), "_");
                    $shelluser = $soapClient->sitesShellUserGet(array("username" => $username, "parent_domain_id" => $domain_id));
                    if (sizeof($shelluser)) {
                        $password = "";
                        $shelluser = $shelluser[0];
                        $isp_config_elemid = $shelluser['shell_user_id'];
                        $username = $shelluser['username'];
                        $isp_config_sshkey = $shelluser['ssh_rsa'];
                        $shelluser['password'] = $password;
                    }
                }

                // Shellbenutzer anlegen
                if (!$shelluser) {


                    $username = trim($defaultUsernames['ssh']['prefix'] . str_replace($defaultUsernames['ssh']['prefix'], "", ($username ? $username : $defaultUsernames['ssh']['username'])), "_");
                    $password = $password ? $password : ISPCUtilities::generateStrongPassword();
                    $isp_config_isactive = 1;
                    $shelluser = [
                        "server_id" => $server_id,
                        "parent_domain_id" => $domain_id,
                        "username" => $username,
                        "username_prefix" => $defaultUsernames['ssh']['prefix'],
                        "password" => $password,
                        "quota_size" => $hd_quota,
                        "active" => $isp_config_isactive ? "y" : "n",
                        "shell" => "/bin/bash",
                        "puser" => "web" . $domain_id,
                        "pgroup" => "client" . $clientId,
                        "dir" => $webdomain['document_root'],
                        "chroot" => "",
                        "ssh_rsa" => $isp_config_sshkey,
                    ];
                    $isp_config_elemid = $soapClient->sitesShellUserAdd($clientId, $shelluser);
                }
                // Shelluser updaten
                if ($isp_config_elemid && $shelluser) {
                    $username = $defaultUsernames['ssh']['prefix'] . str_replace($defaultUsernames['ssh']['prefix'], "", ($username ? $username : $defaultUsernames['ssh']['username']));
                    $shelluser['active'] = $isp_config_isactive ? "y" : "n";
                    $shelluser['username'] = $username;
                    $shelluser['username_prefix'] = $defaultUsernames['ssh']['prefix'];
                    $shelluser['active'] = $isp_config_isactive ? "y" : "n";
                    $shelluser['ssh_rsa'] = $isp_config_sshkey;
                    $soapClient->sitesShellUserUpdate($clientId, $isp_config_elemid, $shelluser);
                }


            }

            /*******
             * Datenbankbenutzer
             */
            if ($type == "mysql") {

                $username = $defaultUsernames['mysql']['prefix'] . str_replace($defaultUsernames['mysql']['prefix'], "", ($username ? $username : $defaultUsernames['mysql']['username']));
                // Prüfen ob Datenbankbenutzer mit ID existiert
                if ($isp_config_elemid) {
                    $dbuser = $soapClient->sitesDatabaseUserGet($isp_config_elemid);
                    if ($dbuser) {
                        $dbuser['password'] = $password;
                    } else {
                        $isp_config_elemid = "";
                    }
                }
                // Datenbankbenutzer  von Client suchen
                if (!$dbuser) {
                    $dbuser = $soapClient->sitesDatabaseUserGet(array("database_user" => $username, "sys_groupid" => $sys_groupid));
                    if (sizeof($dbuser)) {
                        $dbuser = $dbuser[0];
                        $password = "";

                        $dbuser['password'] = $password;
                        $isp_config_elemid = $dbuser['database_user_id'];
                        $username = $dbuser['database_user'];

                    }
                }

                // Datenbankbenutzer hinzufügen
                if (!$dbuser) {
                    $password = $password ? $password : ISPCUtilities::generateStrongPassword();
                    $isp_config_elemid = $soapClient->sitesDatabaseUserAdd($clientId, $server_id, $username, $password);
                    if (!$isp_config_elemid) {
                        throw new Exception($soapClient->getLastException());
                    }
                }
                // Datenbankbenutzer updaten
                if ($isp_config_elemid && $dbuser) {
                    $update = $soapClient->sitesDatabaseUserUpdate($clientId, $isp_config_elemid, $server_id, $username, $password);
                    if (!$update) {
                        //throw new Exception($soapClient->getLastException());
                    }
                }

                // Datenbank erzeugen
                if ($isp_config_db_enabled) {
                    $isp_config_db_name = $defaultUsernames['mysql']['dbprefix'] . str_replace($defaultUsernames['mysql']['dbprefix'], "", ($isp_config_db_name ? $isp_config_db_name : $defaultUsernames['mysql']['dbname']));
                    if ($isp_config_db_id) {
                        $db = $soapClient->sitesDatabaseGet($isp_config_db_id);
                        $rowUpdate->set("isp_config_db_name", $isp_config_db_name);
                    }
                    if (!$db) {
                        $db = $soapClient->sitesDatabaseGet(array("database_name" => $isp_config_db_name));
                        if (sizeof($db)) {
                            $db = $db[0];
                            $isp_config_db_name =  $db['database_name'];
                            $isp_config_db_id = $db['database_id'];
                            $rowUpdate->set("isp_config_db_id", $isp_config_db_id);
                            $rowUpdate->set("isp_config_db_name", $isp_config_db_name);
                            $rowUpdate->set("isp_config_db_active", $db['active'] == "y" ? 1 : 0);
                        }
                    }
                    if (!$db) {
                        $isp_config_db_id = $soapClient->sitesDatabaseAdd($clientId, $server_id, $domain_id, $isp_config_db_name, $isp_config_elemid);
                        if ($isp_config_db_id) {
                            $rowUpdate->set("isp_config_db_id", $isp_config_db_id);
                            $rowUpdate->set("isp_config_db_name", $isp_config_db_name);
                            $rowUpdate->set("isp_config_db_active", 1);
                            $db = $soapClient->sitesDatabaseGet($isp_config_db_id);
                        }
                    }
                    if ($db) {
                        $params = [
                            'database_name_prefix' => $defaultUsernames['mysql']['dbprefix'],
                            'database_name' => $isp_config_db_name,
                            'active' => $isp_config_db_active ? "y" : "n",
                        ];
                        $update = $soapClient->sitesDatabaseUpdate($clientId, $isp_config_db_id, $params);
                    }
                }

            }
            if ($isp_config_elemid && ($type == "mysql" || $type == "ssh")) {
                // CRM aktualisieren
                if ($isp_config_elemid) {
                    $rowUpdate->set(array(
                        'isp_config_isactive' => true,
                        'isp_config_elemid' => $isp_config_elemid,
                        'password' => v4Zugangsdaten::encryptAES($password, v4Zugangsdaten::getSalt()),
                        'username' => $username,
                    ));
                }
            }


        } catch (Exception $e) {
            throw new IAHActionAbort($e->getMessage());
        }
    }


    static function account_before_save(RowUpdate $rowUpdate)
    {

        if (!ISPConfig::account_has_updates($rowUpdate)) {
            return;
        }
        $active = $rowUpdate->getField('ispc_client_isactive');
        $account_id = $rowUpdate->getField("id");
        $changes = $rowUpdate->getChanges();
        $clientId = key_exists('ispc_client_id', $changes) ? $changes['ispc_client_id'] : $rowUpdate->getField('ispc_client_id');
        $company_name = $rowUpdate->getField('name');
        $contact_name = $company_name;
        $primary_contact_id = $rowUpdate->getField('primary_contact_id');
        if ($primary_contact_id) {
            $contact = ListQuery::quick_fetch('Contact', $primary_contact_id);
            if ($contact != null) {
                $contact_firstname = $contact->getField("first_name");
                $contact_name = $contact->getField("last_name");
            }

        }

        $username = key_exists('ispc_username', $changes) ? $changes['ispc_username'] : $rowUpdate->getField('ispc_username');
        $v4_kundennummer = $rowUpdate->getField('v4_kundennummer');

        $password = $rowUpdate->getField('ispc_password');
        $password = $password ? $password : ISPCUtilities::generateStrongPassword();
        $email = $v4_kundennummer . "@visual4.de";
        $telefon = $rowUpdate->getField("phone_office");
        $countrycode = $rowUpdate->getField("billing_address_countrycode");
        $countrycode = $countrycode ? $countrycode : "DE";


        try {
            $soapClient = new \v4\ISPConfig\SoapClient(
                \AppConfig::setting('ispconfig.host'),
                \AppConfig::setting('ispconfig.user'),
                \AppConfig::setting('ispconfig.password')
            );
            if ($clientId) {
                $client = $soapClient->clientGet($clientId);
                if ($client) {
                    if (!$username) {
                        $username = $client['username'];
                    }
                } else {
                    $clientId = "";
                }

            }
            if (!$clientId) {
                $username = $username ? $username : $v4_kundennummer;
                $client = $soapClient->clientGet(array("username" => $username));
                if (sizeof($client)) {
                    $client = $client[0];
                    $clientId = $client['client_id'];
                }
            }
            if (!$clientId) {
                $username = $username ? $username : $v4_kundennummer;
                $clientId = $soapClient->clientAdd(
                    "$contact_name",
                    "$contact_firstname",
                    "$company_name",
                    "$username",
                    "$password",
                    "$email",
                    "$telefon",
                    "$v4_kundennummer",
                    "$countrycode"

                );

                if (!$clientId) {
                    throw new Exception($soapClient->getLastException()->getMessage());
                }
            } else {
                $params = array(
                    "country" => $countrycode,
                    "company_name" => $company_name,
                    "contact_firstname" => $contact_firstname,
                    "contact_name" => $contact_name,
                    "password" => $password,
                    "customer_no" => $v4_kundennummer,
                    "email" => $email,
                    "username" => $username,
                    "locked" => ($active == "no" ? "y" : ""),
                    "canceled" => ($active == "no" ? "y" : ""),

                );

                $update = $soapClient->clientUpdate($clientId, 0, $params);
                if (!$update) {
                    throw new Exception($soapClient->getLastException()->getMessage());
                }
            }
            $rowUpdate->set("ispc_client_id", $clientId);
            $rowUpdate->set("ispc_client_isactive", $active);
            $rowUpdate->set("ispc_username", $username);
            $rowUpdate->set("ispc_password", $password);

        } catch
        (Exception $e) {
            throw new IAHActionAbort($e->getMessage());
        }

    }

    static function account_after_save(RowUpdate $rowUpdate)
    {

        if (!ISPConfig::account_has_updates($rowUpdate)) {
            return;
        }

        $changes = $rowUpdate->getChanges();
        $account_id = $rowUpdate->getField("id");
        $clientId = $rowUpdate->getField('ispc_client_id');
        $clientId = key_exists('ispc_client_id', $changes) ? $changes['ispc_client_id'] : $rowUpdate->getField('ispc_client_id');
        $active = key_exists('ispc_client_isactive', $changes) ? $changes['ispc_client_isactive'] : $rowUpdate->getField('ispc_client_isactive');
        //$clientId = $changes['ispc_client_id'];
        if ($clientId && $active) {
            $result = ISPConfig::syncAccountWebsites($account_id, $clientId);
            if ($result['count']) {
                add_flash_message("ISPConfig - " . $result['count'] . " neue Webseite(n) Seite gefunden: " . implode(", ", $result['newdomains']), "info");
            }
        }
    }
}