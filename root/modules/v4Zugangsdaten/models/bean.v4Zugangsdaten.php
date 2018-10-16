<?php return; ?>
detail
	type: bean
	bean_file: modules/v4Zugangsdaten/v4Zugangsdaten.php
	unified_search: true
	activity_log_enabled: true
	duplicate_merge: false
	optimistic_locking: true
	table_name: v4zugangsdaten
	primary_key: id
	reportable: true
	display_name: server
hooks
    before_save
        --
            class_function: before_save
fields
	app.id
	app.date_entered
	app.date_modified
	app.assigned_user
	app.modified_user
	app.created_by_user
	app.deleted
	server
		vname: LBL_SERVER
		type: url
		len: 150
		vname_list: LBL_LIST_SUBJECT
		unified_search: true
		required: true
    type
        vname: LBL_TYPE
        type: enum
        options: v4_server_types_dom
        required: true
    username
        vname: LBL_USERNAME
        type: varchar
        required: true
    password
        vname: LBL_PASSWORD
        type: varchar
        required:true
        reportable: false
    description
        vname: LBL_DESCRIPTION
        type: text
    service_subcontract
        type: ref
        bean_name: SubContract
        vname: LBL_SUBCONTRACT
        required: true
links
    app.securitygroups
		relationship: securitygroups_zugangsdaten

relationships
	securitygroups_zugangsdaten
		lhs_key: id
		rhs_key: id
		relationship_type: many-to-many
		join_table: securitygroups_records
		join_key_lhs: securitygroup_id
		join_key_rhs: record_id
		relationship_role_column: module
		relationship_role_column_value: v4Zugangsdaten
		lhs_bean: SecurityGroup
		rhs_bean: v4Zugangsdaten