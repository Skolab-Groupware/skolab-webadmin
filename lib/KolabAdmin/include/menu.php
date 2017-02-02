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

require_once('locale.php');

$menuitems = array();

if( $auth->group() == "admin" || $auth->group() == "maintainer" || $auth->group() == 'domain-maintainer' ) {
  $menuitems['user'] = array( 'name' => _('Users'),
							  'url'  => $topdir.'/user/',
							  'title' => _('Manage Email Users'),
							  'submenu' => array( 
												 array( 'name' => _('Create New User'),
														'url'  => 'user.php?action=create' )));
} else {
  $menuitems['user'] = array( 'name' => _('My User Settings'),
							  'url'  => $topdir.'/user/user.php?action=modify',
							  'title' => _('My User Settings'),
							  'submenu' => array(
												 array( 'name' => _('Mail Delivery'),
														'url'  => 'deliver.php'),
												 array( 'name' => _('Forward Email'),
														'url'  => 'forward.php' ),
												 array( 'name' => _('Vacation'),
														'url'  => 'vacation.php' ),
												array( 'name' => _('ActiveSync'),
														'url'  => 'activesync.php' ),
																		));
																		
	$menuitems['activesync'] = array( 'name' => _('ActiveSync'),
							  'url'  => $topdir.'/user/activesync.php',
							  'title' => _('ActiveSync'));																		
}
if( $auth->group() == "admin" || $auth->group() == "maintainer") {
  $menuitems['addressbook'] = array( 'name' => _('Addressbook'),
									 'url'  => $topdir.'/addressbook/',
									 'title' => _('Manage Address Book'),
									 'submenu' => array( 
														array( 'name' => _('Create New vCard'),
															   'url' => 'addr.php?action=create' )));

}
if( $auth->group() == "admin" || $auth->group() == "maintainer" || $auth->group() == 'domain-maintainer') {
  $menuitems['sf'] = array( 'name' => _('Shared Folder'),
							'url'  => $topdir.'/sharedfolder/',
							'title' => _('Manage Shared Folders'),
							'submenu' => array( 
											   array( 'name' => _('Add Shared Folder'),
													  'url' => 'sf.php?action=create' )));  
}
if( $auth->group() == 'admin' || $auth->group() == 'maintainer' || $auth->group() == 'domain-maintainer') {
  $menuitems['distlist'] = array( 'name' => _('Distribution Lists'),
									   'url'  => $topdir.'/distributionlist/',
									   'title' => _('Manage Distribution Lists'),
									   'submenu' => array(
														  array( 'name' => _('Create New List'),
   															 'url'   => 'list.php?action=create' ) ) );
}
if( $auth->group() == 'admin' ) {
  $menuitems['administrator'] = array( 'name' => _('Administrators'),
									   'url'  => $topdir.'/administrator/',
									   'title' => _('Manage Administrators'),
									   'submenu' => array(
														  array( 'name' => _('Create New Administrator'),
																 'url'   => 'admin.php?action=create' ) ) );
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									   'url'  => $topdir.'/domainmaintainer/',
									   'title' => _('Manage Domain Maintainers'),
									   'submenu' => array(
														  array( 'name' => _('Create New Domain Maintainer'),
																 'url'   => 'domainmaintainer.php?action=create' ) ) );
  $menuitems['maintainer'] = array( 'name' => _('Maintainers'),
									'url'  => $topdir.'/maintainer/',
									'title' => _('Manage Maintainers'),
									'submenu' => array(
													   array( 'name' => _('Create New Maintainer'),
															  'url'   => 'maintainer.php?action=create' ) ) );
} else if( $auth->group() == 'maintainer' ) {
  $mdn = $auth->dn();
  $menuitems['maintainer'] = array( 'name' => _('Maintainers'),
									'url'  => $topdir.'/maintainer/maintainer.php?action=modify&dn='.urlencode($mdn),
									'title' => _('Manage Maintainer') );  
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									   'url'  => $topdir.'/domainmaintainer/',
									   'title' => _('Manage Domain Maintainers'),
									   'submenu' => array(
														  array( 'name' => _('Create New Domain Maintainer'),
																 'url'   => 'domainmaintainer.php?action=create' ) ) );
} else if( $auth->group() == 'domain-maintainer' ) {
  $mdn = $auth->dn();
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									'url'  => $topdir.'/domainmaintainer/domainmaintainer.php?action=modify&dn='.urlencode($mdn),
									'title' => _('Manage Domain Maintainer') );  
}
if( $auth->group() == 'admin' ) {
  $menuitems['service'] = array( 'name' => _('Settings'),
								 'url'  => $topdir.'/settings/',
								 'title' => _('System Settings') );
}

$menuitems['about'] = array( 'name' => _('About Kolab'),
							 'url'  => $topdir.'/kolab/',
							 'title' => _('About Kolab'),
							 'submenu' => array( 
												array( 'name' => _('Kolab Systems'),
													   'url'  => 'kolabsystems.php' ),
												array( 'name' => _('Technology'),
													   'url'  => 'technology.php' ),
												));
if( $auth->group() == 'admin' || $auth->group() == 'maintainer' || $auth->group() == 'domain-maintainer') {
  $menuitems['about']['submenu'][] = array( 'name' => _('Versions'),
											'url'  => 'versions.php' );
}

/*
  Local variables:
  mode: php
  indent-tabs-mode: t
  tab-width: 4
  buffer-file-coding-system: utf-8
  End:
  vim:encoding=utf-8:
 */
?>
