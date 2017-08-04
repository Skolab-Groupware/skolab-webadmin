{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<!DOCTYPE HTML>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<title>Kolab: {$page_title}</title>
<link rel="shortcut icon" type="image/png" href="{$webserver_web_prefix}/favicon.png" />
<meta name="robots" content="noindex" />
<meta name="description" content="Kolab Administration Webinterface" />
<meta name="keywords" content="Linux, Unix, Groupware, Email, Calendar" />
<link rel="stylesheet" type="text/css" href="{$stylesheet|default:"$topdir/screen.css"}" />
<link rel="stylesheet" type="text/css" href="{$webserver_web_prefix}/custom.css" />

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
<div id="header" class="container">
<div class="span-24">
<h3><a href="{$kolab_wui}"><img src="{$webserver_web_prefix}/images/klogo.png" style="vertical-align:middle;" alt="logo"></a></h3>
<div id="menu-top">
{if $uid}
<img src="{$webserver_web_prefix}/images/user-icon-1-20x20.gif" width="16" style="vertical-align:middle;" title="Username" alt="username icon"> {$uid} <img src="{$webserver_web_prefix}/images/eye_pencil.png" width="16" style="vertical-align:middle;margin-left:8px;" title="Role" alt="Role icon"> {$group}
{/if}

<img src="{$webserver_web_prefix}/images/ul-icon.png" width="16" style="vertical-align:middle;margin-left:8px;" title="Change Language" alt="Language icon"> <select name="lang" style="height:17px;vertical-align:middle" onchange="changeLanguage(this);">
{section name=id loop=$languages}
{if $languages[id].code==$currentlang}
<option value="{$languages[id].code}" selected="selected">{$languages[id].name}</option>
{else}
<option value="{$languages[id].code}">{$languages[id].name}</option>
{/if}
{/section}
</select>
{if $uid}
<a id="logout" href="{$webserver_web_prefix}/logout.php"><img src="{$webserver_web_prefix}/images/icon_grey_logout.png" width="16" style="vertical-align:middle;margin-left:10px;" title="Logout" alt="Logout icon"></a>
{/if}
</div>
		</div><!-- .span-24 -->
	</div><!-- #header -->
	<div id="root">
		<div class="container">
			<div>
				<ul id="navlist">

				{foreach from=$menuitems item=menuitem}
  				<li>
						<a class="{$menuitem.selected}" href="{$menuitem.url}">{$menuitem.name}</a>
					</li>
{/foreach}

				</ul>
			</div><!-- .span-24 -->


			<div id="content">

<div id="submenu">
{if count($submenuitems) > 0}
{$page_title}:
{strip}
{section name=id loop=$submenuitems}
<a href="{$submenuitems[id].url}">
{$submenuitems[id].name}
</a>&nbsp;|&nbsp;
{/section}
{/strip}
{/if}
</div>

<!--start-->
{if $errors}
<div id="errorcontent">
<div id="errorheader">{t}Errors:{/t}</div>
{section name=id loop=$errors}
{$errors[id]}<br/>
{/section}
</div>
{/if}
{if $messages}
<div id="messagecontent">
<div id="messageheader">{t}Message:{/t}</div>
{section name=id loop=$messages}
{$messages[id]}<br/>
{/section}
</div>
{/if}

<!--end-->
<div id="allhere">
{include file=$maincontent}
</div>
			</div>
		</div><!-- .container -->
	</div><!-- #root -->
<div id="footer">
This is the Community Edition of the <b>Kolab Server</b>. <br />It comes with absolutely <b>no warranties</b> and is typically run entirely self supported. You can find help & information on the community <a href="http://kolab.org">web site</a> & <a href="http://wiki.kolab.org">wiki</a>. <br />Professional support is available from <a href="http://kolabsys.com">Kolab Systems</a>.
</body>
</html>
