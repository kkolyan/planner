<?

require_once '../db.php';
require_once '../email.php';
require_once '../config.php';

try {
    $instance = get_config_option('general.instance');
} catch (Exception $e) {
    $instance = 'Undefined';
}

try {
    $dump = get_dump();

    send('<kkolyankkolyan@gmail.com>', "Backup: $instance", $dump);
} catch (Exception $e) {
    send('<kkolyankkolyan@gmail.com>', "Error on $instance", "$e");
}