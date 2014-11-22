<?
require_once 'Page.php';

class LoginPage extends Page {

    public $origin;

    function log_out($params) {
        unset($_SESSION['user_id']);
    }

    function log_in($params) {
        $name = esc_sql($params->name);
        $password = $params->password;
        if (!$password) {
            return;
        }
        $r = select("select id, pwd_hash from planner_user where `name` = '$name'");
        if ($r && $r[0]) {
            $hash = crypt($password, $r[0]->pwd_hash);
            if ($hash == $r[0]->pwd_hash) {
                $_SESSION['user_id'] = $r[0]->id;
            }
        }
    }

    function __after_post() {
        if (!$this->origin) {
            header('Location: .');
        } else {
            header('Location: '.$this->origin);
        }
    }

    function __do_get() {
        parent::__do_get();

        include 'templates/login.php';
    }


}