<?

require_once 'config.php';

$connection = false;

$mysql_timezone = false;

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

function set_mysql_timezone($tz) {
    global $mysql_timezone;
    $mysql_timezone = $tz;
}

function ensure_conected() {

    global $mysql_timezone;
    global $connection;
    if (!$connection) {
        $config = load_mysql_config();
        $connection = mysql_pconnect($config->host, $config->user, $config->password);
        mysql_select_db("planner");
    }
    mysql_query('set names utf8');
    mysql_query("set time_zone = '".esc_sql($mysql_timezone)."'");

    return $connection;
}

function load_mysql_config() {
    return get_config('mysql.');
}

function ensure_backup($pattern) {
    $date = date($pattern);
    $dir = "../../backups";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    $file = "$dir/planner.$date.sql";

    if (!file_exists($file)) {
        $dump = get_dump();
        file_put_contents($file, $dump);
    }
}

function get_dump() {
    $config = load_mysql_config();
    return shell_exec("mysqldump --user=$config->user --password=$config->password --host=$config->host planner");
}

function esc_sql($str) {
    $con = ensure_conected();
    if ($str === '') {
        return $str;
    }
    $r = mysql_real_escape_string($str, $con);
    if (!$r) {
        throw new Exception();
    }
    return $r;
}

function selectRow($q) {
    $rows = select($q);
    $row = $rows[0];
    return $row;
}

function selectCell($q) {
    $row = selectRow($q);
    $cells = get_object_vars($row);
    $labels = array_keys($cells);
    return $cells[$labels[0]];
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
    return mysql_affected_rows();
}

function err($q) {
    if ($error = mysql_error()) {
        throw new Exception("Failed to execute '$q' due to: $error", mysql_errno());
    }
}