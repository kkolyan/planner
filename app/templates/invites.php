<html>
<? include '../www/head.php' ?>
<body>
<? include 'menu.php' ?>
<? /** @var $this InvitePage */ ?>

<h4>Приглашения</h4>
<form method="post">
    <input type="hidden" name="method" value="create_invite"/>
    <label>

        <textarea name="description"></textarea>
    </label>
    <input type="submit" value="Создать"/>
</form>
<table>
    <tr>
        <th>Создано</th>
        <th>Код</th>
        <th>Описание</th>
    </tr>
    <?
    foreach ($this->invites as $invite) {
        ?>
        <tr>
            <td><?=$invite->created_at?></td>
            <td><a href="register.php?invite_key=<?=$invite->key?>"><?=$invite->key?></a></td>
            <td>
                <pre><?=esc($invite->description)?></pre>
            </td>
            <td>
                <form method="post">
                    <input type="hidden" name="method" value="cancel_invite"/>
                    <input type="hidden" name="key" value="<?=$invite->key?>"/>
                    <input type="submit" value="Отозвать"/>
                </form>
            </td>
        </tr>
        <?
    }
    ?>
</table>
</body>
</html>