<?php

/*
 *  Copyright (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
 *  Copyright (c) 2003 Martin Konold <martin.konold@erfrakon.de>
 *  Copyright (c) 2005 Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 *  Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
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

require_once('config.php');

@session_start();

/*
 * Session variables fetched from LDAP
 *
 * TODO(steffen): Make those variables non-session variables.
 * We dont really need to store those in the session,
 * since we source this file on every invokation anyway.
 */

$_SESSION['fqdnhostname'] = "skolabserver.example.com";
$_SESSION['ldap_master_uri'] = "ldap://127.0.0.1:389";
$_SESSION['base_dn'] = "dc=example,dc=com";
$_SESSION['php_dn'] = "cn=nobody,cn=internal,dc=example,dc=com";
$_SESSION['php_pw'] = "PASSWORD";

/***********************************************************************
 * Global config
 */

$params = array();

/*
 * protocol to use (http, https)
 */
$params['http_protocol'] = 'http';

/*
 * URL to the Skolab Webadmin Tool
 */
$params['skolab_webadmin_url'] = $params['http_protocol'].'://'.$_SESSION['fqdnhostname'].'/'.$webserver_web_prefix;

/*
 * URL to the Skolab (i.e. Horde) Webmailer
 */
$params['skolab_webmailer_url'] = $params['http_protocol'].'://'.$_SESSION['fqdnhostname'];

/*
 * if true, then we return to the login page after logout,
 * if false, we open $params['skolab_webmailer_url'] after logout
 */
$params['return_to_login_after_logout'] = true;

/*
 * Which user classes can log in to the webgui?
 * Currently 4 user classes exist: user, admin, maintainer and manager
 */
$params['allow_user_classes'] = array( 'user', 'admin', 'maintainer', 'manager', 'domain-maintainer' );

/*
 * Control if accouts with CN= DNs shall be renamed to accounts with uid= DNs
 */
$params['rename_to_uid_accounts'] = false;

/*
 * Array to configure visibility/access of LDAP attributes to user's account object
 *
 * Possible values for attribute is
 *
 * 'ro' (readonly)
 * 'rw' (read/write)
 * 'hidden' (atribute removed from display)
 * 'mandatory' (read/write and must not be empty)
 *
 * If an attribute is not in this array, it defaults to 'rw'
 *
 * Note, attributes correspond to form attribute names and not LDAP attribute names.
 *
 * TODO(steffen): Make form and LDAP attributes the same.
 */

$params['attribute_access'] = array(
			 /*
                         // Examples
			 'firstname'  => 'ro',
			 'lastname'   => 'ro',
			 'password'   => 'rw',
			 'mail'       => 'ro',
			 'uid'        => 'ro',
			 'title'      => 'ro',
			 'roomNumber' => 'mandatory',
			 'kolabdelegate'  => 'ro',
			 'telephoneNumber' => 'hidden'
			 */
);

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
