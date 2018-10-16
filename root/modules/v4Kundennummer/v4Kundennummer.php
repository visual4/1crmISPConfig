<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 09.04.14
 * Time: 16:13
 */

class v4Kundennummer {
    public static function account_before_save(RowUpdate $rowUpdate){
        $kdNr = $rowUpdate->getField('v4_kundennummer');
        if (/*$rowUpdate->new_record ||*/ empty($kdNr)) {
            $nextId = AppConfig::next_sequence_value('kundennummer_sequence');
            $prefix = AppConfig::get_sequence_prefix('kundennummer_prefix');
            $rowUpdate->set(array('v4_kundennummer' => $prefix.$nextId));
        }
    }
} 