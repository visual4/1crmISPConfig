<?php

$admin_option_defs = array();
$admin_option_defs['shipping_providers'] = array('Administration', array('LBL_ADMIN_ITEM_TILE', 'ISPConfig'), array('LBL_ADMIN_ITEM_DESC', 'ISPConfig'), './index.php?module=Configurator&action=EditView&layout=ISPConfig');
$admin_group_header[] = array(array('LBL_ADMIN_ITEM_TILE', 'ISPConfig'),'',false,$admin_option_defs);

