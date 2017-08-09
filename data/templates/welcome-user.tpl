{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{t}Welcome to the Kolab Groupware Server{/t}</h1>

<table cellspacing="10" cellpadding="10" style="background:#d0e2e6">
<tr>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/user/user.php?action=modify" class="welcomelinks"><img src="{$webserver_web_prefix}/images/preferences-system.png" border="0" alt="My settings" /><br /><b>{t}My settings{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/user/activesync.php" class="welcomelinks"><img src="{$webserver_web_prefix}/images/phone.png" border="0" alt="ActiveSync" /><br /><b>{t}ActiveSync{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/about/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/dialog-information.png" border="0" alt="About Kolab" /><br /><b>{t}About{/t}</b></a>
</td>
</tr></table>
</div>
