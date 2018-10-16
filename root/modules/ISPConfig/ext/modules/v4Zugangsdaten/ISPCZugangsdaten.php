<?php


require_once __DIR__ . '/../../../ISPConfig.php';
class ISPCZugangsdaten
{

    static function init_record(RowUpdate &$upd, $input)
    {
        $update = array();

        if (!empty($input['isp_config_id'])) {
            $ispconfig = ListQuery::quick_fetch_row('ISPConfig', $input['isp_config_id']);
            if ($ispconfig != null) {

                $update['service_subcontract_id'] = $ispconfig['service_contract_id'];
                $update['account_id'] = $ispconfig['account_id'];
                $update['type'] = "ssh";
                $update['isp_config_enabled'] = true;
                $update['isp_config_isactive'] = true;



                $usernames = ISPConfig::getDefaultUsernames($ispconfig);
                $update['server'] = $usernames[$update['type']]['server'];
                $ssh_username = $usernames['ssh'];
                $mysql_username = $usernames['mysql'];

                $update['username'] = $mysql_username;

            }
        }

        foreach ($update as $k => $v)
            if ($upd->isFieldEmpty($k))
                $upd->set($k, $v);
    }

}