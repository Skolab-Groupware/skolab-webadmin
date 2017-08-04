{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{t}Vacation Notification{/t}</h1>
<div class="contentform">
<form method="post">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {t}Activate vacation notification{/t}<br />
<textarea name="text" cols="80" rows="10">{$text|escape}</textarea><br />
{t}Resend notification only after{/t} <input type="text" size="5" name="days" value="{$days}" /> {t}days{/t}<br />
{t}Send responses for these addresses:{/t}<br />
<textarea name="addresses" cols="80" rows="3">
{section name="id" loop="$addresses{/t}
{$addresses[id]}
{/section}
</textarea><br />
{t}(one address per line){/t}<br />
<input type="checkbox" name="reacttospam" value="true" {if $reacttospam}checked{/if} /> {t}Do not send vacation replies to spam messages{/t}<br />
{t}Only react to mail coming from domain{/t} <input type="text" name="maildomain" value="{$maildomain}" /> {t}(leave empty for all domains){/t}<br />
<input type="submit" name="submit" value="{t}Update{/t}"/><br />
</form>
</div>
