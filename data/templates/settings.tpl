{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{t}Skolab Server Settings{/t}</h1>

{if count($systemaliasconf)>0 }
<a name="systemaliasconf"></a>
<h2>{t}Administrative email addresses{/t}</h2>
<div class="contentsimple">
<p>{t}You have not yet set up a receiving account for the administrative email addresses hostmaster@yourdomain.tld, postmaster@yourdomain.tld, MAILER-DAEMON@yourdomain.tld, abuse@yourdomain.tld and virusalert@yourdomain.tld. Enter the email address of a Skolab account below and press the button to create a distribution list for each of those addresses. Later you can add or remove people from the lists like any other distribution list{/t}</p>
{section name=id loop=$systemaliasconf}
<div class="contentform">
<form id="systemalias_{$systemaliasconf[id].n}" method="post" action="">
<div>
{t}Email address of account that should receive administrative mail for domain{/t} {$systemaliasconf[id].domain|escape:html}:
<input type="text" name="systemaliasmail_{$systemaliasconf[id].n}" size="80"  value="{$systemaliasmail[id]|escape:"html"}" /><br/>
<div class="align_right"><input type="submit" name="submitsystemalias_{$systemaliasconf[id].n}" value="{t}Create Distribution Lists{/t}" /></div>
</div>
</form>
</div>
{/section}
<br />
</div>
{/if}

<h2>{t}Enable or Disable individual Services{/t}</h2>
<form id="serviceform" method="post" action="">
<div>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Service{/t}</th><th>{t}Enabled{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].name|escape:"html"}</td>
	   <td class="actioncell"><input type="checkbox" name="{$entries[id].service}" {if $entries[id].enabled == 'true' }checked="checked"{/if} /></td>
	</tr>
{/section}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"> </td><td class="actioncell"><input type="submit" name="submitservices" value="{t}Update{/t}" /></td>
	</tr>
</table>
</div>
</form>
<h2>{t}Quota settings{/t}</h2>
<div class="contentform">
<form id="quotawarnform" method="post" action="">
<div>
<br />
{t}Warn users when they have used{/t} <input name="quotawarn" size="3"  value="{$quotawarn|escape:"html"}" /> {t}% of their quota{/t}<br />
<div class="align_right"><input type="submit" name="submitquotawarn" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />
<h2>{t}Free/Busy settings{/t}</h2>
<div class="contentform">
<form id="httpallowunauthfbform" method="post" action="">
<div>
<br />
<input type="checkbox" name="httpallowunauthfb" {if $httpallowunauthfb == 'true' }checked="checked"{/if} />
{t}Allow unauthenticated downloading of Free/Busy information{/t}
<br />
<div class="align_right"><input type="submit" name="submithttpallowunauthfb" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />
<div class="contentform">
<form id="freebusypastform" method="post" action="">
<div>
<br />
{t}When creating free/busy lists, include data from{/t} <input name="freebusypast" size="3"  value="{$freebusypast|escape:"html"}" /> {t}days in the past{/t}<br />
<div class="align_right"><input type="submit" name="submitfreebusypast" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />
<h2>{t}Privileged Networks{/t}</h2>
<div class="contentform">
<form id="postfixmynetworksform" method="post" action="">
<div>
{t}Networks allowed to relay and send mail through unauthenticated SMTP connections to the Skolab server (comma separated networks in x.x.x.x/y format):{/t}
<input type="text" name="postfixmynetworks" size="80"  value="{$postfixmynetworks|escape:"html"}" />
<div class="align_right"><input type="submit" name="submitpostfixmynetworks" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />
<h2>{t}SMTP \"smarthost/relayhost\"{/t}</h2>
<div class="contentform">
<form id="postfixrelayhostform" method="post" action="">
<div>
{t}Smarthost (and optional port) to use to send outgoing mail (host.domain.tld). Leave empty for no relayhost.{/t}
<input type="text" name="postfixrelayhost" size="40"  value="{$postfixrelayhost|escape:"html"}" />:
<input type="text" name="postfixrelayport" size="4" value="{$postfixrelayport|escape:"html"}" /><br/>
<div class="align_right"><input type="submit" name="submitpostfixrelayhost" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />
<h2>{t}Accept Internet Mail{/t}</h2>
<div class="contentform">
 <form id="postfixallowunauthform" method="post" action="">
  <div>
   <table border="0">
    <tr>
     <td valign="top"><input type="checkbox" name="postfixallowunauth" {if $postfixallowunauth == 'true' }checked="checked"{/if} />
     </td>
     <td>{t}Accept mail from other domains over unauthenticated SMTP. This must be enabled if you want to use the Skolab Server to receive mail from other internet domains directly. Leave disabled to accept mail only from SMTP gateways that are within the privileged network.{/t}
     </td>
    </tr>
    <tr>
     <td colspan="2" align="right"><input type="submit" name="submitpostfixallowunauth" value="{t}Update{/t}" />
     </td>
    </tr>
   </table>
  </div>
 </form>
