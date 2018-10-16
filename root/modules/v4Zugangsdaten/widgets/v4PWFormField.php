<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 04.02.15
 * Time: 14:27
 */
require_once('include/layout/forms/FormField.php');
require_once 'modules/v4Zugangsdaten/v4Zugangsdaten.php';

class v4PWFormField extends FormField
{
    function renderHtml(HtmlFormGenerator &$gen, RowResult &$row_result, array $parents, array $context)
    {
        global $mod_strings;
        $encrypted = $row_result->getField('password');
        if (empty($encrypted) && $row_result->isNewRecord()) {
            $password = self::pwgen(12);
        } else {
            $password = v4Zugangsdaten::decryptAES($encrypted, v4Zugangsdaten::getSalt());
        }

        $row_result->formatted['password'] = $row_result->row['password'] = $password;
        $this->name = 'password';
        $row_id = $row_result->getPrimaryKeyValue();

        if ((isset($context['editable']) && $context['editable']) ) {
            $value = $gen->form_obj->renderField($row_result, 'password');
            if (! $row_result->isNewRecord()){

                //activity log Eintrag erzeugen
                $status = 'revealPassword';
                $module = $row_result->getModuleDir();
                global $current_user;
                $activity = array(
                    'assigned_user_id' => $current_user->id,
                    'module_name' => $module,
                    'record_item_id' => $row_id,
                    'status' => $status,
                    'primary_account_id' => null,
                    'primary_contact_id' => null,
                );
                $rowUpdate = new RowUpdate('ActivityLog');
                $rowUpdate->set($activity);
                $rowUpdate->new_record = true;
                if ($rowUpdate->validate()) {
                    $rowUpdate->save();

                }
            }

        } else {
            //$value = parent::renderHtml($gen, $row_result, $parents, $context);

            $url = 'index.php?module=v4Zugangsdaten&action=revealPassword&record=' . $row_id;
            $js = 'SUGAR.popups.openUrl("' . $url . '", null, {width: "350px", title_text: "Passwort anzeigen", resizable: false})';
            $value = "<button onclick='" . $js . "'>anzeigen</button>";
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