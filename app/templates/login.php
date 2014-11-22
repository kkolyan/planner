<html>
<? include '../www/head.php' ?>
<body>
<? include 'menu.php' ?>
<form method="post">
    <input type="hidden" name="method" value="log_in"/>
    <label>
        Ник
        <input name="name"/>
    </label>
    <label>
        Пароль
        <input type="password" name="password"/>
    </label>
    <input type="submit" value="Войти"/>
</form>
</body>