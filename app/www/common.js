
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

var tags = [];

function register_tag(name) {
    tags.push(name);
}

function toggle_tags_except(name) {
    for (var i = 0; i < tags.length; i ++) {
        if (tags[i] != name) {
            toggle_tag(tags[i]);
        }
    }
}

function toggle_tag(name) {

    var tasks;
    var taskClassToSet;

    var buttonId = 'tag-'+name;
    var button = document.getElementById(buttonId);
    if (button.className == 'task-tag-visible') {
        button.className = 'task-tag-hidden';
        tasks = document.getElementsByClassName('task-visible');
        taskClassToSet = 'task-hidden';
    } else {
        button.className = 'task-tag-visible';
        tasks = document.getElementsByClassName('task-hidden');
        taskClassToSet = 'task-visible';
    }
    var tasksCopy = [];
    var taskN;
    for (taskN = 0; taskN < tasks.length; taskN ++) {
        tasksCopy.push(tasks[taskN]);
    }
    for (taskN = 0; taskN < tasksCopy.length; taskN ++) {
        var task = tasksCopy[taskN];
        if (true) {
            if (name == '') {
                if (!/\[[A-z0-9А-я ]*\]/.test(task.title)) {
                    task.className = taskClassToSet;
                }
            } else {
                if (task.title.indexOf('['+name+']') >= 0) {
                    task.className = taskClassToSet;
                }
            }
        }
    }

    var toggles = getUISetting('toggles');
    toggles[buttonId] = button.className;

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

    for (var i = 0; i < tags.length; i ++) {
        toggle_tag(tags[i]);
        toggle_tag(tags[i]);
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
