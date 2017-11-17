<?php
/*
 * Copyright (c) 2004 Klarälvdalens Datakonsult AB
 * Copyright (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 * Copyright (c) 2003 Martin Konold <martin.konold@erfrakon.de>
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

$errors = array();

// *** Authentication etc. ***
$sidx = 'sf';

if( $auth->group() != 'maintainer' && $auth->group() != 'admin' && $auth->group() != 'domain-maintainer' ) {
	debug("auth->group=".$auth->group());
	array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('Skolab/Admin/include/menu.php');

// *** Submenu for current page ***
$menuitems[$sidx]['selected'] = 'selected';

// *** Extract data from LDAP ***

function prepare_domain_filter_component($str) {
	return '(cn=*@'.SkolabLDAP::escape($str).')';
}

// Get all entries & dynamically split the letters with growing entries
$entries = array();
if( !$errors ) {
	if (isset($_SESSION['base_dn'])) $base_dn = $_SESSION['base_dn'];
	else $base_dn = 'k=kolab';
	if( $group == 'domain-maintainer' ) {
		$domainfilter = '(|'.join('', array_map( 'prepare_domain_filter_component',
		                 $ldap->domainsForMaintainerDn($auth->dn()))).')';
	} else {
		$domainfilter = '(cn=*)';
	}
	debug("domainfilter=$domainfilter");
	$filter = "(&$domainfilter(objectclass=kolabSharedFolder))";
	$result = ldap_search($ldap->connection, $base_dn, $filter);
	if( $result ) {
		$count = ldap_count_entries($ldap->connection, $result);
		$title = sprintf(_("Manage Shared Folders (%d Folders)"), $count);
		$template = 'sflistall.tpl';
		ldap_sort($ldap->connection,$result,'cn');
		$entry = ldap_first_entry($ldap->connection, $result);
		while( $entry ) {
			$attrs = ldap_get_attributes($ldap->connection, $entry);
			$dn = ldap_get_dn($ldap->connection,$entry);
			$deleted = array_key_exists('kolabDeleteflag',$attrs)?$attrs['kolabDeleteflag'][0]:"FALSE";
			$cn = $attrs['cn'][0];
			$kolabhomeserver = $attrs['kolabHomeServer'][0];
			$folderTypeMap = array ( '' => _('Unspecified'),
			                               'mail' => _('Mails'),
			                               'task' => _('Tasks'),
			                               'journal' => _('Journals'),
			                               'event' => _('Events'),
			                               'contact' => _('Contacts'),
			                               'note' => _('Notes'));
			if( in_array('kolabFolderType',$attrs) ) $folderType = $folderTypeMap[$attrs['kolabFolderType'][0]];
			else $folderType = $folderTypeMap[''];

			$entries[] = array( 'dn' => $dn,
			                                 'cn' => $cn,
			                                 'kolabhomeserver' => $kolabhomeserver,
			                                 'foldertype' => $folderType,
			                                 'deleted' => $deleted );
			$entry = ldap_next_entry( $ldap->connection,$entry );
		}
	}
}

// *** Insert into template and output ***
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
