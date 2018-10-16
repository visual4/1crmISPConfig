<?php return; ?>
detail
    type: edit
    title: LBL_ISPCONFIG_SETTINGS_TITLE
    icon: Administration
layout
	form_buttons
        ispconfig_sync_server
            vname: LBL_ISPCONFIG_SYNC_SERVER
            type: button
            async: false
            #onclick: "SyncServer(this.form);"
            #onclick: return SUGAR.ui.sendForm(document.forms.DetailForm, {"action":"SyncServer"}, null);
            url: ?module=ISPConfig&action=SyncServer
	sections
        --
            id: ispconfig
            vname: LBL_ISPCONFIG_SETTINGS
            columns: 1
            show_descriptions: true
            elements
                - ispconfig_enabled
                --
                    id: auth
					section: true
					toggle_display
						name: ispconfig_enabled
                    elements
                        - ispconfig_host
                        - ispconfig_user
                        - ispconfig_password
                        - ispconfig_sys_userid

