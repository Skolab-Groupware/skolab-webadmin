{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{t}Welcome to the Kolab administration interface{/t}</h1>
</div>
<div id="errorcontent">
<div id="errorheader">{t}NOTE:{/t}</div>
{t}No account is configured to receive mail for administrative addresses. If you have not yet created an account for this, {/t}
<a href="{$topdir}/user/user.php?action=create" target="_blank">{t}please do so{/t}</a> {t}and then go{/t}
<a href="{$topdir}/settings/#systemaliasconf">{t}here{/t}</a> {t}to set up forwarding of mail to administrative email addresses.{/t}
</div>
