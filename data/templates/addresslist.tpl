{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
{t}(only external addresses without a Skolab user account){/t}
</div>

<div class="contenttable">
<table class="contenttable" cellpadding="0" cellspacing="1px">
	<tr class="contentrow">
	<th>{t}Name{/t}</th><th colspan="2">{t}Action{/t}</th>
	</tr>
{section name=id loop=$entries}
	<tr class="contentrow{cycle values="even,odd"}">
	   <td class="contentcell">{$entries[id].name|escape:"html"}</td>
	   <td class="actioncell"><a href="modify.php?dn={$entries[id].dn|escape:"url"}">{t}Modify{/t}</a></td>
	   <td class="actioncell"><a href="delete.php?dn={$entries[id].dn|escape:"url"}">{t}Delete{/t}</a></td>
	</tr>
{/section}
</table>
</div>
