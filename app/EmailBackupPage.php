<?

require_once 'AdminPage.php';

class EmailBackupPage extends AdminPage {
    function __do_get() {
        include 'templates/email_backup.php';
    }

    function backup($params) {
        include_once 'scripts/email_backup.php';
    }

}