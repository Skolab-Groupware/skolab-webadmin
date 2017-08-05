{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{t}(only external addresses without a kolab user account){/t}</h3>
</div>
<div class="align_center">
<a href="{$self_url}?alphalimit="> {t}[ ALL ]{/t} </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=a"> [ A-F ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=g"> [ G-L ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=m"> [ M-R ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=s"> [ S-Z ] </a>&nbsp;&nbsp;
<a href="{$self_url}?alphalimit=other"> {t}[ OTHER ]{/t} </a>
</div>
<div class="contentform">
<form id="filterform" method="post" action="">
<div>
{t}Filter:{/t} <select name="filterattr">
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

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Name{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell"><a href="mailto:{$entries[id].mail|escape:"html"}" title="{$entries[id].sn|escape}, {$entries[id].fn|escape:"html"} &lt;{$entries[id].mail|escape:"html"}&gt;">{$entries[id].sn|escape:"html"}, {$entries[id].fn|escape:"html"}</a></td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{t}Entry deleted, awaiting cleanup...{/t}</td>
	{else}
	   <td class="actioncell"><a href="addr.php?action=modify&amp;dn={$entries[id].dn|escape:"url"}">{t}Modify{/t}</a></td>
	   <td class="actioncell"><a href="addr.php?action=delete&amp;dn={$entries[id].dn|escape:"url"}">{t}Delete{/t}</a></td>
	{/if}
	</tr>
{/section}
</table>
