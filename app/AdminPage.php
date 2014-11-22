<?
require_once 'UserPage.php';

class AdminPage extends UserPage {

    function __do_service() {
        if ($this->user->admin == 'Y') {
            parent::__do_service();
        } else {
            throw new ForbiddenException();
        }
    }
}