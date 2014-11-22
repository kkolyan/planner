<?
require_once 'mvc.php';

class InvitePage extends AdminPage {
    public $key;
    public $invites;

    function __do_get() {
        $this->invites = select('select * from planner_invite order by created_at desc');

        include 'templates/invites.php';
    }

    function create_invite($params) {
        $this->key = preg_replace('/[-{}]/','', com_create_guid());
        insert("insert into planner_invite (`key`, description) values ('$this->key','$params->description__sql')");
    }

    function cancel_invite($params) {
        update("delete from planner_invite where `key` = '$params->key__sql'");
    }
}