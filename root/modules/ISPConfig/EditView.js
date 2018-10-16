var server, serverKeys, serverVals, ipData, selectedIp, optIPkeys, optIPvals, iplabel, iplabelDefaultText, iplabelTd;

function initForm(form) {
    server = SUGAR.ui.getFormInput(form, 'server_id');
    if (server) {
        serverKeys = server['menu']['options']['keys'];
        serverVals = server['menu']['options']['values'];

    }


    ipData = SUGAR.ui.getFormInput(form, 'ip_address');
    if (ipData) {
        selectedIp = ipData.getValue();
        optIPkeys = ipData['menu']['options']['keys'];
        optIPvals = ipData['menu']['options']['values'];
        iplabelTd = document.getElementsByClassName("cell-ip_address")[0];
        iplabel = iplabelTd.getElementsByTagName("span")[0];
        iplabelDefaultText = iplabel.innerHTML;
        updateServerIPs(form);
    }

}

function updatePHP(form) {
    var server = SUGAR.ui.getFormInput(form, 'server_id'),
        php = SUGAR.ui.getFormInput(form, 'php'),
        opts, init, i, k = 0, server_id, selectedPhp;
    if (!server || !php) return;

    server_id = server.getValue();
    selectedPhp = php.getValue();
    opts = {keys: [], values: []};

    if (server_id) {
        for (i = 0; i < serverVals.length; i++) {
            if (serverKeys[i] == server_id) {
                Object.keys(serverVals[i]['php']).map(function (key) {
                    k++;
                    if (!isset(init) && k == 2) init = key;
                    if (selectedPhp == key) init = key;
                    opts.keys.push(key);
                    opts.values.push(serverVals[i]['php'][key]);
                });
            }
        }
        php.setOptions(new SUGAR.ui.SelectOptions(opts));
        php.setValue(init);
    }
}


function updateServerIPs(form) {
    updatePHP(form)
    var server = SUGAR.ui.getFormInput(form, 'server_id'),
        ip = SUGAR.ui.getFormInput(form, 'ip_address'),
        php = SUGAR.ui.getFormInput(form, 'php'),
        opts, init, i, value, server_id, countip = 0;
    if (!server || !ip) return;
    server_id = server.getValue();


    opts = {keys: [], values: []};
    for (i = 0; i < optIPvals.length; i++) {
        if (!server_id || typeof(optIPvals[i]) == "string" || optIPvals[i]['server_id'] == server_id) {
            if (!isset(init)) init = optIPkeys[i + 1];
            if (selectedIp == optIPkeys[i]) init = optIPkeys[i];
            opts.keys.push(optIPkeys[i]);
            value = typeof(optIPvals[i]) == "string" ? optIPvals[i] : optIPvals[i]['name'];
            opts.values.push(value);
            if (typeof(optIPvals[i]) != "string") countip++;
        }
    }

    iplabel.innerHTML = iplabelDefaultText + (countip > 0 ? " (" + countip + " IPs )" : "");
    ip.setOptions(new SUGAR.ui.SelectOptions(opts));
    ip.setValue(init);
}


