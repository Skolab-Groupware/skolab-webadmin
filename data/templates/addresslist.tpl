{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
{tr msg="(only external addresses without a kolab user account)"}
</div>

<div class="contenttable">
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{tr msg="Name"}</th><th colspan="2">{tr msg="Action"}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].name|escape:"html"}</td>
	   <td class="actioncell"><a href="modify.php?dn={$entries[id].dn|escape:"url"}">{tr msg="Modify"}</a></td>
	   <td class="actioncell"><a href="delete.php?dn={$entries[id].dn|escape:"url"}">{tr msg="Delete"}</a></td>
	</tr>
{/section}
</table>
</div>
