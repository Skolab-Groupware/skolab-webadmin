{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{tr msg="Welcome to the Kolab Groupware Server"}</h1>

<table cellspacing="10" cellpadding="10" style="background:#d0e2e6">
<tr>
<td>
<a href="/admin/user/user.php?action=modify" class="welcomelinks"><img src="/admin/images/preferences-system.png" border="0" alt="My settings" /><br /><b>{tr msg="My settings"}</b></a>
</td>
<td>
<a href="/admin/user/activesync.php" class="welcomelinks"><img src="/admin/images/phone.png" border="0" alt="ActiveSync" /><br /><b>{tr msg="ActiveSync"}</b></a>
</td>
<td>
<a href="/admin/kolab/" class="welcomelinks"><img src="/admin/images/dialog-information.png" border="0" alt="About Kolab" /><br /><b>{tr msg="About"}</b></a>
</td>
</tr></table>
</div>
