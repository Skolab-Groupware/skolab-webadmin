<?php
/*
 *  Copyright (c) 2004-2005 Klarälvdalens Datakonsult AB
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

require_once('skolab/admin/include/mysmarty.php');
require_once('skolab/admin/include/headers.php');
require_once('skolab/admin/include/locale.php');
require_once('skolab/admin/include/authenticate.php');

$errors = array();

/**** Authentication etc. ***/
$sidx = 'administrator';

if( $auth->group() != 'admin' ) {
  debug("auth->group=".$auth->group());
  array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('skolab/admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Extract data from LDAP ***/

// read selector for register display
if (isset($HTTP_GET_VARS['alphaselect'])) $alphaselect = $HTTP_GET_VARS['alphaselect'];
else $alphaselect = "[A-F]";
if (isset($HTTP_GET_VARS['page'])) $page = $HTTP_GET_VARS['page'];
else $page = "1";

// Get all entries & dynamically split the letters with growing entries
$entries = array();
if( !$errors ) {
  if (isset($_SESSION['base_dn'])) $base_dn = $_SESSION['base_dn'];
  else $base_dn = 'k=kolab';

  $maintainers = $ldap->groupMembers( "cn=internal,$base_dn", 'admin' );

  $filter = "(&(cn=*)(objectclass=inetOrgPerson)(!(uid=manager))(sn=*)(uid=*))";
  $result = ldap_search($ldap->connection, $base_dn, $filter, array( 'uid', 'sn', 'cn', 'kolabDeleteflag' ));

  if( $result ) {
	$count = count($maintainers)-1;
	$title = _('Manage Administrators (').$count._(' Administrators)');
	// if there are more than 2000 entries, split in 26 categories for every letter,
	// or if more than 50, put in groups, or else just show all.
	if (false && $count > 2000) {
	  // ... TODO
	  $template = 'adminlistalpha.tpl';
	} else if( false && $count > 50 ) {
	  // ... TODO
	  $template = 'adminlistgroup.tpl';
	}  else {
	  $template = 'adminlistall.tpl';
	  $starttime = getmicrotime();
	  ldap_sort($ldap->connection,$result,'sn');
	  $endtime = getmicrotime();
	  //print "sorting took ".($endtime-$starttime)."<br/>";
	  $entry = ldap_first_entry($ldap->connection, $result);
	  while( $entry ) {
		$attrs = ldap_get_attributes($ldap->connection, $entry);
		$dn = ldap_get_dn($ldap->connection,$entry);
		$deleted = array_key_exists('kolabDeleteflag',$attrs)?$attrs['kolabDeleteflag'][0]:"FALSE";
		$uid = $attrs['uid'][0];
		$sn = $attrs['sn'][0];
		$cn = $attrs['cn'][0];
		$fn = KolabLDAP::getGivenName($cn, $sn);
		// skip admins and maintainers
		if( array_key_exists( $dn, $maintainers ) ) {
		  $entries[] = array( 'dn' => $dn,
							  'sn' => $sn,
							  'fn' => $fn,
							  'uid' => $uid,
							  'deleted' => $deleted );
		}
		$entry = ldap_next_entry( $ldap->connection,$entry );
	  }
	}
  }
}

/**** Insert into template and output ***/
$smarty = new MySmarty();
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
