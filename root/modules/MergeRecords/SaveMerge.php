<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/MergeRecords/MergeRecord.php');
$module = array_get_default($_REQUEST, 'merge_module', '');
$model = new ModelDef(AppConfig::module_primary_bean($module));
$record = array_get_default($_REQUEST, 'record', '');
$merged_ids = array_get_default($_REQUEST, 'merged_ids', array());
$merge_record = new MergeRecord();

//Save Main Record
$result = $merge_record->saveMergeResult($model, $record);

//Delete merged records  and reassign relationships to Main Record
if ($result && sizeof($merged_ids) > 0) {

    $links = $model->getLinks();

    for ($i = 0; $i < sizeof($merged_ids); $i++) {
        $merge_record->reassignRelationships($model, $module, $merged_ids[$i], $record, $links);
        $merge_record->deleteMerged($model, $merged_ids[$i]);
    }

}
return array('redirect', array('module' => $module, 'action' => 'DetailView', 'record' => $record, 'record_perform' => 'view', 'layout' => 'Standard'));

?>

