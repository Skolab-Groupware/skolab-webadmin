<?php
/*
 *  Copyright (c) 2005 Klarälvdalens Datakonsult AB
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

$errors = array();

/**** Authentication etc. ***/
$sidx = 'user';

if( $auth->group() != 'maintainer' && $auth->group() != 'admin') {
   array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Extract data from LDAP ***/

// read selector for register display
if (isset($HTTP_GET_VARS['alphaselect'])) $alphaselect = $HTTP_GET_VARS['alphaselect'];
else $alphaselect = "[A-F]";
if (isset($HTTP_GET_VARS['page'])) $page = $HTTP_GET_VARS['page'];
else $page = "1";

// Get all entries & dynamically split the letters with growing entries
if( !$errors ) {
  if (isset($_SESSION['base_dn'])) $base_dn = $_SESSION['base_dn'];
  else $base_dn = 'k=kolab';

  $privmembers = array_merge( $ldap->groupMembers( "cn=internal,$base_dn", 'admin' ),
							  $ldap->groupMembers( "cn=internal,$base_dn", 'maintainer' ) );

  $userfilter = "cn=*";
  $filterattr = $_REQUEST['filterattr'];
  $filtertype = $_REQUEST['filtertype'];
  $filtervalue = $_REQUEST['filtervalue'];
  if( !in_array( $filterattr, array( 'cn', 'uid', 'mail' ) ) ) $filterattr = 'cn';
  if( isset( $filtervalue ) && !empty( $filtervalue ) ) {
	switch( $filtertype ) {
	case 'contains': // contains
	  $userfilter = "$filterattr=*".$ldap->escape($filtervalue).'*';
	  break;
	case 'is': // is
	  $userfilter = "$filterattr=".$ldap->escape($filtervalue);
	  break;
	case 'begins': // begins with
	  $userfilter = "$filterattr=".$ldap->escape($filtervalue).'*';
	  break;
	case 'ends': // ends with
	  $userfilter = "$filterattr=*".$ldap->escape($filtervalue);
	  break;
	}
  }
  $alphalimit = '';
  if( isset( $_REQUEST['alphalimit']) ) {
	$ala='sn'; // alpha limit attibute
	switch( $_REQUEST['alphalimit'] ) {
	case a: $alphalimit = "(|($ala=a*)($ala=b*)($ala=c*)($ala=d*)($ala=e*)($ala=f*))"; break;
	case g: $alphalimit = "(|($ala=g*)($ala=h*)($ala=i*)($ala=j*)($ala=k*)($ala=l*))"; break;
	case m: $alphalimit = "(|($ala=m*)($ala=n*)($ala=o*)($ala=p*)($ala=q*)($ala=r*))"; break;
	case s: $alphalimit = "(|($ala=s*)($ala=t*)($ala=u*)($ala=v*)($ala=w*)($ala=x*)($ala=y*)($ala=z*))"; break;
	case other: $alphalimit = "(|($ala=æ*)($ala=ø*)($ala=å*)($ala=ä*)($ala=ö*)($ala=ü*)($ala=0*)($ala=1*)($ala=2*)($ala=3*)($ala=4*)($ala=5*)($ala=6*)($ala=7*)($ala=8*)($ala=9*))"; break;
	default: $alphalimit = '';
	}
  }
  $filter = "(&($userfilter)$alphalimit(objectclass=kolabInetOrgPerson)(uid=*)(mail=*)(sn=*))";
  $result = ldap_search($ldap->connection, $base_dn, $filter, array( 'uid', 'mail', 'sn', 'cn', 'kolabDeleteflag' ));

  if( $result ) {
	$count = ldap_count_entries($ldap->connection, $result);
	$title = _("Manage Email User ($count Users)");
	// if there are more than 2000 entries, split in 26 categories for every letter,
	// or if more than 50, put in groups, or else just show all.
	if (false && $count > 2000) {
	  // ... TODO
	  $template = 'userlistalpha.tpl';
	} else if( false && $count > 50 ) {
	  // ... TODO
	  $template = 'userlistgroup.tpl';
	}  else {
	  $template = 'userlistall.tpl';
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
        $mail = $attrs['mail'][0];
        $sn = $attrs['sn'][0];
        $cn = $attrs['cn'][0];
        $a = strlen($sn);
        $b = strlen($cn);
        $fn = substr($cn, 0, $b - $a);
		$dncomp = split( ',', $dn );
		if( in_array('cn=groups',$dncomp) ) {
		  $type = 'G';
		} else if( in_array('cn=resources',$dncomp) ) {
		  $type = 'R';
		} else if( in_array('cn=internal',$dncomp) ) {
		  $type = 'I';
		} else {
		  $type = 'U';
		}

		// skip admins and maintainers
		if( !array_key_exists( $dn, $privmembers ) ) {
		  $entries[] = array( 'dn' => $dn,
							  'sn' => $sn,
							  'fn' => $fn,
							  'type' => $type,
							  'mail' => $mail,
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
$smarty->assign( 'self_url', $PHP_SELF );

$smarty->assign( 'filterattrs', array( 'cn'   => _('Name'),
									   'mail' => _('Email'),
									   'uid'  => _('UID') ) );
$smarty->assign( 'filtertypes', array( 'contains'   => _('contains'),
									   'is' => _('is'),
									   'begins'  => _('begins with'),
									   'ends'  => _('ends with') ) );
$smarty->assign( 'filterattr', $filterattr );
$smarty->assign( 'filtertype', $filtertype );
$smarty->assign( 'filtervalue', $filtervalue );

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
