<?php return; ?>

fields
    ispc_client_isactive
        vname: LBL_ISPC_CLIENTACTIVE
		type: status
		options: contract_status_dom
		dbType: char
		len: 3
		default: ""
		audited: true
		massupdate: false
		required: false
    ispc_client_id
        vname: LBL_ISPC_CLIENTID
        type: int
        #unified_search: true
    ispc_username
        vname: LBL_ISPC_USERNAME
        type: varchar
        #unified_search: true
    ispc_password
        vname: LBL_ISPC_PASSWORD
        type: varchar
        #unified_search: false
links
    ispc_sites
		relationship: accounts_ispc_sites
		bean_name: ISPConfig
        vname: LBL_ISPC_SITES_SUBPANEL_TITLE
        add_existing: true
hooks

	before_save
		--
            class_function: account_before_save
			class: ispc_hooks
			file: modules/ISPConfig/ispc_hooks.php

	after_save
		--
			class_function: account_after_save
			class: ispc_hooks
			file: modules/ISPConfig/ispc_hooks.php
relationships
    accounts_ispc_sites
        key: id
		target_bean: ISPConfig
		target_key: account_id
		relationship_type: one-to-many
