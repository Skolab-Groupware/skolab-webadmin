{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Enter UID and password to login"}</h1>


<div class="contentform_login" style="width:40%;text-align:center;">
<form method="post">
<table>
<tr>
	<td><label for="username">{tr msg="Username:"}</label></td><td><input type="text" name="username" id="username" style="background:#f0f0f0;margin:5px;padding:4px;"/></td>
</tr>

<tr>
	<td colspan="2" align="right" width="1px"></td>
</tr>
<tr style="background:#fefefe">
	<td><label for="password">{tr msg="Password:"}</label></td><td><input type="password" name="password" id="password" style="background:#f0f0f0;margin:5px;padding:4px;"/> <input type="submit" name="login" value="{tr msg="Login"}"/></td>
</tr>

</table>
</form>
</div>
