{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<form name="user" method="post">
<table class="contentform">
<tr>
	<th>{t}Attribute{/t}</th>
	<th>{t}Value{/t}</th>
	<th>{t}Comment{/t}</th>
</tr>
<tr>
	<td>{t}First Name{/t}</td>
	<td><input name="firstname" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td>{t}Required{/t}</td>
</tr>
<tr>
	<td>{t}Middle Name{/t}</td>
	<td><input name="middlename" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Last Name{/t}</td>
	<td><input name="lastname" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td>{t}Required{/t}</td>
</tr>
<tr>
	<td>{t}Password{/t}</td>
	<td><input name="password_0" type="password" value=""  size="50" onfocus="javascript:this.select()" />
	</td><td>{t}Required{/t}</td>
</tr>
<tr>
	<td>{t}Verify Password{/t}</td>
	<td><input name="password_1" type="password" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{t}Required{/t}</td>
</tr>
<tr>
	<td>{t}Primary Email Address{/t}</td>
	<td><input name="mail_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{t}Required, non volatile{/t}</td>
</tr>
<tr>
	<td>{t}Title{/t}</td>
	<td><input name="title_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Email Alias{/t}</td>
	<td><input name="alias_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Organisation{/t}</td>
	<td><input name="o_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Organisational Unit{/t}</td>
	<td><input name="ou_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Room Number{/t}</td>
	<td><input name="roomNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Street Address{/t}</td>
	<td><input name="street_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Postbox{/t}</td>
	<td><input name="postOfficeBox_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Postal Code{/t}</td>
	<td><input name="postalCode_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}City{/t}</td>
	<td><input name="l_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Country{/t}</td>
	<td><input name="c_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Telephone Number{/t}</td>
	<td><input name="telephoneNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Fax Number{/t}</td>
	<td><input name="facsimileTelephoneNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{t}Addressbook{/t}</td>
	<td><input name="visible" type="checkbox" checked  /></td>
	<td>{t}check here to make this users address <br/> visible in the address book{/t}</td>
</tr>
<tr>
	<td>{t}User Quota in MB{/t}</td>
	<td><input name="userquota" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{t}Leave blank for unlimited{/t}</td>
</tr>
</table>
<input type="submit" name="submit_user" value="{t}Submit{/t}"  /><input name="action" type="hidden" value="firstsave"  /></form>
