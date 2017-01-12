{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Enter UID and password to login"}</h1>
<div class="contentform">
<form method="post">
<table>
<tr>
	<td><label for="username">{tr msg="Username:"}</label></td><td><input type="text" name="username" id="username"/></td>
</tr>
<tr>
	<td><label for="password">{tr msg="Password:"}</label></td><td><input type="password" name="password" id="password"/></td>
</tr>
<tr>
	<td colspan="2" align="right"><input type="submit" name="login" value="{tr msg="Login"}"/></td>
</tr>
</table>
</form>
</div>
