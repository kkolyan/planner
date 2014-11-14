<?

$connected = false;

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

function ensure_conected() {

    global $connected;
    if ($connected) {
        return;
    }
    $config = parse_ini_file('config.ini');
    mysql_pconnect($config['mysql.host'], $config['mysql.user'], $config['mysql.password']);
    mysql_select_db("planner");
    $connected = true;
}

function select($q) {
    ensure_conected();
    $rows = array();
    $rs = mysql_query($q);
    err($q);
    while ($row = mysql_fetch_object($rs)) {
        $rows[] = $row;
    }
    return $rows;
}

function insert($q) {
    ensure_conected();
    mysql_query($q);
    err($q);
    return mysql_insert_id();
}

function update($q) {
    ensure_conected();
    mysql_query($q);
    err($q);
}

function err($q) {
    if ($error = mysql_error()) {
        throw new Exception("Failed to execute '$q' due to: $error");
    }
}