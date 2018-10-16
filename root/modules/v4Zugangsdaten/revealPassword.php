<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 08.06.2016
 * Time: 20:42
 */

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/database/ListQuery.php');
require_once 'modules/v4Zugangsdaten/v4Zugangsdaten.php';
global $mod_strings, $app_strings, $current_user;

$record = '';
if (empty($_REQUEST['record'])) {
    echo 'oups';
    showError();
}

$record = $_REQUEST['record'];

$lq = new ListQuery('v4Zugangsdaten');
$lq->addFilterPrimaryKey($record);
$lq->addAclFilter('edit');
$result = $lq->runQuerySingle();

if (!$result->getRow()) {
    $password = "GESPERRT (keine Berechtigung)";
    $server = '';
    $username = '';
} else {
    $encrypted = $result->getField('password');
    $password = v4Zugangsdaten::decryptAES($encrypted, v4Zugangsdaten::getSalt());
    $username = $result->getField('username');
    $server = $result->getField('server');
}


$html = <<<EOQ
   <form name='revealPassword' id='revealPassword'> 
    <table width='100%' cellspacing='0' cellpadding='1' border='0' class="tabForm">
<td width='40%' class='dataLabel' nowrap>{$mod_strings['LBL_SERVER']}:</td>
    <td width='60%' class='dataField'><a href='{$server}' target='_blank'>{$server}</a></td>
</tr>
<tr>
<td width='40%' class='dataLabel' nowrap>{$mod_strings['LBL_USERNAME']}:</td>
    <td width='60%' class='dataField'>{$username}</td>
</tr>
<tr>
<td width='40%' class='dataLabel' nowrap>{$mod_strings['LBL_PASSWORD']}:</td>
    <td width='60%' class='dataField'><span id='thePWD'><pre>{$password}</pre></span></td>
</tr>
<tr>
<td width='40%' class='dataLabel' nowrap colspan='2'>
<button class='input-button input-outer' onclick='SUGAR.popups.hidePopup(popup_dialog);' type='button' name='cancel'><div class="input-icon icon-cancel left"></div><span class="input-label">{$mod_strings['LBL_CLOSE_BUTTON_LABEL']}</span></button>
    </td>
</tr>
</table>
</form>
<script type="text/javascript">
    	SUGAR.popups.initContent({form_name: 'revealPassword', onshow: function() { 
    	 setTimeout(function(){SUGAR.popups.hidePopup(popup_dialog);}, 30000);
    	 } 
    	});
    </script>
EOQ;


echo $html;

if (empty($username) || empty($server)) {
    $status = 'revealPassword';
    $module = $result->getModuleDir();
    $activity = array(
        'assigned_user_id' => $current_user->id,
        'module_name' => $module,
        'record_item_id' => $record,
        'status' => $status,
        'primary_account_id' => null,
        'primary_contact_id' => null,
    );

    $rowUpdate = new RowUpdate('ActivityLog');
    $rowUpdate->set($activity);
    $rowUpdate->new_record = true;
    if ($rowUpdate->validate()) {
        $rowUpdate->save();
        //echo 'ok';
    }
//else echo $rowUpdate->getErrors(true);
}
exit();
function showError()
{
    exit("Unauthorized access.");
}

