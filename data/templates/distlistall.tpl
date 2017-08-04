{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{t}Distribution Lists{/t}</h3>
</div>

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Listname{/t}</th><th>{t}Visibility{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd{/t}">
	   <td class="contentcell">{$entries[id].cn|escape:"html{/t}</td>
	{if $entries[id].internal == true }
	   <td class="actioncell">{t}Internal{/t}</td>
	{else}
	   <td class="actioncell">{t}Public{/t}</td>
	{/if}
	{if $entries[id].deleted neq "FALSE{/t}
	   <td class="actioncell" colspan="2">{t}List deleted, awaiting cleanup...{/t}</td>
	{else}
	   <td class="actioncell"><a href="list.php?action=modify&amp;dn={$entries[id].dn|escape:"url{/t}">{t}Modify{/t}</a></td>
	   <td class="actioncell"><a href="list.php?action=delete&amp;dn={$entries[id].dn|escape:"url{/t}">{t}Delete{/t}</a></td>
	{/if}
	</tr>
{/section}
</table>
