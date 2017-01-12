{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="Kolab Server Settings"}</h1>

{if $systemaliasconf }
<a name="systemaliasconf"></a>
<h2>{tr msg="Administrative email addresses"}</h2>
<div class="contentsimple">
<p>{tr msg="You have not yet set up a receiving account for the administrative email addresses hostmaster@yourdomain.tld, postmaster@yourdomain.tld, MAILER-DAEMON@yourdomain.tld, abuse@yourdomain.tld and virusalert@yourdomain.tld. Enter the email address of a kolab account below and press the button to create a distribution list for each of those addresses. Later you can add or remove people from the lists like any other distribution list"}</p>
<div class="contentform">
<form name="systemalias" method="post">
{tr msg="Email address of account that should receive administrative mail:"}
<input type="text" name="systemaliasmail" size="80"  value="{$systemaliasmail}" /><br/>
<div align="right"><input type="submit" name="submitsystemalias" value="{tr msg="Create Distribution Lists"}" /></div>
</form>
</div>
<br />
</div>
{/if}

<h2>{tr msg="Enable or Disable individual Services"}</h2>
<div class="contentsimple">
<p>{tr msg="Using legacy services poses a security thread due to leakage of cleartext passwords, lack of authenticity and privacy."}</p>
<p>{tr msg="The legacy Freebusy Support (FTP and HTTP) is only required for Outlook2000 clients. Under all other circumstances it is advised to use the server-side freebusy creation feature over secure HTTP instead (this is enabled by default and may not be deactivated)."}</p>
<p>{tr msg="Further details with regards to security considerations are available on the internet at the <a href=\"http://www.kolab.org\">Kolab</a> webserver."}</p>
</div>
<h3>{tr msg="Services"}</h3>
<form name="serviceform" method="post">

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Service"}</th><th>{tr msg="Enabled"}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].name|escape:"html"}</td>
	   <td class="actioncell"><input type="checkbox" name="{$entries[id].service}" {if $entries[id].enabled == 'true' }checked{/if}></td>
	</tr>
{/section}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"> </td><td class="actioncell"><input type="submit" name="submitservices" value="{tr msg="Update"}"></td>
	</tr>
</table>
</form>
<h2>{tr msg="Quota settings"}</h2>
<div class="contentform">
<form name="quotawarnform" method="post">
<br />
{tr msg="Warn users when they have used"} <input name="quotawarn" size="3"  value="{$quotawarn}" /> {tr msg="% of their quota"}<br />
<div align="right"><input type="submit" name="submitquotawarn" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<h2>{tr msg="Free/Busy settings"}</h2>
<div class="contentform">
<form name="httpallowunauthfbform" method="post">
<br />
<input type="checkbox" name="httpallowunauthfb" {if $httpallowunauthfb == 'true' }checked{/if} />
{tr msg="Allow unauthenticated downloading of Free/Busy information"}
<br />
<div align="right"><input type="submit" name="submithttpallowunauthfb" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<div class="contentform">
<form name="freebusypastform" method="post">
<br />
{tr msg="When creating free/busy lists, include data from"} <input name="freebusypast" size="3"  value="{$freebusypast}" /> {tr msg="days in the past"}<br />
<div align="right"><input type="submit" name="submitfreebusypast" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<h2>{tr msg="Privileged Networks"}</h2>
<div class="contentform">
<form name="postfixmynetworksform" method="post">
{tr msg="Networks allowed to relay and send mail through unauthenticated SMTP connections to the Kolab server (comma separated networks in x.x.x.x/y format):"}
<input type="text" name="postfixmynetworks" size="80"  value="{$postfixmynetworks}" />
<div align="right"><input type="submit" name="submitpostfixmynetworks" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<h2>{tr msg="SMTP \"smarthost/relayhost\""}</h2>
<div class="contentform">
<form name="postfixrelayhost" method="post">
{tr msg="Smarthost to use to send outgoing mail (host.domain.tld). Leave empty for no relayhost."}
<input type="text" name="postfixrelayhost" size="80"  value="{$postfixrelayhost}" /><br/>
<input type="checkbox" name="postfixrelayhostmx" {if $postfixrelayhostmx == 'true' }checked{/if} />
{tr msg="Enable MX lookup for relayhost (if in doubt, leave it off)"}
<div align="right"><input type="submit" name="submitpostfixrelayhost" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<h2>{tr msg="Accept Internet Mail"}</h2>
<div class="contentform">
<form name="postfixallowunauthform" method="post">
<input type="checkbox" name="postfixallowunauth" {if $postfixallowunauth == 'true' }checked{/if} />
{tr msg="Accept mail from other domains over non-authenticated SMTP. This must be enabled if you want to use the Kolab server to receive mail from other internet domains."}
<div align="right"><input type="submit" name="submitpostfixallowunauth" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />
<h2>{tr msg="Mail Filter Settings"}</h2>
<div class="contentform">
<form name="kolabfilterform" method="post">
<input type="checkbox" name="kolabfilterverifyfrom" {if $kolabfilterverifyfrom == 'true' }checked{/if} />
{tr msg="Check messages for mismatching From header and envelope from."}
<br />
<input type="checkbox" name="kolabfilterallowsender" {if $kolabfilterallowsender == 'true' }checked{/if} />
{tr msg="Use the Sender header instead of From for the above checks if Sender is present."}
<br />
<h4>Action to take for messages that fail the check:</h4>
<input type="radio" name="kolabfilterrejectforgedfrom" value="FALSE" {if $kolabfilterrejectforgedfrom == 'false' }checked{/if} />
{tr msg="Reject the message with the except if it originates from the outside but has a From header that matches the Kolab server's domain. In that case rewrite the From header so the recipient can see the potential forgery."}<br/>
<input type="radio" name="kolabfilterrejectforgedfrom" value="TRUE" {if $kolabfilterrejectforgedfrom == 'true' }checked{/if} />
{tr msg="Always reject the message."}
{tr msg="Note that enabling this setting will make the server reject any mail with non-matching sender and From header if the sender is an account on this server. This is known to cause trouble for example with mailinglists."}
<br />
<div align="right"><input type="submit" name="submitkolabfilter" value="{tr msg="Update"}" /></div>
</form>
</div>
<br />

<h2>{tr msg="Kolab Hosts"}</h2>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Host"}</th><th>{tr msg="Action"}</th>
	</tr>
{section name=id loop=$kolabhost}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$kolabhost[id]|escape:"html"}</td>
	   <td class="actioncell">{strip}
		<form method="post">
		<input type="hidden" name="akolabhost" value="{$kolabhost[id]}" />
		<input type="submit" name="deletekolabhost" value="{tr msg="Delete"}" />
		</form>
           {/strip}</td>
	</tr>
{/section}
	<tr class="contentrow{cycle values="even,odd"}">
	   <form method="post">
	   <td class="contentcell"> 
		<input type="text" size="60" name="akolabhost" />
           </td><td class="actioncell"><input type="submit" name="addkolabhost" value="{tr msg="Add"}" /></td>
	   </form>
	</tr>
</table>
</div>
