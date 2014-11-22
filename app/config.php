<?

require_once 'utils.php';

$configs = array();

function ensure_config($file) {
    global $configs;
    if (!isset($configs[$file])) {
        $configs[$file] = parse_ini_file($file);
    }
    return $configs[$file];
}

function get_config($prefix='', $file='config.ini') {
    $config = ensure_config($file);

    $o = new ArrayObject();
    foreach ($config as $k => $v) {
        if (startsWith($k, $prefix)) {
            $op = substr($k, strlen($prefix));
            $o->$op = $v;
            $o[$op] = $v;
        }
    }
    return $o;
}

function get_config_option($option, $file='config.ini') {
    $config = ensure_config($file);
    return $config[$option];
}