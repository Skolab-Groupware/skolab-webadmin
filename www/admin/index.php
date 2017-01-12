<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *
 *    Written by Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
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

require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');

/**** Authentication etc. ***/

require_once('admin/include/menu.php');

function exists_group( $group ) {
  global $ldap;
  $filter = '(&(objectClass=kolabGroupOfNames)(cn='.$ldap->escape($group).'))';
  $res = $ldap->search( $_SESSION['base_dn'], $filter, array( 'dn' ) );
  return ( $ldap->count($res) > 0 );
}

/**** Check for system aliases ****/
$maincontent = 'welcome.tpl';
if( $auth->group() == 'admin' ) {
  if( !exists_group( 'hostmaster' ) ||
	  !exists_group( 'postmaster' ) ||
	  !exists_group( 'abuse' ) ||
	  !exists_group( 'virusalert' ) ||
	  !exists_group( 'MAILER-DAEMON' ) ) {
	// Ok, user did not set up system aliases
	$maincontent = 'systemaliasnagscreen.tpl';
  }
}

/**** Insert into template and output ***/
$smarty = new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', 'Kolab' );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', array() );
$smarty->assign( 'maincontent', $maincontent );
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
