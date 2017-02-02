{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{tr msg="(only external addresses without a kolab user account)"}</h3>
</div>
<div class="align_center">
<a href="{$self_url}?alphalimit="> {tr msg="[ ALL ]"} </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=a"> [ A-F ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=g"> [ G-L ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=m"> [ M-R ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=s"> [ S-Z ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=other"> {tr msg="[ OTHER ]"} </a>
</div>
<div class="contentform">
<form id="filterform" method="post" action="">
<div>
{tr msg="Filter:"} <select name="filterattr">
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
<input type="submit" name="filtersubmit" value="{tr msg="Filter"}" />
</div>
</form>
</div>

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Name"}</th><th colspan="2">{tr msg="Action"}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"><a href="mailto:{$entries[id].mail|escape:"html"}" title="{$entries[id].sn|escape}, {$entries[id].fn|escape:"html"} &lt;{$entries[id].mail|escape:"html"}&gt;">{$entries[id].sn|escape:"html"}, {$entries[id].fn|escape:"html"}</a></td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{tr msg="Entry deleted, awaiting cleanup..."}</td>
	{else}
	   <td class="actioncell"><a href="addr.php?action=modify&amp;dn={$entries[id].dn|escape:"url"}">{tr msg="Modify"}</a></td>
	   <td class="actioncell"><a href="addr.php?action=delete&amp;dn={$entries[id].dn|escape:"url"}">{tr msg="Delete"}</a></td>
	{/if}
	</tr>
{/section}
</table>
