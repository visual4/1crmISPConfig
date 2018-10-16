<?php return; /* no output */ ?>

layout
    sections
		--
			id: ispconfig_client_cred
            vname: LBL_ISPC_TITLE
            hidden: !config.ispconfig.enabled
			elements
                - ispc_client_isactive
                - ispc_client_id
                - ispc_username
                - ispc_password
    subpanels
        --
        	name: ispc_sites
			hidden: !config.ispconfig.enabled