{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{tr msg="Kolab2 Groupware Server Version"}</h1>
<pre>{$kolabversion}</pre>
<h1>{tr msg="Kolab2 Groupware Server Component Versions"}</h1>
<pre>{$kolabversions}</pre>
<h1>{tr msg="PEAR/Horde Versions"}</h1>
<pre>{$pearhordeversions}</pre>
{if $OPENPKG=="yes"}
<h1>{tr msg="Kolab2 Patched OpenPKG Package Versions"}</h1>
<pre>{$kolabpatchedversions}</pre>
<h1>{tr msg="OpenPKG Version"}</h1>
<pre>{$openpkgversion}</pre>
{/if}
</div>
