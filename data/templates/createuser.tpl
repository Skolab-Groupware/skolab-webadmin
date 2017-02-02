{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<form name="user" method="post">
<table class="contentform">
<tr>
	<th>{tr msg="Attribute"}</th>
	<th>{tr msg="Value"}</th>
	<th>{tr msg="Comment"}</th>
</tr>
<tr>
	<td>{tr msg="First Name"}</td>
	<td><input name="firstname" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td>{tr msg="Required"}</td>
</tr>
<tr>
	<td>{tr msg="Middle Name"}</td>
	<td><input name="middlename" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Last Name"}</td>
	<td><input name="lastname" type="text" value="" size="50" onfocus="javascript:this.select()" /></td>
	<td>{tr msg="Required"}</td>
</tr>
<tr>
	<td>{tr msg="Password"}</td>
	<td><input name="password_0" type="password" value=""  size="50" onfocus="javascript:this.select()" />
	</td><td>{tr msg="Required"}</td>
</tr>
<tr>
	<td>{tr msg="Verify Password"}</td>
	<td><input name="password_1" type="password" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{tr msg="Required"}</td>
</tr>
<tr>
	<td>{tr msg="Primary Email Address"}</td>
	<td><input name="mail_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{tr msg="Required, non volatile"}</td>
</tr>
<tr>
	<td>{tr msg="Title"}</td>
	<td><input name="title_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Email Alias"}</td>
	<td><input name="alias_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Organisation"}</td>
	<td><input name="o_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Organisational Unit"}</td>
	<td><input name="ou_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Room Number"}</td>
	<td><input name="roomNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Street Address"}</td>
	<td><input name="street_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Postbox"}</td>
	<td><input name="postOfficeBox_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Postal Code"}</td>
	<td><input name="postalCode_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="City"}</td>
	<td><input name="l_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Country"}</td>
	<td><input name="c_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Telephone Number"}</td>
	<td><input name="telephoneNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Fax Number"}</td>
	<td><input name="facsimileTelephoneNumber_0" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td></td>
</tr>
<tr>
	<td>{tr msg="Addressbook"}</td>
	<td><input name="visible" type="checkbox" checked  /></td>
	<td>{tr msg="check here to make this users address <br/> visible in the address book"}</td>
</tr>
<tr>
	<td>{tr msg="User Quota in KB"}</td>
	<td><input name="userquota" type="text" value=""  size="50" onfocus="javascript:this.select()" /></td>
	<td>{tr msg="Leave blank for unlimited"}</td>
</tr>
</table>
<input type="submit" name="submit_user" value="{tr msg="Submit"}"  /><input name="action" type="hidden" value="firstsave"  /></form>
