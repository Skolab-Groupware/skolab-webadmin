{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{t}Email Forwarding{/t}</h1>
<div class="contentform">
<form method="POST">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {t}Activate email forwarding{/t}<br />
{t}Forward mail to{/t} <input type="text" name="address" value="{$address}" /><br />
<input type="checkbox" name="keep" value="true" {if $keep}checked{/if} /> {t}Keep copy on server{/t}<br />
<input type="submit" name="submit" value="{t}Update{/t}"/><br />
</form>
</div>
