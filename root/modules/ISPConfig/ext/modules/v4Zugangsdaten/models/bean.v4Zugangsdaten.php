<?php return; ?>
fields
    isp_config_enabled
        vname: LBL_ISPC_ENABLED
        type: bool
    isp_config
        type: ref
        bean_name: ISPConfig
        #required: true
        vname: LBL_ISPC_SITE
    isp_config_elemid
        vname: LBL_ISPC_ID
        type: int
    isp_config_isactive
        vname: LBL_ISPC_ISACTIVE
        type: bool
    isp_config_db_enabled
        vname: LBL_ISPC_DB_ENABLED
        type: bool
    isp_config_db_name
        vname: LBL_ISPC_DB_NAME
        type: varchar
    isp_config_db_id
        vname: LBL_ISPC_DB_ID
        type: int
    isp_config_db_active
        vname: LBL_ISPC_DB_ACTIVE
        type: bool
    isp_config_sshkey
        vname: LBL_ISPC_SSHKEY
        type: text

list
    buttons
        show_create_button: false

hooks
    new_record
		--
			class_function: init_record
            class: ISPCZugangsdaten
            file: modules/ISPConfig/ext/modules/v4Zugangsdaten/ISPCZugangsdaten.php

    before_save
		--
			class_function: zugangsdaten_before_save
            class: ispc_hooks
            file: modules/ISPConfig/ispc_hooks.php




