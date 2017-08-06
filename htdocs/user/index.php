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
require_once('skolab/admin/include/form.class.php');

$errors = array();

/**** Authentication etc. ***/
$sidx = 'user';

if( $auth->group() != 'maintainer' && $auth->group() != 'admin' && $auth->group() != 'domain-maintainer' ) {
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


  $userfilter = "uid=*";
  $filterattr = KolabForm::getRequestVar('filterattr');
  $filtertype = KolabForm::getRequestVar('filtertype');
  $filtervalue = KolabForm::getRequestVar('filtervalue');
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
  $sublist = '';
  $alphagroup = '';
  // Disabled for now
  if( false && isset($_REQUEST['alphalimit']) ) {
	$ala='sn'; // alpha limit attibute
	$a = $_REQUEST['alphalimit'];
	if( $a == "other" ) {
	  $alphalimit = "(|($ala=æ*)($ala=ø*)($ala=å*)($ala=ä*)($ala=ö*)($ala=ü*)($ala=0*)($ala=1*)($ala=2*)($ala=3*)($ala=4*)($ala=5*)($ala=6*)($ala=7*)($ala=8*)($ala=9*))";
	} else if( !empty($a)) {
	  $alphalimit ="($ala=$a*)";
	  $sublist = $a[0];
	}
  } else if( isset( $_REQUEST['alphagroup']) ) {
	$ala='sn'; // alpha limit attibute
	$alphagroup = $_REQUEST['alphagroup'];
	switch( $_REQUEST['alphagroup'] ) {
	case 'a': $alphalimit = "(|($ala=a*)($ala=b*)($ala=c*)($ala=d*)($ala=e*)($ala=f*))"; break;
	case 'g': $alphalimit = "(|($ala=g*)($ala=h*)($ala=i*)($ala=j*)($ala=k*)($ala=l*))"; break;
	case 'm': $alphalimit = "(|($ala=m*)($ala=n*)($ala=o*)($ala=p*)($ala=q*)($ala=r*))"; break;
	case 's': $alphalimit = "(|($ala=s*)($ala=t*)($ala=u*)($ala=v*)($ala=w*)($ala=x*)($ala=y*)($ala=z*))"; break;
	case 'other': $alphalimit = "(|($ala=æ*)($ala=ø*)($ala=å*)($ala=ä*)($ala=ö*)($ala=ü*)($ala=0*)($ala=1*)($ala=2*)($ala=3*)($ala=4*)($ala=5*)($ala=6*)($ala=7*)($ala=8*)($ala=9*))"; break;
	default: $alphalimit = '';
	}
  }
  $domains = $ldap->domainsForMaintainerDn($auth->dn());
  #debug_var_dump($domains);
  if( is_array($domains) ) {
	$domainfilter='';
	foreach( $domains as $dom ) {
	  $domainfilter .= '(mail=*@'.$ldap->escape($dom).')';
	}
	if( $domainfilter ) $domainfilter = "(|$domainfilter)";
  } else {
	$domainfilter= "";
  }
  $filter = "(&($userfilter)$domainfilter$alphalimit(objectclass=kolabInetOrgPerson)(uid=*)(mail=*)(sn=*))";
  debug("filter is \"$filter\"");
  $result = ldap_search($ldap->connection, $base_dn, $filter, array( 'uid', 'mail', 'sn', 'cn', 'kolabDeleteflag' ));

  if( $result ) {
	$count = ldap_count_entries($ldap->connection, $result);
	$title = sprintf(_("Manage Email User (%d Users)"), $count);
	// if there are more than 2000 entries, split in 26 categories for every letter,
	// or if more than 50, put in groups, or else just show all.
	if ( $count > 2000) {
	  // ... TODO
	  //$template = 'userlistalpha.tpl';
	  $template = 'userlisterror.tpl';
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
		$fn = KolabLDAP::getGivenName($cn, $sn);
		$dncomp = preg_split( '/,/', $dn );
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

		  $entries[] = array( 'dn' => $dn,
							  'sn' => $sn,
							  'fn' => $fn,
							  'type' => $type,
							  'mail' => $mail,
							  'uid' => $uid,
							  'deleted' => $deleted );

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
$smarty->assign( 'self_url', $_SERVER['PHP_SELF'] );
$smarty->assign( 'alphagroup', $alphagroup );
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

$smarty->assign( 'sublist', $sublist );
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
