{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
{literal}
html {background: #e7e7e7;}
body {background: #ffffff url({/literal}{$webserver_web_prefix}{literal}/images/body.jpg) repeat-x top left; width:100%;}

.span-24{display: block;float:none;}

#header {
	margin-top: 5px;
	height: 80px;
}


	#header .span-24 {padding-left: 22px;}

	#header h3{
		height: 54px;
		top: 17px;
		position:relative;
	}



	#header ul li {
		list-style-type: none;
		list-style-image: url({/literal}{$webserver_web_prefix}{literal}/images/listitem.png);
	}

#root {
{/literal}
{if $uid}
{literal}
	background: #e7e7e7 url({/literal}{$webserver_web_prefix}{literal}/images/root.jpg) repeat-x top left;
{/literal}
{/if}
{literal}
	min-height: 150px;

}

	#root #main {
		background: transparent url({/literal}{$webserver_web_prefix}{literal}/images/bottom_all.png) no-repeat bottom left;
		margin-bottom: 10px;
		padding-bottom: 5px;
	}
	#navlist{
	width:100%;

	}

#navlist li {
	float:left;
	line-height: 55px;
	margin-left: 15px;
	margin-right: 2px;
	list-style: none;
	margin-top: 1px;

}


	#navlist li a {
		color: #5d5656;
		text-decoration: none;
		text-align: center;
		display: block;
		font-weight:normal;
		font-size:14px;
	}

	#navlist li a.selected {font-weight:bold;color: #c6ced8;}

#content {
	color: #626262;
	padding-top: 8em;
	padding-bottom: 24px;
	margin-bottom: 20px;
}

	#content h2, #content h2 a, #content h1 {color: #5c5c5c; font-weight: normal;text-shadow: 0 2px 2px #FFFFFF; }

#footer {

  padding: 6px;
text-align: center;
}

/* Overriding some global properties */
a:link, a:visited { font-weight: normal; }


	#submenu
	{
	margin-top:-42px;
	margin-left: 47px;
	float:left;


	padding:5px;


	}

#submenu  a {
		color: #5d5656;
		text-decoration: underline;
		text-align: left;
		padding:7px;


	}

#menu-top{

	float:right;
	position:absolute;
	top: 16px;
	right: 12px;


}


#errorcontent
{
 background:#ffffff;
 width:90%;
 display:block;
  padding: 0.2em;
  margin: 1em;
  text-align: left;
 border: 1px dashed;

}

#errorheader
{
background:#cf5659;
font-size:17px;
color:#ffffff;
}


#messagecontent
{
 background:#ffffff;
 width:90%;
 display:block;
  padding: 0.2em;
  margin: 1em;
  text-align: left;
 border: 1px dashed;

}

#messageheader
{
background:#a8cc76;
font-size:17px;
color:#ffffff;
}

#allhere
{
background:#ffffff;
margin:2px;
padding:9px;
border-radius: 15px;
-moz-border-radius:10px;
-webkit-border-radius:10px;
}

.align_center {
  text-align: center;
}

.contentform {
  /*float: left;*/
  padding: .1em .5em .1em .5em;
  background-color: #c6ced8;
  border: solid 0.2px black;
}

.contentrow {
border: 1px dashed;
background-color: #c6ced8;

}

.welcomelinks{
	color: #5d5656;
	text-decoration: none;
	font-size:12px;
	text-align: center;
}
{/literal}