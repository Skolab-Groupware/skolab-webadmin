{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{$heading}</h1>

<div content="contentsimple">
<p>{tr msg="The user with DN"} {$dn|escape} {tr msg="has been deleted"}</p>
<p><a href="index.php">{tr msg="Back to list of users"}</a></p>
</div>
