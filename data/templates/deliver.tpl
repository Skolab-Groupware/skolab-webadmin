{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Email Delivery"}</h1>
<div class="contentform">
<form method="POST">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {tr msg="Activate delivery to folder"}<br />
{tr msg="Deliver regular mail to folder"} <input type="text" name="inbox" value="{$inbox}" /><br />
<input type="submit" name="submit" value="{tr msg="Update"}"/><br />
</form>
</div>