</div>
<br />
<h2>{t}Domains{/t}</h2>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Domain{/t}</th><th>{t}Action{/t}</th>
	</tr>
{section name=id loop=$postfixmydestination}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$postfixmydestination[id]|escape:"html"}</td>
	   <td class="actioncell">{strip}
		<input type="hidden" name="adestination" value="{$postfixmydestination[id]}" />
		<input type="submit" name="deletedestination" value="{t}Delete{/t}" />
           {/strip}</td>
	</tr>
	</form>
{/section}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">
		<input type="text" size="60" name="adestination" />
           </td><td class="actioncell"><input type="submit" name="adddestination" value="{t}Add{/t}" /></td>
	</tr>
	</form>
</table>
<br/>
<h2>{t}Mail Filter Settings{/t}</h2>
<div class="contentform">
<form id="kolabfilterform" method="post" action="">
<div>
<input type="checkbox" name="kolabfilterverifyfrom" {if $kolabfilterverifyfrom == 'true' }checked="checked"{/if} />
{t}Check messages for mismatching From header and envelope from.{/t}
<br />
<input type="checkbox" name="kolabfilterallowsender" {if $kolabfilterallowsender == 'true' }checked="checked"{/if} />
{t}Use the Sender header instead of From for the above checks if Sender is present.{/t}
<br />
<h4>{t}Action to take for messages that fail the check:{/t}</h4>
<table border="0">
 <tr>
  <td valign="top"><input type="radio" name="kolabfilterrejectforgedfrom" value="FALSE" {if $kolabfilterrejectforgedfrom == 'false' }checked="checked"{/if} />
  </td>
  <td>{t}Reject the message, except if it originates from the outside and the From header matches one of Skolab server&rsquo;s domains. In that case rewrite the From header so the recipient can see the potential forgery.{/t}<br/>
  </td>
 </tr>
 <tr>
  <td valign="top"><input type="radio" name="kolabfilterrejectforgedfrom" value="TRUE" {if $kolabfilterrejectforgedfrom == 'true' }checked="checked"{/if} />
  </td>
  <td>{t}Always reject the message.{/t}
{t}Note that enabling this setting will make the server reject any mail with non-matching sender and From header if the sender is an account on this server. This is known to cause trouble for example with mailinglists.{/t}
  </td>
 </tr>
</table>
<div class="align_right"><input type="submit" name="submitkolabfilter" value="{t}Update{/t}" /></div>
</div>
</form>
</div>
<br />

<h2>{t}Skolab Hostnames (for Master and Slaves){/t}</h2>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Host{/t}</th><th>{t}Action{/t}</th>
	</tr>
{section name=id loop=$kolabhost}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$kolabhost[id]|escape:"html"}</td>
	   <td class="actioncell">{strip}
		<input type="hidden" name="akolabhost" value="{$kolabhost[id]}" />
		<input type="submit" name="deletekolabhost" value="{t}Delete{/t}" />
           {/strip}</td>
	</tr>
	</form>
{/section}
	<form method="post" action="">
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">
		<input type="text" size="60" name="akolabhost" />
           </td><td class="actioncell"><input type="submit" name="addkolabhost" value="{t}Add{/t}" /></td>
	</tr>
	</form>
</table>
