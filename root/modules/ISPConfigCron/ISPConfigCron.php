<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 17.04.15
 * Time: 10:23
 */


require_once 'modules/ISPConfig/ISPConfig.php';
require_once 'modules/ISPConfig/classes/ISPConfig/SoapClient.php';
require_once('data/SugarBean.php');

class ISPConfigCron extends SugarBean
{


    static function new_record(RowUpdate $upd)
    {
        $update = array();
        if ($upd->getField("isp_config_id")) {
            $ispconfig = ListQuery::quick_fetch('ISPConfig', $upd->getField("isp_config_id"));
            if ($ispconfig != null) {
                if ($ispconfig->getField("domain_name")) {
                    $path = "/var/www/" . $ispconfig->getField("domain_name");
                }
            }

        }
        if (!$path) {
            $path = "[web_root]";
        } else {
            $path = $path . "/web";
        }

        $command = 'sleep $[ ( $RANDOM % 120 ) + 1 ]s; cd ' . $path . ' && /usr/bin/php -q scheduler.php';
        $update['command'] = $command;

        foreach ($update as $k => $v)
            if ($upd->isFieldEmpty($k))
                $upd->set($k, $v);

    }

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

        $isp_config_id = $rowUpdate->getField('isp_config_id');
        if (!$isp_config_id) {
            return;
        }

        $ispconfig = ListQuery::quick_fetch('ISPConfig', $isp_config_id);
        if ($ispconfig != null) {
            if (!$ispconfig->getField("active")) {
                return;
            }
            $domain_id = $ispconfig->getField("domain_id");
            if (!$domain_id) {
                throw new Exception("Keine DomainID hinterlegt");
            }
            try {
                $soapClient = new \v4\ISPConfig\SoapClient(
                    \AppConfig::setting('ispconfig.host'),
                    \AppConfig::setting('ispconfig.user'),
                    \AppConfig::setting('ispconfig.password')
                );
                $cron_id = $rowUpdate->getField('cron_id');
                $active = $rowUpdate->getField("active");
                $domain = $soapClient->sitesWebDomainGet($domain_id);
                if (!$domain) {
                    throw new Exception("Domain $domain_id konnte nicht gefunden werden");
                }
                $server_id = $domain['server_id'];
                $client_id = $domain['sys_userid'];

                if ($cron_id) {
                    $cron = $soapClient->sitesCronGet($cron_id);
                    $cron_id = $cron['id'];

                }


                $params = [
                    'server_id' => $server_id,
                    'parent_domain_id' => $domain_id,
                    'run_min' => $rowUpdate->getField("run_min"),
                    'run_hour' => $rowUpdate->getField("run_hour"),
                    'run_mday' => $rowUpdate->getField("run_mday"),
                    'run_month' => $rowUpdate->getField("run_month"),
                    'run_wday' => $rowUpdate->getField("run_wday"),
                    'command' => $rowUpdate->getField("command"),
                    'active' => $active ? "y" : "n",
                    'log' => $rowUpdate->getField("log") ? "y" : "n",
                ];
                if (!$cron_id) {
                    $cron_id = $soapClient->sitesCronAdd($client_id, $params);
                    if (!$cron_id) {
                        throw new Exception($soapClient->getLastException());
                    }
                } else {
                    $update = $soapClient->sitesCronUpdate($client_id, $cron_id, $params);
                    if (!$update) {
//                        throw new Exception($soapClient->getLastException());
                    }

                }

                $rowUpdate->set(array(
                    'cron_id' => $cron_id
                ));

            } catch (Exception $e) {
                throw new IAHActionAbort($e->getMessage());
            }
        }
    }


}