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
<link rel="stylesheet" type="text/css" href="{$stylesheet|default:"$topdir/screen.css"} />
<link rel="stylesheet" type="text/css" href="{$webserver_web_prefix}/custom.css" />

<script type="text/javascript">
<!--
function changeLanguage(combobox) {ldelim}
	val=combobox.options[combobox.selectedIndex].value;
        if(val!="") window.location="{$lang_url}"+val;
{rdelim}
-->
</script>
{literal}
<script src="{$webserver_web_prefix}/images/mootools.v1.11.js" type="text/javascript"></script>
<script src="{$webserver_web_prefix}/images/sliding-tabs.js" type="text/javascript"></script>
<script type="text/javascript">
function synced(a)
{
  var current = a.getElementById;
  var ch = a.id;

  //get the real id from id-1 or id-2
  var actual_id = ch.split('-');


  //get the second element
  var secelement = document.getElementById(actual_id[0]+'-'+'2');
  var firelement = document.getElementById(actual_id[0]+'-'+'1');


  if (firelement.checked==false && secelement.checked==true && a.value==1)
 {
    secelement.checked=false;

 }



}
</script>
<style type="text/css" media="screen">
		#heading {
			display: block;
			text-align: center;
			margin-bottom: 1em;
			background: #f0f0f0;
		}
		#heading * {
			display: inline;
			padding: 7px;
			user-select: none;
			cursor: pointer;
			vertical-align: middle;
		}
		#heading li.active {
			background-color: lightgrey;
			border-radius: 7px;
			-webkit-border-radius: 5px;
			-opera-border-radius: 6px;
			-moz-border-radius: 5px;
		}

		#wrapper { border: 1px dotted gray; margin: 1em; padding: 1em;}

		#panes {
			text-align: justify;
			border-style: none;
			/*width: 245px;*/
			margin: 0 1em 0 1em;

		}

		#panes p {
		  width: 500px;
		  margin: 0 auto 1em auto;
		  line-height: 1.2em;
		}

		#panes div div { overflow: hidden; top:-96px; position:relative;}
		#previous { float: none; cursor: pointer; }
		#next { float: none; cursor: pointer; }
		table, th, td {vertical-align:top;}

		</style>


{/literal}
</head>
<body>
<div id="header" class="container">
<div class="span-24">
{if $uid}
<h3><a href="{$skolab_webmailer_url}"><img src="{$webserver_web_prefix}/images/skolab-webadmin-logo.png" style="vertical-align:middle;" alt="logo"></a></h3>
{else}
<h3><a href="{$skolab_webmailer_url}"><img src="{$webserver_web_prefix}/images/skolab-webadmin-logo.png" style="vertical-align:middle;" alt="logo"></a></h3>
{/if}
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
<a id="logout" href="{$webserver_web_prefix}/logout.php"><img src="{$webserver_web_prefix}/images/icon_grey_logout.png" width="16" style="vertical-align:middle;margin-left:90px;" title="Logout" alt="Logout icon"></a>
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
This is the Community Edition of the <b>Kolab Server</b>. It comes with absolutely <b>no warranties</b> and is typically run entirely self supported. You can find help & information on the community <a href="http://kolab.org">web site</a> & <a href="http://wiki.kolab.org">wiki</a>. Professional support is available from <a href="http://kolabsys.com">Kolab Systems</a>.
</body>
</html>
