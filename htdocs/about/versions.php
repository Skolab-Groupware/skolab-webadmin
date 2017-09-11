<?php
/*
 * Copyright (c) 2004 Klarälvdalens Datakonsult AB
 * Copyright (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 * Copyright (c) 2003-2006 Martin Konold <martin.konold@erfrakon.de>
 * Copyright (c) 2007 Intevation GmbH
 * Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
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

require_once('Skolab/Admin/include/mysmarty.php');
require_once('Skolab/Admin/include/headers.php');
require_once('Skolab/Admin/include/locale.php');
require_once('Skolab/Admin/include/authenticate.php');

/**** Authentication etc. ***/
$sidx = 'about';

require_once('Skolab/Admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Page logic ****/

// Remember to keep this list up to date when patching packages!
// The variable 'kolab_pkgs' is used to collect a packages that make up the
// kolab groupware server.
$kolabversions = shell_exec('
  ' . $RPM .' -q --qf "%{NAME}: %{VERSION}-%{RELEASE}\n" \
    ' . $kolab_pkgs . ' \
    | sort
');

$pearhordeversions = shell_exec('
  ' . $RPM . ' -q --qf "%{NAME}: %{VERSION}-%{RELEASE}\n" \
    ' . $pear_horde_pkgs . ' \
    | sort
');


# This is an openpkg test, difficult to see after make.
# but visible in the vanilla sources.
if ($WITHOPENPKG == "yes") {
	$openpkgversion = shell_exec("$RPM -q openpkg");
	$kolabpatchedversions = shell_exec("$RPM -qa|grep 'kolab[0-9]*$'" );
}

if( $kolabversion[0] == '@' ) {
	// Unofficial/non-openpkg package
	$kolabversion = 'unknown';
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
$smarty->assign( 'pearhordeversions', $pearhordeversions );
$smarty->assign( 'kolabpatchedversions', $kolabpatchedversions );
$smarty->assign( 'openpkgversion', $openpkgversion );
$smarty->assign( 'OPENPKG', $WITHOPENPKG );
$smarty->assign( 'maincontent', 'versions.tpl' );
$smarty->display('page.tpl');

/*
  Local variables:
  mode: php
  indent-tabs-mode: t
  tab-width: 4
  coding: utf-8
  End:
*/
 ?>
