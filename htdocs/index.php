<?php
/*
 *  Copyright (c) 2004-2006 Klarälvdalens Datakonsult AB
 *  Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
 *
 *    Originally written by
 *    Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
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

require_once('Skolab/Admin/include/menu.php');

function exists_group( $group ) {
	global $ldap;
	$filter = '(&(objectClass=kolabGroupOfNames)(mail='.$ldap->escape($group).'))';
	$res = $ldap->search( $_SESSION['base_dn'], $filter, array( 'dn' ) );
	return ( $ldap->count($res) > 0 );
}

/**** Check for system aliases ****/
$maincontent = 'welcome.tpl';
if( $auth->group() == 'admin' ) {
	$maincontent = 'welcome-admin.tpl';
	$domains = $ldap->domains();
	foreach( $domains as $domain ) {
		if( (!exists_group( 'hostmaster@'.$domain ) ||
		     !exists_group( 'postmaster@'.$domain ) ||
		     !exists_group( 'abuse@'.$domain ) ||
		     !exists_group( 'virusalert@'.$domain ) ||
		     !exists_group( 'MAILER-DAEMON@'.$domain ))
		     && !HIDE_ADMINISTRATIVE_EMAILSETTINGS) {
			// Ok, user did not set up system aliases
			$maincontent = 'systemaliasnagscreen.tpl';
		}
	}
}

if ($auth->group() == 'user') {
	$maincontent = 'welcome-user.tpl';
}
if ($auth->group() == 'maintainer') {
	$maincontent = 'welcome-maintainer.tpl';
}
if ($auth->group() == 'domain-maintainer') {
	$maincontent = 'welcome-domain-maintainer.tpl';
}


/**** Insert into template and output ***/
$smarty = new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', 'Kolab' );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', array() );

//assign values fo different welcome pages, based on menu.php settings
$smarty->assign( 'dmurl',$menuitems['domain-maintainer']['url']);
$smarty->assign( 'murl',$menuitems['maintainer']['url']);
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
