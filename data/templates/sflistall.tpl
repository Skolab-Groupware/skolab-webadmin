{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{t}Shared folders{/t}</h3>
</div>

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Name{/t}</th><th>{t}Server{/t}</th><th>{t}Type{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].cn|escape:"html"}</td>
	   <td class="contentcell">{$entries[id].kolabhomeserver|escape:"html"}</td>
	   <td class="contentcell">{$entries[id].foldertype|escape:"html"}</td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{t}Folder deleted, awaiting cleanup...{/t}</td>
	{else}
	   <td class="actioncell"><a href="sf.php?action=modify&amp;dn={$entries[id].dn|escape:"url"}">{t}Modify{/t}</a></td>
	   <td class="actioncell"><a href="sf.php?action=delete&amp;dn={$entries[id].dn|escape:"url"}">{t}Delete{/t}</a></td>
	{/if}
	</tr>
{/section}
</table>
