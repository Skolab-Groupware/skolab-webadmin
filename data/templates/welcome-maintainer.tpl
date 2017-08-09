{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{t}Welcome to the Skolab Groupware Server Maintenance{/t}</h1>

<table cellspacing="10" cellpadding="10" style="background:#d0e2e6">
<tr>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/user/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/system-users.png" border="0" alt="Manage Users" /><br /><b>{t}Manage Users{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/addressbook/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/office-address-book.png" border="0" alt="Addressbook" /><br /><b>{t}Addressbook{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/sharedfolder/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/preferences-system-network-sharing.png" border="0" alt="Shared Folders" /><br /><b>{t}Shared Folders{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/distributionlist/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/list.png" border="0" alt="Distribution Lists" /><br /><b>{t}Distribution Lists{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$murl}" class="welcomelinks"><img src="{$webserver_web_prefix}/images/book.png" border="0" alt="Manage Maintainers" /><br /><b>{t}Maintainers{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/domainmaintainer/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/book2.png" border="0" alt="Domain Maintainers" /><br /><b>{t}Domain Maintainers{/t}</b></a>
</td>
<td style="text-align: center;">
<a href="{$webserver_web_prefix}/about/" class="welcomelinks"><img src="{$webserver_web_prefix}/images/dialog-information.png" border="0" alt="About Skolab" /><br /><b>{t}About{/t}</b></a>
</td>
</tr></table>
</div>
