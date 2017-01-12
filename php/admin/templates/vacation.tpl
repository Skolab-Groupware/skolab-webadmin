{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Vacation Notification"}</h1>
<div class="contentform">
<form method="post">
<input type="checkbox" name="active" value="true" {if $active}checked{/if} /> {tr msg="Activate vacation notification (only one of vacation, forward and delivery to folder can be active at any time)"}<br />
<textarea name="text" cols="80" rows="10">{$text|escape}</textarea><br />
{tr msg="Resend notification only after"} <input type="text" size="5" name="days" value="{$days}" /> {tr msg="days"}<br />
{tr msg="Send responses for these addresses:"}<br />
<textarea name="addresses" cols="80" rows="3">
{section name="id" loop="$addresses"}
{$addresses[id]}
{/section}
</textarea><br />
{tr msg="(one address per line)"}<br />
<input type="checkbox" name="reacttospam" value="true" {if $reacttospam}checked{/if} /> {tr msg="Do not send vacation replies to spam messages"}<br />
{tr msg="Only react to mail coming from domain"} <input type="text" name="maildomain" value="{$maildomain}" /> {tr msg="(leave empty for all domains)"}<br />
<input type="submit" name="submit" value="{tr msg="Update"}"/><br />
</form>
</div>
