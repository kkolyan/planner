
function toggle(q) {
    var o = document.getElementById(q);
    if (o.className == 'state-hidden') {
        o.className = 'state-shown';
    } else {
        o.className = 'state-hidden';
    }
    var toggles = getUISetting('toggles');
    toggles[q] = o.className;
    saveUISettings();
}

function update_toggles() {

    var toggles = getUISetting('toggles');
    if (toggles) {
        for (var key in toggles) {
            var element = document.getElementById(key);
            if (element) {
                element.className = toggles[key];
            }
        }
    }

}

var settings;

function getUISetting(name) {
    ensureUISettings();
    var setting = settings[name];
    if (!setting) {
        setting = settings[name] = {};
    }
    return setting;
}

function ensureUISettings() {
    if (!settings) {
        var value = getCookie('ui-settings');
        if (!value) {
            settings = {};
        } else {
            settings = JSON.parse(value);
        }
    }
    return settings;
}

function saveUISettings() {
    var value = JSON.stringify(settings);
    var expires = new Date( new Date().getTime() + 3000*24*60*60*1000 );
    document.cookie = 'ui-settings='+encodeURIComponent(value)+';expires='+expires.toUTCString();
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
