{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{tr msg="Welcome to the Kolab administration interface"}</h1>
</div>
<div id="errorcontent">
<div id="errorheader">{tr msg="NOTE:"}</div>
{tr msg="No account is configured to receive mail for administrative addresses. If you have not yet created an account for this, "}
<a href="{$topdir}/user/user.php?action=create" target="_blank">{tr msg="please do so"}</a> {tr msg="and then go"}
<a href="{$topdir}/service/#systemaliasconf">{tr msg="here"}</a> {tr msg="to set up forwarding of mail to administrative email addresses."}
</div>
