<?php

require_once('include/layout/forms/FormElement.php');



class v4PasswordWidget extends FormElement {
    function init($params=null, $model=null) {
        parent::init($params, $model);
	}

	function getRequiredFields() {
        $fields = array('password');
		return $fields;
	}

    function renderListCell(ListFormatter &$fmt, ListResult &$result, $row_id, $list_params=null) {

        $url = 'index.php?module=v4Zugangsdaten&action=revealPassword&record='.$row_id;
        $js = 'SUGAR.popups.openUrl("'.$url.'", null, {width: "350px", title_text: "Passwort anzeigen", resizable: false})';
        $output = "<button onclick='".$js."'>anzeigen</button>";
        return $output;
    }

    function getLabel($context=null) {
    	$l = parent::getLabel($context);
    	if(! $l)
			return translate('LBL_PASSWORD', 'v4Zugangsdaten');
		return $l;
    }
} 