<?php return; /* no output */ ?>

detail
    type: editview
    title: LBL_MODULE_TITLE
layout
    scripts
		--
			file: "modules/ISPConfigCron/EditView.js"
    form_hooks
		oninit: initForm
    summary
		header
			fields
                --
                    name: isp_config

	sections
        --
			id: main
			elements
                - active
                - log
                - run_min
                - run_hour
                - run_mday
                - run_month
                - run_wday
                --
                    name: command
                    colspan: 1
                - cron_id