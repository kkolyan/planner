
<html>
<? include '../www/head.php' ?>
<body>

<? /** @var $this RegisterPage */?>
<?
if ($this->just_success) {
    ?>Аккаунт успешно создан, теперь вы можете <a href=".">войти</a><?
} else if ($this->invalid_key) {
    ?>Ключ недействителен или устарел. Пожалуйста обратитесь к администратору<?
} else {
    ?>
    <h4>Создать аккаунт</h4>
    <?
    if ($this->errors) {
        ?><ul><?
        foreach ($this->errors as $error) {
            ?><li><?=$error?></li><?
        }
        ?></ul><?
    }
    ?>
    <form method="post">
        <input type="hidden" name="method" value="register"/>
        <input type="hidden" name="invite_key" value="<?=esc($this->invite_key)?>"/>
        <label>
            Ник
            <input name="name" value="<?=esc($_POST['name'])?>"/>
        </label>
        <label>
            Пароль
            <input name="password" type="password"/>
        </label>
        <label>
            Тот-же пароль для верности
            <input name="password_confirm" type="password"/>
        </label>
        <input type="submit" value="Вперед"/>
    </form>
    <?
}
?>
</body>
</html>