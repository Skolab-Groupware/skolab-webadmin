{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h3>{t}Email Users{/t}</h3>
<div align="center">
<a {if $alphagroup=="{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup="> {t}[ ALL ]{/t} </a>&nbsp;&nbsp;
<a {if $alphagroup=="a{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=a"> [ A-F ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="g{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=g"> [ G-L ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="m{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=m"> [ M-R ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="s{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=s"> [ S-Z ] </a>&nbsp;&nbsp;
<a {if $alphagroup=="other{/t}class="alphagroupitemselected"{/if} href="{$self_url}?alphagroup=other"> {t}[ OTHER ]{/t} </a>
</div>
<div class="contentform">
<form name="filterform" method="post">
{t}Filter:{/t} <select name="filterattr">
{foreach key=value item=name from=$filterattrs}
{if $value eq $filterattr}
  <option value="{$value}" selected>{$name|escape:"html{/t}</option>
{else}
  <option value="{$value}">{$name|escape:"html{/t}</option>
{/if}
{/foreach}
</select>
<select name="filtertype">
{foreach key=value item=name from=$filtertypes}
{if $value eq $filtertype}
  <option value="{$value}" selected>{$name|escape:"html{/t}</option>
{else}
  <option value="{$value}">{$name|escape:"html{/t}</option>
{/if}
{/foreach}
</select>
<input type="text" name="filtervalue" value="{$filtervalue|escape:"html{/t}" />
<input type="submit" name="filtersubmit" value="{t}Filter{/t}" /></form>
</div>
<div align="center">
<h1>Too many users, please narrow down the search.</h1>
</div>