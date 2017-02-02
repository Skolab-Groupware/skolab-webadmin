<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *  Copyright (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 *  Copyright (c) 2003 Martin Konold <martin.konold@erfrakon.de>
 *
 *    Written by Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 *    Updated by Stuart Binge <s.binge@codefusion.co.za>
 *
 *  This  program is free  software; you can redistribute  it and/or
 *  modify it  under the terms of the GNU  General Public License as
 *  published by the  Free Software Foundation; either version 2, or
 *  (at your option) any later version.
 *
 *  This program is  distributed in the hope that it will be useful,
 *  but WITHOUT  ANY WARRANTY; without even the  implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *  General Public License for more details.
 *
 *  You can view the  GNU General Public License, online, at the GNU
 *  Project's homepage; see <http://www.gnu.org/licenses/gpl.html>.
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
$smarty = new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', $menuitems[$sidx]['submenu'] );
$smarty->assign( 'maincontent', 'codefusion.tpl' );
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
