<?php

/*
 *  Copyright (c) 2004-2005 Klarälvdalens Datakonsult AB
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

$errors = array();

// *** Authentication etc. ***
$sidx = 'service';

if( $auth->group() != 'admin') {
	array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('Skolab/Admin/include/menu.php');

// *** Submenu for current page ***
$menuitems[$sidx]['selected'] = 'selected';

// *** Extract data from LDAP, fill forms, write back to LDAP ***


function postvalue( $varname )
{
	if( isset($_REQUEST[$varname]) && $_REQUEST[$varname] == true ) return 'TRUE';
	else return 'FALSE';
}

function extract_ldap_values()
{
	global $ldap;
	global $pop3;
	global $pop3s;
	global $imap;
	global $imaps;
	global $sieve;
	global $http;
	global $httpallowunauthfb;
	global $amavis;
	global $quotawarn;
	global $freebusypast;
	global $postfixmydomain;
	global $postfixmydestination;
	global $postfixmynetworks;
	global $postfixallowunauth;
	global $postfixrelayhost;
	global $postfixrelayport;
	global $kolabhost;
	global $kolabfilterverifyfrom;
	global $kolabfilterallowsender;
	global $kolabfilterrejectforgedfrom;

	// Get values from LDAP
	if (($result = ldap_read($ldap->connection, "k=kolab,".$_SESSION['base_dn'], '(objectclass=*)')) &&
	    ($entry = ldap_first_entry($ldap->connection, $result)) &&
	    ($attrs = ldap_get_attributes($ldap->connection, $entry))) {
		$pop3 = $attrs['cyrus-pop3'][0];
		$pop3s = $attrs['cyrus-pop3s'][0];
		$imap = $attrs['cyrus-imap'][0];
		$imaps = $attrs['cyrus-imaps'][0];
		$sieve = $attrs['cyrus-sieve'][0];
		$http = $attrs['apache-http'][0];
		$httpallowunauthfb = $attrs['apache-allow-unauthenticated-fb'][0];
		$amavis = $attrs['postfix-enable-virus-scan'][0];
		$quotawarn = $attrs['cyrus-quotawarn'][0];
		$freebusypast = $attrs['kolabFreeBusyPast'][0];
		$postfixmydomain = $attrs['postfix-mydomain'][0];
		$postfixmydestination = $attrs['postfix-mydestination'];
		unset($postfixmydestination['count']);
		sort($postfixmydestination);
		unset( $attrs['postfix-mynetworks']['count'] );
		$postfixmynetworks = join(', ',$attrs['postfix-mynetworks']);
		$postfixallowunauth = $attrs['postfix-allow-unauthenticated'][0];
		$postfixrelayhost = $attrs['postfix-relayhost'][0];
		$postfixrelayport = $attrs['postfix-relayport'][0];
		$kolabhost = $attrs['kolabHost'];
		unset( $kolabhost['count'] );
		$kolabfilterverifyfrom = $attrs['kolabfilter-verify-from-header'][0];
		$kolabfilterallowsender = $attrs['kolabfilter-allow-sender-header'][0];
		$kolabfilterrejectforgedfrom = $attrs['kolabfilter-reject-forged-from-header'][0];
		ldap_free_result($result);
	}
}

function toboolstr( $b ) {
	return ( $b == 'TRUE' )?'true':'false';
}

extract_ldap_values();

$domains = $ldap->domains();
$domain_count = 0;
foreach( $domains as $domain ) {

	// Write back to LDAP
	if( $_REQUEST['submitsystemalias_'.$domain_count] ) {
		$mail = trim($_REQUEST['systemaliasmail_'.$domain_count]);
		$dn = $ldap->dnForMailOrAlias( $mail );
		if( !$dn ) {
			$errors[] = sprintf(_("No account found for email address %s"), $mail);
		} else {
			foreach( array( 'postmaster', 'hostmaster', 'abuse', 'virusalert', 'MAILER-DAEMON' ) as $group ) {
				$gadr = $group.'@'.$domain;
				$attrs = array( 'objectClass' => array( 'top', 'kolabGroupOfNames' ),
				                'cn' => $gadr,
				                'mail' => $gadr,
				                'member' => $dn
				);
				if( !ldap_add( $ldap->connection, "cn=$gadr,".$_SESSION['base_dn'], $attrs ) ) {
					$errors[] = sprintf(_("LDAP Error: Failed to add distribution list %s: %s"), $gadr, $ldap->error());
				} else {
					$messages[] = sprintf( _("Successfully created distribution list %s"), $gadr);
				}
			}
		}
	}
	$domain_count++;
}

if( $_REQUEST['submitservices'] ) {
	$attrs = array();
	if( postvalue( 'pop3' ) != $pop3 )   $attrs['cyrus-pop3'] = postvalue( 'pop3' );
	if( postvalue( 'pop3s' ) != $pop3s ) $attrs['cyrus-pop3s'] = postvalue( 'pop3s' );
	if( postvalue( 'imap' ) != $imap )   $attrs['cyrus-imap'] = postvalue( 'imap' );
	if( postvalue( 'imaps' ) != $imaps ) $attrs['cyrus-imaps'] = postvalue( 'imaps' );
	if( postvalue( 'sieve' ) != $sieve ) $attrs['cyrus-sieve'] = postvalue( 'sieve' );
	if( postvalue( 'http' ) != $http )    $attrs['apache-http'] = postvalue( 'http' );
	if( postvalue( 'amavis' ) != $amavis )  $attrs['postfix-enable-virus-scan'] = postvalue( 'amavis' );

	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf( _("LDAP Error: failed to modify kolab configuration object: %s"),
	                     ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submitquotawarn'] ) {
	$attrs = array();
	$attrs['cyrus-quotawarn'] = trim( $_REQUEST['quotawarn'] );
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submithttpallowunauthfb'] ) {
	$attrs = array();
	$attrs['apache-allow-unauthenticated-fb'] = postvalue( 'httpallowunauthfb' );
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submitfreebusypast'] ) {
	$attrs = array();
	$value = trim( $_REQUEST['freebusypast'] );
	if( $value == '' ) $value = array();
	$attrs['kolabFreeBusyPast'] = $value;
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submitpostfixmynetworks'] ) {
	$attrs = array();
	$attrs['postfix-mynetworks'] = preg_split( "/[\s,]+/", trim( $_REQUEST['postfixmynetworks'] ) );
	//if( $attrs['postfix-mynetworks'] == '' ) $attrs['postfix-mynetworks'] = array();
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submitpostfixallowunauth'] ) {
	$attrs = array();
	$attrs['postfix-allow-unauthenticated'] = postvalue( 'postfixallowunauth' );
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

if( $_REQUEST['submitkolabfilter'] ) {
	$attrs = array(
				 'kolabfilter-verify-from-header'        => postvalue( 'kolabfilterverifyfrom' ),
				 'kolabfilter-allow-sender-header'       => postvalue( 'kolabfilterallowsender' ),
				 'kolabfilter-reject-forged-from-header' => $_REQUEST['kolabfilterrejectforgedfrom']=='TRUE'?'TRUE':'FALSE' );
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
 }

if( $_REQUEST['submitpostfixrelayhost'] ) {
	$host_val = trim( $_REQUEST['postfixrelayhost'] );
	$port_val = trim( $_REQUEST['postfixrelayport'] );
	if( $host_val == '' ) $host_val = array();
	if( $port_val == '' ) $port_val = array();
	$attrs = array();
	$attrs['postfix-relayhost'] = $host_val;
	$attrs['postfix-relayport'] = $port_val;
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
	                    ldap_error($ldap->connection));
	}
}

// Delete domain
if( $_REQUEST['deletedestination'] ) {
	extract_ldap_values();
	debug_var_dump($_REQUEST['adestination']);
	$key = array_search( trim($_REQUEST['adestination']),$postfixmydestination);
	if( $key !== false ) {
		unset( $postfixmydestination[ $key ] );
	}
	$postfixmydestination = array_values( $postfixmydestination );
	debug_var_dump($postfixmydestination);
	$attrs = array();
	$attrs['postfix-mydestination'] = $postfixmydestination;
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
		$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
		                    ldap_error($ldap->connection));
	}
	$domain_obj_dn = 'cn='.$ldap->escape(trim($_REQUEST['adestination'])).',cn=domains,cn=internal,'.$_SESSION['base_dn'];
	debug("Trying to delete $domain_obj_dn");
	if( !$errors && $ldap->read($domain_obj_dn) && !ldap_delete($ldap->connection, $domain_obj_dn ) ) {
		$errors[] = sprintf(_("LDAP Error: Failed to delete domain object %s: %s"), $domain_obj_dn,
		                    ldap_error($ldap->connection));
	}
}
// Add domain
if( $_REQUEST['adddestination'] ) {
	extract_ldap_values();
	if( trim($_REQUEST['adestination']) ) {
		$postfixmydestination[] = trim($_REQUEST['adestination']);
		$attrs = array();
		$attrs['postfix-mydestination'] = $postfixmydestination;
		if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
			$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
			                    ldap_error($ldap->connection));
		}
	}
}

// Delete kolabhost
if( $_REQUEST['deletekolabhost'] ) {
	extract_ldap_values();
	$key = array_search( trim($_REQUEST['akolabhost']),$kolabhost);
	if( $key !== false ) {
		unset( $kolabhost[ $key ] );
	}
	$kolabhost = array_values( $kolabhost );
	$attrs = array();
	$attrs['kolabhost'] = $kolabhost;
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
		$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
		                    ldap_error($ldap->connection));
	}
}

// Add kolabhost
if( $_REQUEST['addkolabhost'] ) {
	extract_ldap_values();
	if( trim($_REQUEST['akolabhost']) ) {
		$kolabhost[] = trim($_REQUEST['akolabhost']);
		$attrs = array();
		$attrs['kolabhost'] = $kolabhost;
		if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
			$errors[] = sprintf(_("LDAP Error: failed to modify kolab configuration object: %s"),
			                    ldap_error($ldap->connection));
		}
	}
}

// Fill in output form
extract_ldap_values();
$entries = array( array( 'service' => 'pop3', 'name'  => _('POP3 Service'), 'enabled' => toboolstr( $pop3 ) ),
                  array( 'service' => 'pop3s', 'name' => _('POP3/SSL service (TCP port 995)'), 'enabled' => toboolstr( $pop3s ) ),
                  array( 'service' => 'imap', 'name'  => _('IMAP Service'), 'enabled' => toboolstr( $imap ) ),
                  array( 'service' => 'imaps', 'name' => _('IMAP/SSL Service (TCP port 993)'), 'enabled' => toboolstr( $imaps ) ),
                  array( 'service' => 'sieve', 'name' => sprintf(_('Sieve service (TCP port %s)'), 2000), 'enabled' => toboolstr( $sieve ) ),
                  array( 'service' => 'http', 'name'  => _('FreeBusy Service via HTTP (in addition to HTTPS)'), 'enabled' => toboolstr( $http ) ),
                  array( 'service' => 'amavis', 'name' => _('Amavis Email Scanning (Virus/Spam)'), 'enabled' => toboolstr( $amavis ) ) );

// *** Check for system aliases ****
function exists_group( $group ) {
	global $ldap;
	$filter = '(&(objectClass=kolabGroupOfNames)(mail='.$ldap->escape($group).'))';
	$res = $ldap->search( $_SESSION['base_dn'], $filter, array( 'dn' ) );
	return ( $ldap->count($res) > 0 );
}

// *** Insert into template and output ***
$smarty = new MySmarty();
$smarty->assign( 'errors', $errors );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'entries', $entries );
$smarty->assign( 'quotawarn', $quotawarn );
$smarty->assign( 'httpallowunauthfb', toboolstr($httpallowunauthfb) );
$smarty->assign( 'freebusypast', $freebusypast );
$smarty->assign( 'postfixmydestination', $postfixmydestination );
$smarty->assign( 'postfixmynetworks', $postfixmynetworks );
$smarty->assign( 'postfixallowunauth', toboolstr($postfixallowunauth) );
$smarty->assign( 'postfixrelayhost', $postfixrelayhost );
$smarty->assign( 'postfixrelayport', $postfixrelayport );
$smarty->assign( 'kolabfilterverifyfrom', toboolstr($kolabfilterverifyfrom) );
$smarty->assign( 'kolabfilterallowsender', toboolstr($kolabfilterallowsender) );
$smarty->assign( 'kolabfilterrejectforgedfrom', toboolstr($kolabfilterrejectforgedfrom) );
$smarty->assign( 'kolabhost', $kolabhost );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems',
                                 array_key_exists('submenu',
                                                              $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'maincontent', 'settings.tpl' );

$systemaliasconf = array();

// Section administrative email addresses may be hidden by
// setting HIDE_ADMINISTRATIVE_EMAILSETTINGS to 'true' in
// php/admin/include/conf.php
if( $auth->group() == 'admin' && !HIDE_ADMINISTRATIVE_EMAILSETTINGS) {
	$domain_count = 0;
	foreach( $ldap->domains() as $domain ) {
		if( !exists_group( 'hostmaster@'.$domain ) ||
		    !exists_group( 'postmaster@'.$domain ) ||
		    !exists_group( 'abuse@'.$domain ) ||
		    !exists_group( 'virusalert@'.$domain ) ||
		    !exists_group( 'MAILER-DAEMON@'.$domain ) ) {
			// Ok, user did not set up system aliases
			$systemaliasconf[] = array( 'n'=>$domain_count, 'domain'=>$domain );
		}
	$domain_count++;
	}
}

$smarty->assign( 'systemaliasconf', $systemaliasconf );
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
