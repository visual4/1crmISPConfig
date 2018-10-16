<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 04.02.15
 * Time: 14:27
 */
require_once('include/layout/forms/FormField.php');
require_once 'modules/v4Zugangsdaten/v4Zugangsdaten.php';

class ISPCPWFormField extends FormField
{
    function renderHtml(HtmlFormGenerator &$gen, RowResult &$row_result, array $parents, array $context)
    {
        global $mod_strings;
        $id = $parents[0]->id;
        if ($id =="shelluser") {
            $fieldname = "shelluser_password";
        } else if ($id =="dbuser") {
            $fieldname = "dbuser_password";
        } else {
            $fieldname = "password";
        }

        $row_id = $row_result->getPrimaryKeyValue();

        $row = ListQuery::quick_fetch_key($row_result->getModel()->name,"id",$row_id);
        if ($row)
            $encrypted = $row->getFieldValue($fieldname);


         //   $password = v4Zugangsdaten::decryptAES($encrypted, v4Zugangsdaten::getSalt());
        $password = $encrypted;

        $row_result->formatted[$fieldname] = $row_result->row[$fieldname] = $password;
        $this->name = $fieldname;
        $row_id = $row_result->getPrimaryKeyValue();

        if ((isset($context['editable']) && $context['editable']) ) {
            $value = $gen->form_obj->renderField($row_result, $fieldname);
        } else {

            $url = 'index.php?module=ISPConfig&action=revealPassword&record=' . $row_id .'&fieldname='.$fieldname;
            $js = 'SUGAR.popups.openUrl("' . $url . '", null, {width: "350px", title_text: "Passwort anzeigen", resizable: false})';
            $value = "<button onclick='" . $js . "'>**********</button>";
            if (empty($encrypted)) {
                $value = "";
            } else {
                $value = "**********";
            }
        }

        return $value;
    }

    function init($params = null, $model = null)
    {
        parent::init($params, $model);
    }

    function getRequiredFields()
    {
        return array('password');
    }

    function getLabel($context = null)
    {
        $l = parent::getLabel($context);
        if (!$l)
            return translate('LBL_PASSWORD', 'v4Zugangsdaten');
        return $l;
    }

    function pwgen($lng = 15)
    {
        if ($lng < 8) $lng = 8;
        mt_srand(crc32(microtime()));

        //Welche Buchstaben benutzt werden sollen (Charset)
        $buchstaben = "abcdefghijkmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!$%=*+#,.;:?-";
        $buchstaben = str_shuffle($buchstaben);
        $str_lng = strlen($buchstaben) - 1;

        do {
            $passwort = "";
            for ($i = 0; $i < $lng; $i++) {

                $passwort .= substr($buchstaben, mt_rand(0, $str_lng), 1);
            }

        } while (!preg_match("%^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).*$%", $passwort));
        return $passwort;
    }


} 