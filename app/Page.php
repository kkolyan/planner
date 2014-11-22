<?

require_once 'utils.php';

class Page {
    public $user;

    function __after_post() {
        $str = 'Location: ' . $_SERVER['REQUEST_URI'];
        header($str);
    }

    function __do_get() {
    }

    function  __do_post() {
        $params = new AutoParams();
        foreach ($_POST as $k => $v) {
            $params->$k = $v;
        }
        $m = $params->method;

        $this->$m($params);
    }

    function  __do_service() {

        foreach ($_GET as $k => $v) {
            $this->$k = $v;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !startsWith($_POST['method'], '__')) {
            $this->__do_post();

            $this->__after_post();
        } else {
            $this->__do_get();
        }
    }

    function  __prepare() {
    }

    function __construct() {

        try {
            $this->__prepare();

            $this->__do_service();

        } catch (HttpStatusException $e) {
            $e->sendHttpStatus();
        } catch (Exception $e) {
            ?><pre><?= $e ?></pre>Code: <?=$e->getCode()?><?
        }
    }
}