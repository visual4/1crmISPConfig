<?php return; /* no output */ ?>

detail
	type: bean
	bean_file: modules/ISPConfig/ISPConfig.php
	audit_enabled: true
	activity_log_enabled: true
	unified_search: true
	duplicate_merge: true
	optimistic_locking: true
	table_name: ispconfig_server
	primary_key: server_id
	default_order_by: date_entered DESC
	reportable: true
    display_name: server_name
fields
	server_id
        vname: LBL_SERVER
        type: enum
		len: 80
    server_ip
        vname: LBL_SERVER_IP
        type: varchar
    server_name
        vname: LBL_SERVER_NAME
        type: varchar
        unified_search: true
    server_hostname
        vname: LBL_SERVER_HOSTNAME
        type: varchar
        unified_search: true
    server_type
        vname: LBL_SERVER_PHPVERSION
        type: varchar
        unified_search: true
indices
	idx_server_id
		fields
			- server_id
