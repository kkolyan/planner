
function toggle(q) {
    var o = document.getElementById(q);
    if (o.className == 'state-hidden') {
        o.className = 'state-shown';
    } else {
        o.className = 'state-hidden';
    }
    var toggles = getToggles();
    toggles[q] = o.className;
    setToggles(toggles);
}

function update_toggles() {

    var toggles = getToggles();

    for (var key in toggles) {
        var element = document.getElementById(key);
        if (element) {
            element.className = toggles[key];
        }
    }

    if (!toggles) {
        var expires = new Date( new Date().getTime() + 3000*24*60*60*1000 );
        document.cookie = 'toggles=;expires='+expires.toUTCString();
    }
}

function getToggles() {
    var togglesC = getCookie('toggles');
    if (!togglesC) {
        return {};
    }
    return JSON.parse(togglesC);
}

function setToggles(toggles) {
    var value = JSON.stringify(toggles);
    var expires = new Date( new Date().getTime() + 3000*24*60*60*1000 );
    document.cookie = 'toggles='+encodeURIComponent(value)+';expires='+expires.toUTCString();
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
