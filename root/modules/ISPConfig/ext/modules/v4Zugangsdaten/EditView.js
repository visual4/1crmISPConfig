var typeData, selectedType, myform, defaultUsernames = [];

function initForm(form) {
    myform = form;
    typeData = SUGAR.ui.getFormInput(myform, 'type');
    SUGAR.ui.attachInputEvent(typeData, 'onchange', updateType);
    SUGAR.ui.attachInputEvent(SUGAR.ui.getFormInput(myform, 'isp_config_db_enabled'), 'onchange', updateType);
    SUGAR.ui.attachInputEvent(SUGAR.ui.getFormInput(myform, 'isp_config_enabled'), 'onchange', updateType);
    SUGAR.ui.attachInputEvent(SUGAR.ui.getFormInput(myform, 'isp_config'), 'onchange', updateType);

    new SUGAR.ui.Dialog()
    if (typeData) {
        selectedType = typeData.getValue();

    }
    updateType();
    SUGAR.ui.attachFormInputEvent(form, 'type', 'onchange', validateUsername);
    SUGAR.ui.attachFormInputEvent(form, 'isp_config_enabled', 'onchange', validateUsername);
    SUGAR.ui.attachFormInputEvent(form, 'username', 'onkeyup', validateUsername);
    SUGAR.ui.attachFormInputEvent(form, 'isp_config_db_name', 'onkeyup', validateUsername);

}


function validateUsername() {
    var typeFrom = SUGAR.ui.getFormInput(myform, 'type');
    var ispConfigEnabledFrom = SUGAR.ui.getFormInput(myform, 'isp_config_enabled');
    if ((typeFrom.getValue() == "mysql" || typeFrom.getValue() == "ssh") && ispConfigEnabledFrom.getValue() == "1") {
        var usernameForm = SUGAR.ui.getFormInput(myform, 'username');
        var username = usernameForm.getValue();
        usernameForm.setValue(username.replace(/\-/g, ""));

        var dbForm = SUGAR.ui.getFormInput(myform, 'isp_config_db_name');
        var dbname = dbForm.getValue();
        dbForm.setValue(dbname.replace(/\-/g, ""));
    }

}

function updateType() {
    var typeFrom = SUGAR.ui.getFormInput(myform, 'type'),
        usernameForm = SUGAR.ui.getFormInput(myform, 'username'),
        ispConfigEnabled = SUGAR.ui.getFormInput(myform, 'isp_config_enabled'),
        DBEnabledFrom = SUGAR.ui.getFormInput(myform, 'isp_config_db_enabled'),
        dbNameForm = SUGAR.ui.getFormInput(myform, 'isp_config_db_name'),
        ispConfig = SUGAR.ui.getFormInput(myform, 'isp_config'),
        type, isp_config_id, username, isp_config_enabled, isp_config_db_enabled, dbname;

    if (!typeFrom || !usernameForm) return;

    type = typeFrom.getValue();
    username = usernameForm.getValue();
    dbname = dbNameForm.getValue();
    isp_config_enabled = ispConfigEnabled.getValue();

    isp_config_db_enabled = DBEnabledFrom.getValue();


    var sect_ispconfig = document.getElementById("le_section_ispconfig");
    var sect_isp_config_database = document.getElementById("isp_config_database");
    var sect_isp_config_database_setting = document.getElementById("isp_config_database_setting");
    var sect_isp_config_isactive = document.getElementById("isp_config_isactive_setting");

    if (type != "ssh" && type != "mysql") {
        sect_ispconfig.style.display = 'none';
    } else {
        sect_ispconfig.style.display = '';
    }


    isp_config_id = document.getElementsByName("isp_config_id")[0].value;
    if (isp_config_enabled && isp_config_id && (type == "ssh" || type == "mysql" )) {
        if (defaultUsernames[isp_config_id] == undefined) {
            var req = new SUGAR.conn.JSONRequest('get_default_usernames', null, {
                module: 'ISPConfig',
                isp_config_id: isp_config_id
            });
            req.fetch(function (data) {
                var result = data.getResult()
                defaultUsernames[isp_config_id] = result;
                updateUsernames();
            });
        } else {
            updateUsernames();
        }


    }


}

function updateUsernames() {
    var insert_username = false,
        insert_server,
        type = SUGAR.ui.getFormInput(myform, 'type').getValue(),
        isp_config_id = document.getElementsByName("isp_config_id")[0].value,
        usernameForm = SUGAR.ui.getFormInput(myform, 'username'),
        serverForm = SUGAR.ui.getFormInput(myform, 'server'),
        username = usernameForm.getValue(),
        server = serverForm.getValue(),
        dbNameForm = SUGAR.ui.getFormInput(myform, 'isp_config_db_name'),
        isp_config_db_enabled = SUGAR.ui.getFormInput(myform, 'isp_config_db_enabled').getValue(),
        dbname = dbNameForm.getValue();

    for (var id in defaultUsernames) {
        for (var key in defaultUsernames[id]) {
            if (username == defaultUsernames[id][key]['username'] || username == "") {
                insert_username = true;
            }
            if (server == defaultUsernames[id][key]['server'] || server == "") {
                insert_server = true;
            }
        }
    }
    if (type == "mysql" && isp_config_db_enabled && dbname == "" && defaultUsernames[isp_config_id]['mysql']['dbname']) dbNameForm.setValue(defaultUsernames[isp_config_id]['mysql']['dbname']);

    if (insert_username) {
        username = defaultUsernames[isp_config_id][type]['username'];
        usernameForm.setValue(username);
    }
    if (insert_server) {
        server = defaultUsernames[isp_config_id][type]['server'];
        serverForm.setValue(server);
    }
}

