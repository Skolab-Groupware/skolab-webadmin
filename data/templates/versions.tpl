{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<div class="contenttext">
<h1>{t}Skolab Groupware Server Version{/t}</h1>
<pre>{$kolabversion} Community Edition</pre>
<h1>{t}Skolab Groupware Server Component Versions{/t}</h1>
<pre>{$kolabversions}</pre>
<h1>{t}PEAR/Horde Versions{/t}</h1>
<pre>{$pearhordeversions}</pre>
{if $OPENPKG=="yes{/t}
<h1>{t}Skolab Patched OpenPKG Package Versions{/t}</h1>
<pre>{$kolabpatchedversions}</pre>
<h1>{t}OpenPKG Version{/t}</h1>
<pre>{$openpkgversion}</pre>
{/if}
</div>
