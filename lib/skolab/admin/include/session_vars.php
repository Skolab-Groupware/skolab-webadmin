<?php
/*
# (c) 2005 Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
# (c) 2003 Tassilo Erlewein <tassilo.erlewein@erfrakon.de>
# (c) 2003 Martin Konold <martin.konold@erfrakon.de>
# This program is Free Software under the GNU General Public License (>=v2).
# Read the file COPYING that comes with this packages for details.

*/

/*
 * Session variables fetched from LDAP
 *
 * TODO(steffen): Make those variables non-session variables.
 * We dont really need to store those in the session,
 * since we source this file on every invokation anyway.
 */

@session_start();

$_SESSION['fqdnhostname'] = "kolabserver.example.com";
$_SESSION['ldap_master_uri'] = "ldap://127.0.0.1:389";
$_SESSION['base_dn'] = "dc=example,dc=com";
$_SESSION['php_dn'] = "cn=nobody,cn=internal,dc=example,dc=com";
$_SESSION['php_pw'] = "PASSWORD";


/***********************************************************************
 * Global config
 */

$params = array();

/*
 * Which user classes can log in to the webgui?
 * Currently 4 user classes exist: user, admin, maintainer and manager
 */
$params['allow_user_classes'] = array( 'user', 'admin', 'maintainer', 'manager', 'domain-maintainer' );

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
