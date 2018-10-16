var myform, website, command_field, websites = [], webrootclass = "webroot", defaultDocRoot = "[web_root]",
    defaultCommand = "cd [web_root]/web && /usr/bin/php -q scheduler.php";

function initForm(form) {
    myform = form;

    command_field = document.getElementsByName("command")[0];

    var p = document.createElement("p");
    p.innerHTML = mod_string('LBL_DOCUMENT_ROOT_DESC', 'ISPConfigCron');
    if (command_field != "undefined") {
        command_field.parentNode.insertBefore(p, command_field.nextSibling);
    }

    AddDefautlCommandButton();
    website = SUGAR.ui.getFormInput(myform, 'isp_config');
    SUGAR.ui.attachInputEvent(website, 'onchange', getDefaults);
    if (website) {
        getDefaults();
    }

}


function getDefaults() {
    var website = SUGAR.ui.getFormInput(myform, 'isp_config'),
        isp_config_id;
    if (!website) return;

    isp_config_id = document.getElementsByName("isp_config_id")[0].value;
    if (isp_config_id) {
        if (websites[isp_config_id] == undefined) {
            var req = new SUGAR.conn.JSONRequest('get_crm_website', null, {
                module: 'ISPConfigCron',
                isp_config_id: isp_config_id
            });
            req.fetch(function (data) {
                var result = data.getResult()
                websites[isp_config_id] = result;
                updateFields();
            });

        } else {
            updateFields();
        }


    }
}

function transformCommand(add) {
    var command_value = document.getElementsByName("command")[0].value;
    var tmp = "[tmp]";
    for (var id in websites) {
        if (websites.hasOwnProperty(id)) {
            command_value = command_value.replace(defaultDocRoot,  tmp +"/web");
            command_value = command_value.replace(websites[id]["document_root"],  tmp);
            command_value = command_value.replace("/var/www/" + websites[id]["domain_name"], tmp);
        }
    }
    if (command_value.indexOf(tmp) >= 0) {
        if (add==defaultDocRoot) {
            command_value = command_value.replace(tmp + "/web", defaultDocRoot);
        }
        command_value = command_value.replace(tmp, add);

    } else {
        command_value = (command_value.trim() +  " "  + add).trim();
    }
    return command_value;
}

function AddDefautlCommandButton() {

    var docroot = "";
    var domroot = "";
    var command_value = defaultCommand;
    var isp_config_id = document.getElementsByName("isp_config_id")[0].value;

    if (isp_config_id && websites[isp_config_id]) {
        domroot = "/var/www/" + websites[isp_config_id]["domain_name"] + "";
        docroot = websites[isp_config_id]["document_root"] + "";
    }
    var p = document.createElement("p");
    p.className = webrootclass;
    if (docroot) {
        var a = document.createElement("a");
        a = document.createElement("a");
        a.innerHTML = "[Add Document Path]";
        a.onclick = function () {
            document.getElementsByName("command")[0].value = transformCommand(docroot);
            // command_value.replace(defaultDocRoot, docroot)
        };
        p.appendChild(a);
    }
    if (domroot) {
        var a2 = document.createElement("a");
        a2 = document.createElement("a");
        a2.innerHTML = "[Add Domain Path]";
        a2.onclick = function () {
            document.getElementsByName("command")[0].value = transformCommand(domroot);
            // document.getElementsByName("command")[0].value = command_value.replace(defaultDocRoot, domroot)
        };
        p.appendChild(document.createTextNode(" "));
        p.appendChild(a2);
    }
    var a3 = document.createElement("a");
    a3 = document.createElement("a");
    a3.innerHTML = "[Add Placeholder Path]";
    a3.onclick = function () {
        document.getElementsByName("command")[0].value = transformCommand(defaultDocRoot);
        // document.getElementsByName("command")[0].value = command_value.replace(defaultDocRoot, defaultDocRoot)
    };
    p.appendChild(document.createTextNode(" "));
    p.appendChild(a3);
    command_field.parentNode.insertBefore(p, command_field.nextSibling);


}

function updateFields() {
    var website = SUGAR.ui.getFormInput(myform, 'isp_config'),
        command = SUGAR.ui.getFormInput(myform, 'command'),
        isp_config_id, command_value, i;
    if (!website) return;
    isp_config_id = document.getElementsByName("isp_config_id")[0].value;

    // command_value = command.getValue();
    //
    // if (!command_value) {
    //     command_value = defaultCommand;
    // }
    // for (var id in websites) {
    //     if (websites.hasOwnProperty(id)) {
    //         var type = "domain_name";
    //         if (command_value.indexOf(websites[id]["document_root"])) {
    //             type = "document_root";
    //         }
    //         command_value = command_value.replace(websites[id]["document_root"], defaultDocRoot);
    //         command_value = command_value.replace(websites[id]["domain_name"], defaultDocRoot);
    //     }
    // }
    // var docroot = defaultDocRoot;
    // if ( websites[isp_config_id][type])
    //     docroot = websites[isp_config_id][type];
    //
    // command_value = command_value.replace(defaultDocRoot,docroot );
    // command.setValue(command_value);
    removeElementsByClass(webrootclass);

    if (websites[isp_config_id]) {
        var p = document.createElement("p");
        p.className = webrootclass;
        var s = document.createElement("strong");
        p.appendChild(s);

        s.innerHTML = mod_string('LBL_DOCUMENT_ROOT', 'ISPConfigCron') + ": " + websites[isp_config_id]["document_root"];
        command_field.parentNode.insertBefore(p, command_field.nextSibling);
    }
    AddDefautlCommandButton();
}

function removeElementsByClass(className) {
    var elements = document.getElementsByClassName(className);
    while (elements.length > 0) {
        elements[0].parentNode.removeChild(elements[0]);
    }
}

