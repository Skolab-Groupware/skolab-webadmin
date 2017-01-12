<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *  Copyright (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 *  Copyright (c) 2003 Martin Konold <martin.konold@erfrakon.de>
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

$errors = array();

/**** Authentication etc. ***/
$sidx = 'distlist';

if( $auth->group() != 'maintainer' && $auth->group() != 'admin') {
   array_push($errors, _("Error: You don't have Permissions to access this Menu") );
}

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Extract data from LDAP ***/

// Get all entries & dynamically split the letters with growing entries
if( !$errors ) {
  if (isset($_SESSION['base_dn'])) $base_dn = $_SESSION['base_dn'];
  else $base_dn = 'k=kolab';
  $filter = "(&(cn=*)(objectclass=kolabGroupOfNames))";
  $result = ldap_search($ldap->connection, $base_dn, $filter);
  if( $result ) {
	$count = ldap_count_entries($ldap->connection, $result);
	$title = sprintf( _("Manage Distribution Lists (%d Lists)"), $count );
	$template = 'distlistall.tpl';
	ldap_sort($ldap->connection,$result,'cn');
	$entry = ldap_first_entry($ldap->connection, $result);
	while( $entry ) {
	  $attrs = ldap_get_attributes($ldap->connection, $entry);
	  $dn = ldap_get_dn($ldap->connection,$entry);
	  $cn = $attrs['cn'][0];
	  if( $cn != 'admin' && $cn != 'maintainer' ) {
		$deleted = array_key_exists('kolabDeleteflag',$attrs)?$attrs['kolabDeleteflag'][0]:"FALSE";
		$kolabhomeserver = _('not yet implemented');
		$internal = (strpos($dn,"cn=internal")!==false);
		$entries[] = array( 'dn' => $dn,
							'cn' => $cn,
							'deleted' => $deleted,
							'internal' => $internal );
	  }
	  $entry = ldap_next_entry( $ldap->connection,$entry );
	}
  }
}


/**** Insert into template and output ***/
$smarty =& new MySmarty();
$smarty->assign( 'errors', $errors );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'entries', $entries );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', 
				 array_key_exists('submenu', 
								  $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'maincontent', $template );
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
