<?

class Page {
    public $user;

    function __after_post() {
        $str = 'Location: ' . $_SERVER['REQUEST_URI'];
        header($str);
    }

    function __do_get() {
    }

    function __prepare() {

        /** @var $app Page */
        $user_id = $_SESSION['user_id'];
        if ($user_id) {
            $users = select("select id, `name`, admin from planner_user where id = $user_id");
            $this->user = $users[0];
        }
        if (!$this->user) {
            unset($_SESSION['user_id']);
        }
    }

    function __construct() {

        try {
            $this->__prepare();

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $params = new stdClass();
                foreach ($_POST as $k => $v) {
                    $params->$k = $v;
                }
                $m = $params->method;

                $this->$m($params);

                $this->__after_post();
            } else {
                $this->__do_get();
            }

        } catch (Exception $e) {
            ?><pre><?= $e ?></pre>Code: <?=$e->getCode()?><?
        }
    }
}