<?php
/*
 (c) 2004 Klarälvdalens Datakonsult AB
 (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 (c) 2003 Martin Konold <martin.konold@erfrakon.de>
 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');

/**** Authentication etc. ***/
$sidx = 'about';

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Page logic ****/

// Remember to keep this list up to date when patching packages!
$kolabversions = shell_exec("$kolab_prefix/bin/openpkg rpm -q perl-kolab kolabd kolab-resource-handlers kolab-webadmin" );
$kolabpatchedversions = shell_exec("$kolab_prefix/bin/openpkg rpm -q amavisd apache imapd postfix" );
$openpkgversion = shell_exec("$kolab_prefix/bin/openpkg rpm -q openpkg");

$kolabversion = '@kolab_version@';
if( $kolabversion[0] == '@' ) {
  // Unofficial/non-openpkg package
  $kolabversion = '2.0-unofficial';
}

/**** Insert into template and output ***/
$smarty = new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', $menuitems[$sidx]['submenu'] );
$smarty->assign( 'kolabversion',  $kolabversion );
$smarty->assign( 'kolabversions', $kolabversions );
$smarty->assign( 'kolabpatchedversions', $kolabpatchedversions );
$smarty->assign( 'openpkgversion', $openpkgversion );
$smarty->assign( 'maincontent', 'versions.tpl' );
$smarty->display('page.tpl');

/*
  Local variables:
  mode: php
  indent-tabs-mode: t
  tab-width: 4
  buffer-file-coding-system: utf-8
  End:
*/
 ?>
