{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h3>{tr msg="Shared folders"}</h3>
</div>

<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Name"}</th><th>{tr msg="Server"}</th><th colspan="2">{tr msg="Action"}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].cn|escape:"html"}</td>
	   <td class="contentcell">{$entries[id].kolabhomeserver|escape:"html"}</td>
	{if $entries[id].deleted neq "FALSE"}
	   <td class="actioncell" colspan="2">{tr msg="Folder deleted, awaiting cleanup..."}</td>
	{else}
	   <td class="actioncell"><a href="sf.php?action=modify&dn={$entries[id].dn|escape:"url"}">{tr msg="Modify"}</a></td>
	   <td class="actioncell"><a href="sf.php?action=delete&dn={$entries[id].dn|escape:"url"}">{tr msg="Delete"}</a></td>
	{/if}
	</tr>
{/section}
</table>
