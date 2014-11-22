<?
if ($this->user) {
    if ($this->user->admin == 'Y') {
        ?>
        <a href=".">Главная</a>
        <a href="invites.php">Приглашения</a>
        <a href="email_backup.php">Бэкап</a>
    <?
    }
    ?>
    <form method="post" action="login.php?origin=<?=urlencode($_SERVER['REQUEST_URI'])?>">
        <input type="hidden" name="method" value="log_out"/>
        <b><?= $this->user->name ?></b>
        <input type="submit" value="Выйти"/>
    </form>
    <?
}