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
    $config = load_mysql_config();
    mysql_pconnect($config->host, $config->user, $config->password);
    mysql_select_db("planner");
    $connected = true;
}

function load_mysql_config() {
    $o = new stdClass();
    $config = parse_ini_file('config.ini');
    $o->host = $config['mysql.host'];
    $o->user = $config['mysql.user'];
    $o->password = $config['mysql.password'];
    return $o;
}

function ensure_backup($pattern) {
    $date = date($pattern);
    $dir = "../../backups";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    $file = "$dir/planner.$date.sql";

    if (!file_exists($file)) {
        $config = load_mysql_config();
        $dump = shell_exec("mysqldump --user=$config->user --password=$config->password --host=$config->host planner");
        file_put_contents($file, $dump);
    }
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