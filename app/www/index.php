<?
date_default_timezone_set('Europe/Moscow');

require_once '../db.php';
require_once '../App.php';

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

try {
    $app = new App();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $params = new stdClass();
        foreach ($_POST as $k => $v) {
            $params->$k = $v;
        }
        $m = $params->method;

        $app->$m($params);

        header('Location: .');
    }

    $app->__prepare_to_show();

    include '../templates/tasks.php';

} catch (Exception $e) {
    ?><pre><?= $e ?></pre><?
}
