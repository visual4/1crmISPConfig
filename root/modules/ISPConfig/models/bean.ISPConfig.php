<?php return; /* no output */ ?>

detail
	type: bean
	bean_file: modules/ISPConfig/ISPConfig.php
	audit_enabled: true
	activity_log_enabled: true
	unified_search: true
	duplicate_merge: true
	optimistic_locking: true
	table_name: ispconfig
	primary_key: id
	default_order_by: date_entered DESC
	reportable: true
    display_name: name

hooks
    before_save
        --
            class_function: before_save
            class: ispc_hooks
            file: modules/ISPConfig/ispc_hooks.php
    after_save
        --
            class_function: after_save
            class: ispc_hooks
            file: modules/ISPConfig/ispc_hooks.php

fields
	app.id
	app.deleted
	app.date_entered
	app.date_modified
	app.modified_user
	app.assigned_user
	app.created_by_user
    name
        vname: LBL_NAME
        type: varchar
        required: true
        unified_search: true
    account
		vname: LBL_ACCOUNT
		type: ref
		bean_name: Account
        unified_search: true
		required: true
		isnull: true
    server_id
        vname: LBL_SERVER
        type: enum
		len: 80
        options_add_blank: true
        unified_search: true
		options_function
			class: ISPConfig
			file: modules/ISPConfig/ISPConfig.php
			class_function: getServerList
        required: true
        updateable: false
    document_root
        vname: LBL_PATH
        type: varchar
        unified_search: true
    domain_name
        vname: LBL_ISPC_DOMAIN
        type: varchar
        unified_search: true
        required: true
    domain_id
        vname: LBL_ISPC_ID
        type: int
        unified_search: true
    active
        vname: LBL_ACTIVE
        type: bool
        default: true
        unified_search: true
    client_id
        vname: LBL_CLIENT_ID
        type: int
    ip_address
        vname: LBL_IP_ADDRESS
        type: enum
        unified_search: true
        options_function
			class: ISPConfig
			file: modules/ISPConfig/ISPConfig.php
			class_function: getIPList
        default: *
    hd_quota
        vname: LBL_HD_QUOTA
        type: int
        default: -1
    traffic_quota
        vname: LBL_TRAFFIC_QUOTA
        type: int
        default: -1
    service_contract
        type: ref
        bean_name: SubContract
        #required: true
        vname: LBL_SUBCONTRACT
        add_filters
			--
				param: account
				field_name: account
    php
        vname: LBL_PHP
		type: enum
		#options: ispc_php_version_dom
        options_function
			class: ISPConfig
			file: modules/ISPConfig/ISPConfig.php
			class_function: getAllPhp
		len: 25
		audited: false
		default: php-fpm
		massupdate: true
        options_add_blank: false
    php_version
        vname: LBL_PHP_VERSION
		type: varchar
		editable: false
    backup_interval
        vname: LBL_BACKUP_INTERVALL
		type: enum
		options: ispc_backup_interval_dom
		len: 25
		audited: false
		default: daily
		massupdate: true
        options_add_blank: false
    backup_copies
        vname: LBL_BACKUP_COPIES
        type: enum
        options: [1,2,3,4,5,6,7,8,9,10]
        len: 25
        audited: false
        default: 0
        massupdate: true
        options_add_blank: false
    backup_excludes
        vname: LBL_BACKUP_EXCLUDES
        type: varchar
        default : "web/*,bin/*,dev/*,etc/*,lib/*,lib64/*,log/*,usr/*,var/*"
    lets_encrypt
        vname: LBL_LETS_ENCRYPT
        type: bool
    apache_directives
        vname: LBL_APACHE_DIRECTIVES
        type: text
        default: "client_max_body_size 100M;"



indices
	idx_client_id
		fields
			- client_id
    idx_service_contract_id
		fields
			- service_contract_id
    idx_account_id
		fields
			- account_id
links
    ispc_zugangsdaten
		relationship: ispc_subcontract_zugangsdaten
		vname: LBL_ZUGANGSDATEN


relationships
    ispc_subcontract_zugangsdaten
		key: id
		target_bean: v4Zugangsdaten
		target_key: isp_config_id
		relationship_type: many-to-many
