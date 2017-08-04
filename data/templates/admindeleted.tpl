{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{$heading}</h1>

<div content="contentsimple">
<p>{t}The administrator with DN{/t} {$dn|escape} {t}has been deleted{/t}</p>
<p><a href="index.php">{t}Back to list of administrators{/t}</a></p>
</div>
