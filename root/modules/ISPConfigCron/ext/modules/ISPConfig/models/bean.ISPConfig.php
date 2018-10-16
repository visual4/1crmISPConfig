<?php return; /* no output */ ?>


links
    cronjobs
		relationship: ispc_crons
		bean_name: ISPConfigCron
		vname: LBL_CRON_TITLE
        add_existing: true
relationships
    ispc_crons
		key: id
        target_bean: ISPConfigCron
		target_key: isp_config_id
		relationship_type: one-to-many
