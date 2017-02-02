{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{tr msg="Welcome to the Kolab Groupware Server Maintenance"}</h1>

<table cellspacing="10" cellpadding="10" style="background:#d0e2e6">
<tr>
<td>
<a href="/admin/user/" class="welcomelinks"><img src="/admin/images/system-users.png" border="0" alt="Manage Users" /><br /><b>{tr msg="Manage Users"}</b></a>
</td>
<td>
<a href="/admin/addressbook/" class="welcomelinks"><img src="/admin/images/office-address-book.png" border="0" alt="Addressbook" /><br /><b>{tr msg="Addressbook"}</b></a>
</td>
<td>
<a href="/admin/sharedfolder/" class="welcomelinks"><img src="/admin/images/preferences-system-network-sharing.png" border="0" alt="Shared Folders" /><br /><b>{tr msg="Shared Folders"}</b></a>
</td>
<td>
<a href="/admin/distributionlist/" class="welcomelinks"><img src="/admin/images/list.png" border="0" alt="Distribution Lists" /><br /><b>{tr msg="Distribution Lists"}</b></a>
</td>
<td>
<a href="{$murl}" class="welcomelinks"><img src="/admin/images/book.png" border="0" alt="Manage Maintainers" /><br /><b>{tr msg="Maintainers"}</b></a>
</td>
<td>
<a href="/admin/domainmaintainer/" class="welcomelinks"><img src="/admin/images/book2.png" border="0" alt="Domain Maintainers" /><br /><b>{tr msg="Domain Maintainers"}</b></a>
</td>
<td>
<a href="/admin/kolab/" class="welcomelinks"><img src="/admin/images/dialog-information.png" border="0" alt="About Kolab" /><br /><b>{tr msg="About"}</b></a>
</td>
</tr></table>
</div>
