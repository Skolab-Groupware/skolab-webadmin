{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h3>{tr msg="Email Users"}</h3>
<div align="center">
<a href="{$self_url}?alphalimit="> {tr msg="[ ALL ]"} </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=a"> [ A-F ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=g"> [ G-L ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=m"> [ M-R ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=s"> [ S-Z ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=other"> {tr msg="[ OTHER ]"} </a>
</div>
<div class="contentform">
<form name="filterform" method="post">
{tr msg="Filter:"} <select name="filterattr">
{foreach key=value item=name from=$filterattrs}
{if $value eq $filterattr}
  <option value="{$value}" selected>{$name|escape:"html"}</option>
{else}
  <option value="{$value}">{$name|escape:"html"}</option>
{/if}
{/foreach}
</select>
<select name="filtertype">
{foreach key=value item=name from=$filtertypes}
{if $value eq $filtertype}
  <option value="{$value}" selected>{$name|escape:"html"}</option>
{else}
  <option value="{$value}">{$name|escape:"html"}</option>
{/if}
{/foreach}
</select>
<input type="text" name="filtervalue" value="{$filtervalue|escape:"html"}" />
<input type="submit" name="filtersubmit" value="{tr msg="Filter"}" /></form>
</div>
<div>
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Type"}</th><th>{tr msg="Name"}</th><th>{tr msg="E-mail"}</th><th>{tr msg="uid"}</th><th colspan="2">{tr msg="Action"}</th>
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
	   <td class="actioncell" colspan="2">{tr msg="User Deleted, awaiting cleanup..."}</td>
	{else}
	   <td class="actioncell" align="center"><a href="user.php?action=modify&dn={$entries[id].dn|escape:"url"}">{tr msg="Modify"}</a></td>
	   <td class="actioncell" align="center"><a href="user.php?action=delete&dn={$entries[id].dn|escape:"url"}">{tr msg="Delete"}</a></td>
	{/if}
	</tr>
{/section}
</table>
</div>