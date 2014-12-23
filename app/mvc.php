<?
$tz = $_COOKIE['tz'];
if (!$tz) {
    $tz = 'GMT';
}
date_default_timezone_set($tz);

session_start();

require_once 'db.php';

set_mysql_timezone($tz);

require_once 'Page.php';

require_once 'AdminPage.php';

function format_date($date, $pattern) {
    $d = new DateTime($date);
    return $d->format($pattern);
}

function mapBy(&$list, $f, $vf=null) {
    $map = array();
    foreach ($list as $i) {
        $key = $f($i);
        $entry = &$map[$key];
        if (!$entry) {
            $map[$key] = array();
            $entry = &$map[$key];
        }
        if ($vf) {
            $entry[] = $vf($i);
        } else {
            $entry[] = $i;
        }
    }
    return $map;
}


abstract class HttpStatusException extends Exception {
    abstract function sendHttpStatus();
}

class ForbiddenException extends HttpStatusException {

    function sendHttpStatus() {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: text/plain")
        ?>403 Forbidden<?
    }
}

class AuthenticationRequiredException extends HttpStatusException {

    function sendHttpStatus() {
        header("Location: login.php?origin=".urlencode($_SERVER['REQUEST_URI']));
    }
}