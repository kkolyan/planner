<?
require_once 'mvc.php';

class RegisterPage extends Page {

    public $invite_key;
    public $invalid_key;
    public $errors = array();
    public $just_success;

    private $redirect_to;

    function __do_get() {

        if (isset($_GET['success'])) {
            $this->just_success = true;
        } else {
            $this->invite_key = $_GET['invite_key'];

            if (!$this->is_key_valid($this->invite_key)) {
                $this->invalid_key = true;
            }
        }
        include 'templates/register.php';
    }

    private function is_key_valid($key) {
        $count = selectCell("select count(*) from planner_invite where `key` = '".esc_sql($key)."'");
        return $count > 0;
    }

    function register($params) {

        $this->invite_key = $params->invite_key;
        $min_name_length = 3;
        if (strlen($params->name) < $min_name_length) {
            $this->errors[] = "Ник должен содержать по крайней мере $min_name_length символа";
        }
        if (!preg_match('/^[A-Za-z0-9_]*$/', $params->name)) {
            $this->errors[] = 'Ник должен состоять только из латиницы, цифр и "_"';
        }
        $min_password_length = 6;
        if (strlen($params->password) < $min_password_length) {
            $this->errors[] = "Пароль должен содержать по крайней мере $min_password_length символов, иначе его легко подберут";
        }
        if ($params->password != $params->password) {
            $this->errors[] = 'Введенные пароли не совпадают';
        }
        if (!$this->errors) {
            if ($this->is_key_valid($params->invite_key)) {
                $name = esc_sql($params->name);
                $salt = '$6$rounds=5000$'.mt_rand().'$';
                $hash = esc_sql(crypt($params->password, $salt));
                try {
                    insert("INSERT INTO planner_user (`name`, pwd_hash) VALUES ('$name', '$hash')");
                    update("delete from planner_invite where `key` = '".esc_sql($params->invite_key)."'");
                    $this->redirect_to = 'register.php?success';
                } catch (Exception $e) {
                    if ($e->getCode() == 1062) {
                        $this->errors[] = 'Такой ник уже кем-то используется';
                    }
                }
            } else {
                // it seems key is expired. reload page.
                header('Location: '.$_SERVER['REQUEST_URI']);
            }
        }
    }

    function __after_post() {
        if ($this->redirect_to) {
            header("Location: $this->redirect_to");
        } else {
            include 'templates/register.php';
        }
    }
}