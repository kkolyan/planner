
function toggle(q) {
    var o = document.getElementById(q);
    if (o.className == 'state-hidden') {
        o.className = 'state-shown';
    } else {
        o.className = 'state-hidden';
    }
}