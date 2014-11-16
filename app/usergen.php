<?

$password = '123123';
$salt = '$6$rounds=5000$'.mt_rand().'$';
$hash = crypt($password, $salt);
print $hash;