<?php return; ?>
detail
    title: LBL_MODULE_TITLE
layout
    scripts
		--
			file: "modules/ISPConfig/EditView.js"
    form_hooks
		oninit: initForm
	form_buttons
        ssh_create
			type: button
			vname: LBL_CREATE_ZUGANGSDATEN_LABEL
			url: ?module=ISPConfig&action=CreateZugangsdaten&record={RECORD}&{RETURN}
            hidden: !bean.domain_id
        sync_website
			type: button
			vname: LBL_SYNCWEBSITE_LABEL
			url: ?module=ISPConfig&action=SyncWebsite&record={RECORD}&{RETURN}
            hidden: !bean.domain_id
    summary
		header
			fields
                --
                    name: name
        subheader
			fields
                --
                    name: account
                - service_contract
        notes
		meta

    columns
        --
            field: name
            width: 40
            add_fields
            	--
            		field: server_id
            		list_format: separate
            		list_position: prefix
	sections
        --
			id: main
			elements
                --
                    name: server_id
                    list_format: separate
                    onchange: "updateServerIPs(this.form);"
                - active
                --
                    name: domain_name
                --
                    name: ip_address
                    editable: true
                --
                    name: hd_quota
                    vname: LBL_HD_QUOTA_DESCRIPTION
                --
                    name: traffic_quota
                    vname: LBL_TRAFFIC_QUOTA_DESCRIPTION
                - php
                - php_version
                - backup_interval
                - backup_copies
                - backup_excludes
                -
                - lets_encrypt
                --
                --
                    name: domain_id
                    editable: true
                --
                    name: document_root
                    editable: false
                --
                    name: apache_directives





    subpanels
        - ispc_zugangsdaten