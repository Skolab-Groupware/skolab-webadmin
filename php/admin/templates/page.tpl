{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>{$page_title}</title>
<meta name="robots" content="noindex" />	
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="{$currentlang}" /> 
<meta name="description" content="Kolab Administration Webintefrace" />
<meta name="keywords" content="Linux, Unix, Groupware, Email, Calendar" />
<link rel="stylesheet" href="{$stylesheet|default:"$topdir/style.css"}" />

<script type="text/javascript">
<!--
function changeLanguage(combobox) {ldelim}
	val=combobox.options[combobox.selectedIndex].value;
        if(val!="") window.location="{$lang_url}"+val;
{rdelim}
-->
</script>

</head>
<body>
<div id="topbar">
	<a href="/admin"><div id="toplogo"></div></a>
	<div id="toptitle">{$page_title}</div>
</div>
<div id="topuserinfo">
{if $uid}
{tr msg="User:"} {$uid} | {tr msg="Role:"} {$group} | <a id="logout" href="{$topdir}/logout.php">{tr msg="Logout"}</a>
{else}
{tr msg="Not logged in"}
{/if}
<br/>
<select name="lang" class="langcombo" onchange="changeLanguage(this);">
{section name=id loop=$languages}
{if $languages[id].code==$currentlang}
<option value="{$languages[id].code}" selected>{$languages[id].name}</option>
{else}
<option value="{$languages[id].code}">{$languages[id].name}</option>
{/if}
{/section}
</select>
</div>
{strip}
<div id="topmenu">
{foreach from=$menuitems item=menuitem}
<a href="{$menuitem.url}">
	<span class="topmenuitem{$menuitem.selected}">&nbsp;{$menuitem.name}&nbsp;</span>
</a>
{/foreach}
</div>
{/strip}
{if count($submenuitems) > 0}
<div id="submenu">
{strip}
{section name=id loop=$submenuitems}
<a href="{$submenuitems[id].url}">
	<span class="submenuitem{$submenuitems[id].selected}">{$submenuitems[id].name}</span>
</a>&nbsp;|&nbsp;
{/section}
{/strip}
</div>
{/if}
<div id="page">
{if $errors}
<div id="errorcontent">
<div id="errorheader">{tr msg="Errors:"}</div>
{section name=id loop=$errors}
{$errors[id]}<br/>
{/section}
</div>
{/if}
{if $messages}
<div id="messagecontent">
<div id="messageheader">{tr msg="Message:"}</div>
{section name=id loop=$messages}
{$messages[id]}<br/>
{/section}
</div>
{/if}
<div id="maincontent">
{include file=$maincontent}
</div>
<!--
<div id="validators">
<a href="http://validator.w3.org/check/referer">
<img style="border:0;width:88px;height:31px"
     src="http://www.w3.org/Icons/valid-xhtml10"
     alt="Valid XHTML 1.0!" />
</a>
<a href="http://jigsaw.w3.org/css-validator/check/referer">
<img style="border:0;width:88px;height:31px"
     src="http://jigsaw.w3.org/css-validator/images/vcss" 
     alt="Valid CSS!" />
</a>
</div>
-->
</div>
<br/>
<br/>
</body>
</html>