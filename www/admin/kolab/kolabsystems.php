<?php
/*
 (c) 2011 Kolab Systems AG
 (c) 2004 Klarlvdalens Datakonsult AB
 (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 (c) 2003 Martin Konold <martin.konold@erfrakon.de>
 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once('KolabAdmin/include/mysmarty.php');
require_once('KolabAdmin/include/headers.php');
require_once('KolabAdmin/include/locale.php');
require_once('KolabAdmin/include/authenticate.php');

/**** Authentication etc. ***/
$sidx = 'about';

require_once('KolabAdmin/include/menu.php');


/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Insert into template and output ***/
$smarty =& new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', $menuitems[$sidx]['submenu'] );
$smarty->assign( 'maincontent', 'kolabsystems.tpl' );
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
