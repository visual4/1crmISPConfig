<?php return; ?>

detail
	type: subpanel
layout
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