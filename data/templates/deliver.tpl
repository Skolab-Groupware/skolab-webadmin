{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{t}Email Delivery{/t}</h1>
<div class="contentform">
<form method="POST">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {t}Activate delivery to folder{/t}<br />
{t}Deliver regular mail to folder{/t} <input type="text" name="inbox" value="{$inbox}" /><br />
<input type="submit" name="submit" value="{t}Update{/t}"/><br />
</form>
</div>
