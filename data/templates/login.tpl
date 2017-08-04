{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{t}Enter UID and password to login{/t}</h1>


<div class="contentform_login" style="width:40%;text-align:center;">
<form method="post">
<table>
<tr style="background:#fefefe">
	<td><label for="username">{t}Username:{/t}</label></td><td style="text-align: left;"><input type="text" name="username" id="username" style="background:#f0f0f0;margin:5px;padding:4px;"/></td>
</tr>

<!-- include some vertical space ... -->
<tr>
	<td colspan="2" align="right" width="1px"></td>
</tr>

<tr style="background:#fefefe">
	<td><label for="password">{t}Password:{/t}</label></td><td style="text-align: left;"><input type="password" name="password" id="password" style="background:#f0f0f0;margin:5px;padding:4px;"/> <input type="submit" name="login" value="{t}Login{/t}"/></td>
</tr>

</table>
</form>
</div>
