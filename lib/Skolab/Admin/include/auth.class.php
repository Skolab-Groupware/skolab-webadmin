<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
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

require_once('ldap.class.php');
require_once('debug.php');
require_once('mysmarty.php');
require_once('locale.php');
require_once('session_vars.php');

class SkolabAuth {
	function __construct ( $do_auth = true, $params = array() ) {
		global $params;
		$this->params = $params;
		if( isset( $_GET['logout'] ) || isset( $_POST['logout'] ) ) {
			$this->logout();
		} else if( $do_auth ) {
			$this->authenticate();
		}
	}

	function authenticate() {
		global $ldap;
		$this->error_string = false;
		if( !isset( $ldap ) ) {
			$this->error_string = _("Server error, no ldap object!");
			return false;
		}

		// Anon. bind first
		if( !$ldap->bind( $_SESSION['php_dn'],  $_SESSION['php_pw'] ) ) {
			$this->error_string = _("Could not bind to LDAP server: ").$ldap->error();
			$this->gotoLoginPage();
		}
		if( $this->isAuthenticated() ) {
			$bind_result = $ldap->bind( $_SESSION['auth_dn'], $_SESSION['auth_pw'] );
		} else {
			$bind_result = false;
		}
		if( !$bind_result ) {
			// Anon. bind first
			if( !$ldap->bind() ) {
				$this->error_string = _("Could not bind to LDAP server");
				$this->gotoLoginPage();
			}
			// User not logged in, check login/password
			if( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
				$dn = $ldap->dnForUid( $_POST['username'] );
				if (!$dn) {
					$dn = $ldap->dnForMail( $_POST['username'] ); // try mail attribute
				}
				if( $dn ) {
					$auth_user = $ldap->uidForDn( $dn );
					$auth_group = $ldap->groupForUid( $auth_user );
					$tmp_group = ($auth_user=='manager')?'manager':$auth_group;
					if( !in_array( $tmp_group, $this->params['allow_user_classes'] ) ) {
						$this->error_string = _("User class '$tmp_group' is denied access");
						$this->gotoLoginPage();
					}
					$bind_result = $ldap->bind( $dn, $_POST['password'] );
					if( $bind_result ) {
						// All OK!
						$_SESSION['auth_dn'] = $dn;
						$_SESSION['auth_user'] = $auth_user;
						$_SESSION['auth_pw'] = $_POST['password'];
						$_SESSION['auth_group'] = $auth_group;
						$_SESSION['remote_ip'] = $_SERVER['REMOTE_ADDR'];
						return true;
					} else {
						$this->error_string = _("Wrong username or password");
						$this->gotoLoginPage();
					}
				} else {
					$this->error_string = _("Wrong username or password");
					//$this->error_string = "Dn not found";
					$this->gotoLoginPage();
				}
			} else {
				//$this->error_string = _('Please log in as a valid user');
				$this->gotoLoginPage();
				// noreturn
			}
		} else {
			// All OK, user already logged in
			return true;
		}
	}

	function logout() {
		session_unset();
		session_destroy();
		$this->error_string = false;
		$this->gotoLoginPage();
	}

	function handleLogin() {
		if( isset( $_POST['login'] ) ) {
			$this->authenticate();
		} else if( isset( $_POST['logout'] ) ) {
			$this->logout();
		}
	}

	function gotoLoginPage() {
		global $topdir;
		global $webserver_web_prefix;
		$smarty = new MySmarty();
		$smarty->assign( 'topdir', $topdir );
		$smarty->assign( 'uid', '' );
		$smarty->assign( 'group', '' );
		$smarty->assign( 'errors', array() );
		$smarty->assign( 'messages', array() );
		$smarty->assign( 'page_title', _('Skolab Groupware Login') );
		$smarty->assign( 'menuitems', array() );
		$smarty->assign( 'submenuitems', array() );
		if( $this->error() ) $smarty->assign( 'errors', array( $this->error() ) );
		$smarty->assign( 'maincontent', 'login.tpl' );
		$smarty->assign( 'webserver_web_prefix', $webserver_web_prefix );
		$smarty->display('page.tpl');
		exit();
	}

	function isAuthenticated() {
		return isset( $_SESSION['auth_dn'] ) && $_SESSION['remote_ip'] == $_SERVER['REMOTE_ADDR'];
	}

	function dn() {
		if( $this->isAuthenticated() ) return $_SESSION['auth_dn'];
		else return false;
	}

	function uid() {
		if( $this->isAuthenticated() ) return $_SESSION['auth_user'];
		else return false;
	}

	function group() {
		if( $this->isAuthenticated() ) return $_SESSION['auth_group'];
	}

	function password() {
		if( $this->isAuthenticated() ) {
			return $_SESSION['auth_pw'];
		}
		else return false;
	}

	function setDn( $dn ) {$_SESSION['auth_dn'] = $dn;}
	function setUid( $uid ) {$_SESSION['auth_user'] = $uid;}
	function setPassword( $pw ) {$_SESSION['auth_pw'] = $pw;}

	function error() {
		return $this->error_string;
	}

	var $error_string = false;
	var $params;
}

/*
  Local variables:
  mode: php
  indent-tabs-mode: t
  tab-width: 4
  buffer-file-coding-system: utf-8
  End:
 */
?>
