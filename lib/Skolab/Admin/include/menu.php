<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *  Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
 *
 *    Originally written by
 *    Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 *    Updated by Bogomil Shopov <shopov@kolabsys.com>
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
							  'url'  => $webserver_web_prefix.'/user/',
							  'title' => _('Manage Email Users'),
							  'submenu' => array(
												 array( 'name' => _('Create New User'),
														'url'  => 'user.php?action=create' )));
} else {
  $menuitems['user'] = array( 'name' => _('My User Settings'),
							  'url'  => $webserver_web_prefix.'/user/user.php?action=modify',
							  'title' => _('My User Settings'),
							  'submenu' => array(
												 array( 'name' => _('Mail Delivery'),
														'url'  => 'deliver.php'),
												 array( 'name' => _('Forward Email'),
														'url'  => 'forward.php' ),
												 array( 'name' => _('Vacation'),
														'url'  => 'vacation.php' )
																	));
}
if( $auth->group() == "admin" || $auth->group() == "maintainer") {
  $menuitems['addressbook'] = array( 'name' => _('Addressbook'),
									 'url'  => $webserver_web_prefix.'/addressbook/',
									 'title' => _('Manage Address Book'),
									 'submenu' => array(
														array( 'name' => _('Create New vCard'),
															   'url' => 'addr.php?action=create' )));

}
if( $auth->group() == "admin" || $auth->group() == "maintainer" || $auth->group() == 'domain-maintainer') {
  $menuitems['sf'] = array( 'name' => _('Shared Folder'),
							'url'  => $webserver_web_prefix.'/sharedfolder/',
							'title' => _('Manage Shared Folders'),
							'submenu' => array(
											   array( 'name' => _('Add Shared Folder'),
													  'url' => 'sf.php?action=create' )));
}
if( $auth->group() == 'admin' || $auth->group() == 'maintainer' || $auth->group() == 'domain-maintainer') {
  $menuitems['distlist'] = array( 'name' => _('Distribution Lists'),
									   'url'  => $webserver_web_prefix.'/distributionlist/',
									   'title' => _('Manage Distribution Lists'),
									   'submenu' => array(
														  array( 'name' => _('Create New List'),
   															 'url'   => 'list.php?action=create' ) ) );
}
if( $auth->group() == 'admin' ) {
  $menuitems['administrator'] = array( 'name' => _('Administrators'),
									   'url'  => $webserver_web_prefix.'/administrator/',
									   'title' => _('Manage Administrators'),
									   'submenu' => array(
														  array( 'name' => _('Create New Administrator'),
																 'url'   => 'admin.php?action=create' ) ) );
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									   'url'  => $webserver_web_prefix.'/domainmaintainer/',
									   'title' => _('Manage Domain Maintainers'),
									   'submenu' => array(
														  array( 'name' => _('Create New Domain Maintainer'),
																 'url'   => 'domainmaintainer.php?action=create' ) ) );
  $menuitems['maintainer'] = array( 'name' => _('Maintainers'),
									'url'  => $webserver_web_prefix.'/maintainer/',
									'title' => _('Manage Maintainers'),
									'submenu' => array(
													   array( 'name' => _('Create New Maintainer'),
															  'url'   => 'maintainer.php?action=create' ) ) );
} else if( $auth->group() == 'maintainer' ) {
  $mdn = $auth->dn();
  $menuitems['maintainer'] = array( 'name' => _('Maintainers'),
									'url'  => $webserver_web_prefix.'/maintainer/maintainer.php?action=modify&dn='.urlencode($mdn),
									'title' => _('Manage Maintainer') );
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									   'url'  => $webserver_web_prefix.'/domainmaintainer/',
									   'title' => _('Manage Domain Maintainers'),
									   'submenu' => array(
														  array( 'name' => _('Create New Domain Maintainer'),
																 'url'   => 'domainmaintainer.php?action=create' ) ) );
} else if( $auth->group() == 'domain-maintainer' ) {
  $mdn = $auth->dn();
  $menuitems['domain-maintainer'] = array( 'name' => _('Domain Maintainers'),
									'url'  => $webserver_web_prefix.'/domainmaintainer/domainmaintainer.php?action=modify&dn='.urlencode($mdn),
									'title' => _('Manage Domain Maintainer') );
}
if( $auth->group() == 'admin' ) {
  $menuitems['service'] = array( 'name' => _('Settings'),
								 'url'  => $webserver_web_prefix.'/settings/',
								 'title' => _('System Settings') );
}

$menuitems['about'] = array( 'name' => _('About Skolab'),
							 'url'  => $webserver_web_prefix.'/about/',
							 'title' => _('About Skolab'),
							 'submenu' => array(
												array( 'name' => _('Skolab Groupware Project'),
													   'url'  => 'skolabgroupware.php' ),
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
