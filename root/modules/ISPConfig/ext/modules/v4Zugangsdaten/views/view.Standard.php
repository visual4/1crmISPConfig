<?php return; /* no output */ ?>


layout
    sections
		--
			id: ispconfig
            vname: LBL_ISPC_TITLE
			elements
                - isp_config_enabled
                --
                    id: isp_config_enabled_setting
					section: true
					toggle_display
						name: isp_config_enabled
                    elements
                        - isp_config
                        - isp_config_elemid
                --
                    id: isp_config_isactive_setting
                    section: true
                    toggle_display
                        name: type
                        value: ssh
                    elements
                        - isp_config_isactive
                --
                    id: isp_config_database
                    section: true
                    toggle_display
                        name: type
                        value: mysql
                    elements
                        - isp_config_db_enabled
                --
                    id: isp_config_database_setting
                    section: true
                    toggle_display
                        name: isp_config_db_enabled
                    elements
                        - isp_config_db_name
                        - isp_config_db_id
                        - isp_config_db_active
                --
                    id: isp_config_sshkey_setting
                    section: true
                    toggle_display
                        name: type
                        value: ssh
                    elements
                        - isp_config_sshkey