{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{t}Maintainers{/t}</h3>
</div>

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Name{/t}</th><th>{t}UID{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].sn|escape:"html"}, {$entries[id].fn|escape:"html"}</td>
	   <td class="contentcell">{$entries[id].uid|escape:"html"}</td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{t}Object Deleted, awaiting cleanup...{/t}</td>
	{else}
	   <td class="actioncell"><a href="maintainer.php?action=modify&amp;dn={$entries[id].dn|escape:"url"}">{t}Modify{/t}</a></td>
	   <td class="actioncell"><a href="maintainer.php?action=delete&amp;dn={$entries[id].dn|escape:"url"}">{t}Delete{/t}</a></td>
	{/if}
	</tr>
{/section}
</table>
