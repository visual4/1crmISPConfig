<?php return; /* no output */ ?>

detail
	type: bean
	bean_file: modules/ISPConfigCron/ISPConfigCron.php
	audit_enabled: true
	activity_log_enabled: true
	unified_search: true
	duplicate_merge: true
	optimistic_locking: true
	table_name: ispconfig_crons
	primary_key: id

hooks
    new_record
		--
			class_function: new_record
    before_save
        --
            class_function: before_save
    new_record
        --
            class_function: edit
fields
    app.id
	app.deleted
	app.date_entered
	app.date_modified
	app.modified_user
	app.assigned_user
	app.created_by_user
	server_id
        vname: LBL_SERVER
        type: enum
		len: 80
    isp_config
        vname: LBL_WEBSITE
        type: ref
        bean_name: ISPConfig
        required: true
        massupdate: false
        unified_search: true
    cron_id
        vname: LBL_CRON_ID
        type: varchar
        editable: false
        #hidden: true
    run_min
        vname: LBL_RUN_MIN
        type: varchar
        required: true
        default: */5
        unified_search: true
    run_hour
        vname: LBL_RUN_HOUR
        type: varchar
        required: true
        default: *
        unified_search: true
    run_mday
        vname: LBL_RUN_MDAY
        type: varchar
        required: true
        default: *
    run_month
        vname: LBL_RUN_MONTH
        type: varchar
        required: true
        default: *
    run_wday
        vname: LBL_RUN_WDAY
        type: varchar
        required: true
        default: *
    command
        vname: LBL_COMMAND
        type: varchar
        required: true
        unified_search: true
    log
        vname: LBL_LOG
        type: bool

    active
        vname: LBL_ACTIVE
        type: bool
        default: true

indices
	idx_server_id
		fields
			- server_id
    idx_isp_config_id
		fields
			- isp_config_id

