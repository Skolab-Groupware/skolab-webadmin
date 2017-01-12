{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Email Forwarding"}</h1>
<div class="contentform">
<form method="POST">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {tr msg="Activate email forwarding (only one of vacation, forward and delivery to folder can be active at any time)"}<br />
{tr msg="Forward mail to"} <input type="text" name="address" value="{$address}" /><br />
<input type="checkbox" name="keep" value="true" {if $keep}checked{/if} /> {tr msg="Keep copy on server"}<br />
<input type="submit" name="submit" value="{tr msg="Update"}"/><br />
</form>
</div>