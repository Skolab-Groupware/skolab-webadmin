{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h3>{t}Email Users{/t}</h3>
<div class="align_center">
<a {if $alphagroup==""}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup="> {t}[ ALL ]{/t} </a>&nbsp;&nbsp;
<a {if $alphagroup=="a"}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=a"> [ A-F ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="g"}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=g"> [ G-L ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="m"}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=m"> [ M-R ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="s"}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=s"> [ S-Z ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="other"}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=other"> {t}[ OTHER ]{/t} </a>
</div>
<div class="contentform">
<form id="filterform" method="post" action="">
<div>

{t}Filter:{/t}

<select name="filterattr">
{foreach key=value item=name from=$filterattrs}
{if $value eq $filterattr}
  <option value="{$value}" selected="selected">{$name|escape:"html"}</option>
{else}
  <option value="{$value}">{$name|escape:"html"}</option>
{/if}
{/foreach}
</select>

<select name="filtertype">
{foreach key=value item=name from=$filtertypes}
{if $value eq $filtertype}
  <option value="{$value}" selected="selected">{$name|escape:"html"}</option>
{else}
  <option value="{$value}">{$name|escape:"html"}</option>
{/if}
{/foreach}
</select>

<input type="text" name="filtervalue" value="{$filtervalue|escape:"html"}" />
<input type="submit" name="filtersubmit" value="{t}Filter{/t}" />

</div>
</form>
</div>
<div>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Type{/t}</th><th>{t}Name{/t}</th><th>{t}E-mail{/t}</th><th>{t}uid{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>

{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	{if $entries[id].type == 'U' }
	   <td class="contentcell" title="User Account" align="center">U</td>
	{elseif $entries[id].type == 'I' }
	   <td class="contentcell" title="Internal User Account" align="center">I</td>
	{elseif $entries[id].type == 'G' }
	   <td class="contentcell" title="Group Account" align="center">G</td>
	{elseif $entries[id].type == 'R' }
	   <td class="contentcell" title="Resource Account" align="center">R</td>
	{else}
	   <td class="contentcell" title="Unknown Account Type" align="center">?</td>
	{/if}
	   <td class="contentcell">{$entries[id].sn|escape:"html"}, {$entries[id].fn|escape:"html"}</td>
	   <td class="contentcell"><a href="mailto:{$entries[id].mail|escape:"html"}">{$entries[id].mail|escape:"html"}</a></td>
	   <td class="contentcell">{$entries[id].uid|escape:"html"}</td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{t}User Deleted, awaiting cleanup...{/t}</td>
	{else}
	   <td class="actioncell" align="center"><a href="user.php?action=modify&amp;dn={$entries[id].dn|escape:"url"}">{t}Modify{/t}</a></td>
	   <td class="actioncell" align="center"><a href="user.php?action=delete&amp;dn={$entries[id].dn|escape:"url"}">{t}Delete{/t}</a></td>
	{/if}
	</tr>
{/section}

</table>
</div>
