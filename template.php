<!doctype html>
<meta charset=utf-8>
<title>GG2 Haxxy Rewards</title>
<link rel=stylesheet href=style.css>

<div id=head><img src="http://static.ganggarrison.com/GG2ForumLogo.png" alt="" id=logo><img src="http://static.ganggarrison.com/Themes/GG2/images/smflogo.gif" alt="" id=smflogo></div>
<div id=desc>
    <p>This is where you can manage your Haxxy rewards.</p>
<?php
if ($loggedin && !$gg2_login):
?>
    <p>Here are your <em>personal</em> reward details, to add to <code>gg2.ini</code>:</p>
    <p><code>[Haxxy]<br>RewardId=<?=$user_id?><br>RewardKey=<?=$secret_key?></code></p>
    <p>Please keep your reward login data safe and don't pass it on - if we find it floating around the internet, we will disable it and you will lose your rewards.</p>
<?php
endif;
?>
</div>
<form method=post>
<?php
    // IE workaround
    foreach ($_GET as $param => $value):
?>
        <input type=hidden name="<?=htmlspecialchars($param)?>" value="<?=htmlspecialchars($value)?>">
<?php
    endforeach;
?>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>description</th>
                <th>enabled</th>
            </tr>
        </thead>
        <tbody>
<?php
    if ($message):
?>
    <?=nl2br(htmlspecialchars($message))?>
<?php
    endif;
?>
<?php
    if (!$loggedin):
?>
        <tr>
            <td colspan=3>You need to <a href="/forums/index.php?action=login">log in to the forums</a> to select rewards.</td>
        </tr>
<?php
    elseif (empty($rewards)):
?>
        <tr>
            <td colspan=3>You have no rewards.</td>
        </tr>
<?php
    else:
        foreach ($rewards as $reward):
?>
            <tr>
                <td class=id><?=htmlspecialchars($reward['id'])?></td>
                <td class=description><?=htmlspecialchars($reward['description'])?></td>
                <td class=enabled><input type=checkbox name="<?=$reward['field_name']?>" value="enabled" <?=$reward['enabled'] ? 'checked' : ''?>></td>
            </tr>
<?php
        endforeach;
    endif;
?>
        </tbody>
    </table>
<?php
    if ($loggedin and !empty($rewards)):
?>
    <input type=submit value="Update">
<?php
    endif;
?>
</form>
