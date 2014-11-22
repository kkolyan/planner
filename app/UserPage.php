<?
require_once 'Page.php';

class UserPage extends Page {

    function __load_user() {

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

    function  __prepare() {
        $this->__load_user();
    }

    function  __do_service() {
        if ($this->user) {
            parent::__do_service();
        } else {
            throw new AuthenticationRequiredException();
        }
    }


}