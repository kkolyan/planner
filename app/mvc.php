<?
date_default_timezone_set('Europe/Moscow');

session_start();

require_once 'db.php';

require_once 'Page.php';

function format_date($date, $pattern) {
    $d = new DateTime($date);
    return $d->format($pattern);
}

function mapBy(&$list,$f) {
    $map = array();
    foreach ($list as $i) {
        $key = $f($i);
        $entry = &$map[$key];
        if (!$entry) {
            $map[$key] = array();
            $entry = &$map[$key];
        }
        $entry[] = $i;
    }
    return $map;
}
