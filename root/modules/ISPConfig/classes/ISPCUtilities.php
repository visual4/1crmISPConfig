<?php
/**
 * Created by PhpStorm.
 * User: thomas_stauch
 * Date: 15.09.2017
 * Time: 16:54
 */

//namespace v4\ISPConfig;

class ISPCUtilities
{

    static function clean_domain_name($domain_name)
    {
        $domain_name = trim($domain_name, '/');
        if (!preg_match('#^http(s)?://#', $domain_name)) {
            $domain_name = 'http://' . $domain_name;
        }
        $urlParts = parse_url($domain_name);

        $domain_name = preg_replace('/^www\./', '', $urlParts['host']);
        return $domain_name;
    }

    static function is_valid_domain_name($domain_name)
    {
        return (preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $domain_name));
    }

    static function generateStrongPassword($length = 15, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

}