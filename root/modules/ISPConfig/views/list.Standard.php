<?php return; ?>

detail
	type: list

layout
    scripts
		--
			file: "modules/ISPConfig/EditView.js"
    form_hooks
		oninit: initForm
    columns
        --
            field: name
            detail_link: true
        - account
        - service_contract
        --
            field: domain_name
            detail_link: true
            add_fields: [ip_address]
        --
            field: server_id
            #add_fields: [document_root]
        --
            field: hd_quota
            add_fields: [traffic_quota]
        --
            field: php
            #add_fields: [php_version]
        --
            field: backup_interval
            add_fields: [backup_copies]
        - active

auto_filters
    name
    account