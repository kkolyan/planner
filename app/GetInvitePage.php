<?
require_once 'mvc.php';

class GetInvitePage extends Page {
    public $key;

    function __do_get() {
        if ($this->user->admin == 'Y') {
            update('delete from planner_invite where created_at < unix_timestamp(date_sub(now(), interval 7 day))');

            $this->key = preg_replace('/[-{}]/','', com_create_guid());


            insert("insert into planner_invite (`key`) values ('$this->key')");

            include 'templates/get_invite.php';
        } else {
            header("HTTP/1.1 403 Forbidden");
        }
    }
}