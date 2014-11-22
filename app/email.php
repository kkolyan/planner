<?
require_once "Mail.php";

function send($to, $subject, $body, $from = false) {

    $cfg = get_config('email.');

    if (!$from) {
        $from = $cfg->default_from;
    }

    $headers = array(
        'From' => $from,
        'To' => $to,
        'Subject' => $subject
    );


    $smtp = Mail::factory('smtp', array(
        'host' => 'ssl://smtp.gmail.com',
        'port' => '465',
        'auth' => true,
        'username' => $cfg->username,
        'password' => $cfg->password
    ));

    $mail = $smtp->send($to, $headers, $body);

    if (PEAR::isError($mail)) {
        throw new Exception($mail->getMessage());
    }
    return true;
}