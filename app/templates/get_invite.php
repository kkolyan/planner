<html>
<body>
<a id="registerLink" href="register.php?invite_key=<?=
/** @var $this GetInvitePage */
$this->key?>" onshow="">Ссылка Регистрации</a>
<script>
    var a = document.getElementById('registerLink');
    a.innerHTML = a.href;
</script>
</body>
</html>