{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Kolab Server Settings"}</h1>

{if count($systemaliasconf)>0 }
<a name="systemaliasconf"></a>
<h2>{tr msg="Administrative email addresses"}</h2>
<div class="contentsimple">
<p>{tr msg="You have not yet set up a receiving account for the administrative email addresses hostmaster@yourdomain.tld, postmaster@yourdomain.tld, MAILER-DAEMON@yourdomain.tld, abuse@yourdomain.tld and virusalert@yourdomain.tld. Enter the email address of a kolab account below and press the button to create a distribution list for each of those addresses. Later you can add or remove people from the lists like any other distribution list"}</p>
{section name=id loop=$systemaliasconf}
<div class="contentform">
<form id="systemalias_{$systemaliasconf[id].n}" method="post" action="">
<div>
{tr msg="Email address of account that should receive administrative mail for domain "}  {$systemaliasconf[id].domain|escape:html}:
<input type="text" name="systemaliasmail_{$systemaliasconf[id].n}" size="80"  value="{$systemaliasmail[id]|escape:"html"}" /><br/>
<div class="align_right"><input type="submit" name="submitsystemalias_{$systemaliasconf[id].n}" value="{tr msg="Create Distribution Lists"}" /></div>
</div>
</form>
</div>
{/section}
<br />
</div>
{/if}

<h2>{tr msg="Enable or Disable individual Services"}</h2>
<form id="serviceform" method="post" action="">
<div>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Service"}</th><th>{tr msg="Enabled"}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].name|escape:"html"}</td>
	   <td class="actioncell"><input type="checkbox" name="{$entries[id].service}" {if $entries[id].enabled == 'true' }checked="checked"{/if} /></td>
	</tr>
{/section}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"> </td><td class="actioncell"><input type="submit" name="submitservices" value="{tr msg="Update"}" /></td>
	</tr>
</table>
</div>
</form>
<h2>{tr msg="Quota settings"}</h2>
<div class="contentform">
<form id="quotawarnform" method="post" action="">
<div>
<br />
{tr msg="Warn users when they have used"} <input name="quotawarn" size="3"  value="{$quotawarn|escape:"html"}" /> {tr msg="% of their quota"}<br />
<div class="align_right"><input type="submit" name="submitquotawarn" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<h2>{tr msg="Free/Busy settings"}</h2>
<div class="contentform">
<form id="httpallowunauthfbform" method="post" action="">
<div>
<br />
<input type="checkbox" name="httpallowunauthfb" {if $httpallowunauthfb == 'true' }checked="checked"{/if} />
{tr msg="Allow unauthenticated downloading of Free/Busy information"}
<br />
<div class="align_right"><input type="submit" name="submithttpallowunauthfb" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<div class="contentform">
<form id="freebusypastform" method="post" action="">
<div>
<br />
{tr msg="When creating free/busy lists, include data from"} <input name="freebusypast" size="3"  value="{$freebusypast|escape:"html"}" /> {tr msg="days in the past"}<br />
<div class="align_right"><input type="submit" name="submitfreebusypast" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<h2>{tr msg="Privileged Networks"}</h2>
<div class="contentform">
<form id="postfixmynetworksform" method="post" action="">
<div>
{tr msg="Networks allowed to relay and send mail through unauthenticated SMTP connections to the Kolab server (comma separated networks in x.x.x.x/y format):"}
<input type="text" name="postfixmynetworks" size="80"  value="{$postfixmynetworks|escape:"html"}" />
<div class="align_right"><input type="submit" name="submitpostfixmynetworks" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<h2>{tr msg="SMTP \"smarthost/relayhost\""}</h2>
<div class="contentform">
<form id="postfixrelayhostform" method="post" action="">
<div>
{tr msg="Smarthost (and optional port) to use to send outgoing mail (host.domain.tld). Leave empty for no relayhost."}
<input type="text" name="postfixrelayhost" size="40"  value="{$postfixrelayhost|escape:"html"}" />:
<input type="text" name="postfixrelayport" size="4" value="{$postfixrelayport|escape:"html"}" /><br/>
<div class="align_right"><input type="submit" name="submitpostfixrelayhost" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<h2>{tr msg="Accept Internet Mail"}</h2>
<div class="contentform">
<form id="postfixallowunauthform" method="post" action="">
<div>
<input type="checkbox" name="postfixallowunauth" {if $postfixallowunauth == 'true' }checked="checked"{/if} />
{tr msg="Accept mail from other domains over unauthenticated SMTP. This must be enabled if you want to use the Kolab Server to receive mail from other internet domains directly. Leave disabled to accept mail only from SMTP gateways that are within the privileged network."}
<div class="align_right"><input type="submit" name="submitpostfixallowunauth" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />
<h2>{tr msg="Domains"}</h2>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Domain"}</th><th>{tr msg="Action"}</th>
	</tr>
{section name=id loop=$postfixmydestination}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$postfixmydestination[id]|escape:"html"}</td>
	   <td class="actioncell">{strip}
		<input type="hidden" name="adestination" value="{$postfixmydestination[id]}" />
		<input type="submit" name="deletedestination" value="{tr msg="Delete"}" />
           {/strip}</td>
	</tr>
	</form>
{/section}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"> 
		<input type="text" size="60" name="adestination" />
           </td><td class="actioncell"><input type="submit" name="adddestination" value="{tr msg="Add"}" /></td>
	</tr>
	</form>
</table>
<br/>
<h2>{tr msg="Mail Filter Settings"}</h2>
<div class="contentform">
<form id="kolabfilterform" method="post" action="">
<div>
<input type="checkbox" name="kolabfilterverifyfrom" {if $kolabfilterverifyfrom == 'true' }checked="checked"{/if} />
{tr msg="Check messages for mismatching From header and envelope from."}
<br />
<input type="checkbox" name="kolabfilterallowsender" {if $kolabfilterallowsender == 'true' }checked="checked"{/if} />
{tr msg="Use the Sender header instead of From for the above checks if Sender is present."}
<br />
<h4>{tr msg="Action to take for messages that fail the check:"}</h4>
<input type="radio" name="kolabfilterrejectforgedfrom" value="FALSE" {if $kolabfilterrejectforgedfrom == 'false' }checked="checked"{/if} />
{tr msg="Reject the message, except if it originates from the outside and the From header matches one of Kolab server's domains. In that case rewrite the From header so the recipient can see the potential forgery."}<br/>
<input type="radio" name="kolabfilterrejectforgedfrom" value="TRUE" {if $kolabfilterrejectforgedfrom == 'true' }checked="checked"{/if} />
{tr msg="Always reject the message."}
{tr msg="Note that enabling this setting will make the server reject any mail with non-matching sender and From header if the sender is an account on this server. This is known to cause trouble for example with mailinglists."}
<br />
<div class="align_right"><input type="submit" name="submitkolabfilter" value="{tr msg="Update"}" /></div>
</div>
</form>
</div>
<br />

<h2>{tr msg="Kolab Hostnames (for Master and Slaves)"}</h2>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Host"}</th><th>{tr msg="Action"}</th>
	</tr>
{section name=id loop=$kolabhost}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$kolabhost[id]|escape:"html"}</td>
	   <td class="actioncell">{strip}
		<input type="hidden" name="akolabhost" value="{$kolabhost[id]}" />
		<input type="submit" name="deletekolabhost" value="{tr msg="Delete"}" />
           {/strip}</td>
	</tr>
	</form>
{/section}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"> 
		<input type="text" size="60" name="akolabhost" />
           </td><td class="actioncell"><input type="submit" name="addkolabhost" value="{tr msg="Add"}" /></td>
	</tr>
	</form>
</table>
