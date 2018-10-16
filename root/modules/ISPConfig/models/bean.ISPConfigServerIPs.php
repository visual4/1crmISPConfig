<?php return; /* no output */ ?>

detail
	type: bean
	bean_file: modules/ISPConfig/ISPConfig.php
	duplicate_merge: false
	table_name: ispconfig_server_ips
	primary_key: server_ip_id
    display_name: server_ip
    default_order_by: server_ip ASC
fields
    server_ip_id
        vname: LBL_SERVER
        type: enum
		len: 80
	server_id
        vname: LBL_SERVER
        type: enum
		len: 80
    server_ip
        vname: LBL_SERVER_IP
        type: varchar
indices
	idx_server_id
		fields
			- server_id
