<?

require_once 'mvc.php';

function startsWith($str, $prefix) {
    return substr($str, 0, strlen($prefix)) === $prefix;
}

function endsWith($str, $prefix) {
    return substr($str, -strlen($prefix)) === $prefix;
}


function links($text) {
    $text = preg_replace(
        "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
        "'<a href=\"$1\" target=\"_blank\">$1</a>$4'", $text);
    return $text;
}

assert(startsWith('123456', '123'));
assert(endsWith('123456', '456'));

class AutoParams {
    function __get($name) {
        if (endsWith($name, '__sql')) {
            $base = substr($name, 0, -5);
            if (isset($this->$base)) {
                return esc_sql($this->$base);
            }
        }
        return null;
    }
}