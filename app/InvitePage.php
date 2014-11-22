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
        $this->key = preg_replace('/[-{}]/','', $this->create_guid());
        insert("insert into planner_invite (`key`, description) values ('$this->key','$params->description__sql')");
    }

    private function create_guid() {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }

    function cancel_invite($params) {
        update("delete from planner_invite where `key` = '$params->key__sql'");
    }
}