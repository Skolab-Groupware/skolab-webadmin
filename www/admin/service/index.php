<?php
require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');

$errors = array();

/**** Authentication etc. ***/
$sidx = 'service';

if( $auth->group() != 'admin') {
   array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Extract data from LDAP, fill forms, write back to LDAP ***/


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
  global $ftp;
  global $http;
  global $httpallowunauthfb;
  global $amavis;
  global $quotawarn;
  global $freebusypast;
  global $postfixmydomain;
  global $postfixmynetworks;
  global $postfixallowunauth;
  global $postfixrelayhost;
  global $postfixrelayhostmx;
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
	$ftp = $attrs['proftpd-ftp'][0];
	$http = $attrs['apache-http'][0];
	$httpallowunauthfb = $attrs['apache-allow-unauthenticated-fb'][0];
	$amavis = $attrs['postfix-enable-virus-scan'][0];
	$quotawarn = $attrs['cyrus-quotawarn'][0];
	$freebusypast = $attrs['kolabFreeBusyPast'][0];
	$postfixmydomain = $attrs['postfix-mydomain'][0];
	unset( $attrs['postfix-mynetworks']['count'] );
	$postfixmynetworks = join(', ',$attrs['postfix-mynetworks']);
	$postfixallowunauth = $attrs['postfix-allow-unauthenticated'][0];
	$postfixrelayhost = $attrs['postfix-relayhost'][0];
	if( ereg( '\\[(.+)\\]', $postfixrelayhost, $regs ) ) {
	  $postfixrelayhost = $regs[1];
	  $postfixrelayhostmx = 'false';
	} else if( $postfixrelayhost != '' ) {
	  $postfixrelayhostmx = 'true';
	} else {
	  $postfixrelayhostmx = 'false';
	}
	$kolabhost = $attrs['kolabHost'];
	unset( $kolabhost['count'] );
	$kolabfilterverifyfrom = $attrs['kolabfilter-verify-from-header'][0];
	$kolabfilterallowsender = $attrs['kolabfilter-allow-sender-header'][0];
	$kolabfilterrejectforgedfrom = $attrs['kolabfilter-reject-forged-from-header'][0];
	ldap_free_result($result);
  }
}

function toboolstr( $b ) { return ( $b == 'TRUE' )?'true':'false'; }


extract_ldap_values();

// Write back to LDAP
if( $_REQUEST['submitsystemalias'] ) {
  $mail = trim($_REQUEST['systemaliasmail']);
  $dn = $ldap->dnForMailOrAlias( $mail );
  if( !$dn ) {
	$errors[] = _("No account found for email address $mail");
  } else {
	foreach( array( 'postmaster', 'hostmaster', 'abuse', 'virusalert', 'MAILER-DAEMON' ) as $group ) {
	  $attrs = array( 'objectClass' => array( 'top', 'kolabGroupOfNames' ),
					  'cn' => $group,
					  'mail' => $group.'@'.$postfixmydomain,
					  'member' => $dn );
	  if( !ldap_add( $ldap->connection, "cn=$group,".$_SESSION['base_dn'], $attrs ) ) {
		$errors[] = _("LDAP Error: Failed to add distribution list $group: ").$ldap->error();
	  } else {
		$messages[] = "Successfully created distribution list $group";
	  }
	}
  }
}

if( $_REQUEST['submitservices'] ) {
  $attrs = array();
  if( postvalue( 'pop3' ) != $pop3 )   $attrs['cyrus-pop3'] = postvalue( 'pop3' );
  if( postvalue( 'pop3s' ) != $pop3s ) $attrs['cyrus-pop3s'] = postvalue( 'pop3s' );
  if( postvalue( 'imap' ) != $imap )   $attrs['cyrus-imap'] = postvalue( 'imap' );
  if( postvalue( 'imaps' ) != $imaps ) $attrs['cyrus-imaps'] = postvalue( 'imaps' );
  if( postvalue( 'sieve' ) != $sieve ) $attrs['cyrus-sieve'] = postvalue( 'sieve' );
  if( postvalue( 'ftp' ) != $ftp )     $attrs['proftpd-ftp'] = postvalue( 'ftp' );
  if( postvalue( 'http' ) != $http )    $attrs['apache-http'] = postvalue( 'http' );
  if( postvalue( 'amavis' ) != $amavis )  $attrs['postfix-enable-virus-scan'] = postvalue( 'amavis' );
  
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submitquotawarn'] ) {
  $attrs = array();
  $attrs['cyrus-quotawarn'] = trim( $_REQUEST['quotawarn'] );
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submithttpallowunauthfb'] ) {
  $attrs = array();
  $attrs['apache-allow-unauthenticated-fb'] = postvalue( 'httpallowunauthfb' );
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submitfreebusypast'] ) {
  $attrs = array();
  $attrs['kolabFreeBusyPast'] = trim( $_REQUEST['freebusypast'] );
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submitpostfixmynetworks'] ) {
  $attrs = array();
  $attrs['postfix-mynetworks'] = preg_split( "/[\s,]+/", trim( $_REQUEST['postfixmynetworks'] ) );
  //if( $attrs['postfix-mynetworks'] == '' ) $attrs['postfix-mynetworks'] = array();
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submitpostfixallowunauth'] ) {
  $attrs = array();
  $attrs['postfix-allow-unauthenticated'] = postvalue( 'postfixallowunauth' );
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}

if( $_REQUEST['submitkolabfilter'] ) {
  $attrs = array(
				 'kolabfilter-verify-from-header'        => postvalue( 'kolabfilterverifyfrom' ),
				 'kolabfilter-allow-sender-header'       => postvalue( 'kolabfilterallowsender' ),
				 'kolabfilter-reject-forged-from-header' => $_REQUEST['kolabfilterrejectforgedfrom']=='TRUE'?'TRUE':'FALSE' );
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
 }

if( $_REQUEST['submitpostfixrelayhost'] ) {
  $value = trim( $_REQUEST['postfixrelayhost'] );
  if( $value != '' && !isset( $_REQUEST['postfixrelayhostmx'] ) ) {
	$value = "[$value]";
  }
  if( $value == '' ) $value = array();
  $attrs = array();  
  $attrs['postfix-relayhost'] = $value;
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
        $errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
          .ldap_error($ldap->connection);
  }
}


if( $_REQUEST['deletekolabhost'] ) {
  extract_ldap_values();
  $key = array_search($_REQUEST['akolabhost'],$kolabhost);
  if( $key !== false ) {
	unset( $kolabhost[ $key ] );
  }
  $attrs = array();
  $attrs['kolabhost'] = $kolabhost;
  if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	$errors[] = _("LDAP Error: failed to modify kolab configuration object: ")
	  .ldap_error($ldap->connection);
  }
}
if( $_REQUEST['addkolabhost'] ) {
  extract_ldap_values();
  if( trim($_REQUEST['akolabhost']) ) {
	$kolabhost[] = trim($_REQUEST['akolabhost']);
	$attrs = array();
	$attrs['kolabhost'] = $kolabhost;
	if( !($result = ldap_modify($ldap->connection, "k=kolab,".$_SESSION['base_dn'], $attrs)) ) {
	  $errors[] = _("LDAP Error: failed to modify kolab configuration object: ").ldap_error($ldap->connection);
	}
  }
}

// Fill in output form
extract_ldap_values();
$entries = array( array( 'service' => 'pop3', 'name'  => _('POP3 Service'), 'enabled' => toboolstr( $pop3 ) ),
				  array( 'service' => 'pop3s', 'name' => _('POP3/SSL service (TCP port 995)'), 'enabled' => toboolstr( $pop3s ) ),
				  array( 'service' => 'imap', 'name'  => _('IMAP Service'), 'enabled' => toboolstr( $imap ) ),
				  array( 'service' => 'imaps', 'name' => _('IMAP/SSL Service (TCP port 993)'), 'enabled' => toboolstr( $imaps ) ),
				  array( 'service' => 'sieve', 'name' => _('Sieve service (TCP port 2000)'), 'enabled' => toboolstr( $sieve ) ),
				  array( 'service' => 'ftp', 
						 'name'   => _('FTP FreeBusy Service (Legacy, not interoperable with Kolab2 FreeBusy)'), 
						 'enabled' => toboolstr( $ftp ) ),
				  array( 'service' => 'http', 'name'  => _('HTTP FreeBusy Service (Legacy)'), 'enabled' => toboolstr( $http ) ),
				  array( 'service' => 'amavis', 'name' => _('Amavis Email Scanning (Virus/Spam)'), 
						 'enabled' => toboolstr( $amavis ) ) );

/**** Check for system aliases ****/
function exists_group( $group ) {
  global $ldap;
  $filter = '(&(objectClass=kolabGroupOfNames)(cn='.$ldap->escape($group).'))';
  $res = $ldap->search( $_SESSION['base_dn'], $filter, array( 'dn' ) );
  return ( $ldap->count($res) > 0 );
}

/**** Insert into template and output ***/
$smarty = new MySmarty();
$smarty->assign( 'errors', $errors );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'entries', $entries );
$smarty->assign( 'quotawarn', $quotawarn );
$smarty->assign( 'httpallowunauthfb', toboolstr($httpallowunauthfb) );
$smarty->assign( 'freebusypast', $freebusypast );
$smarty->assign( 'postfixmynetworks', $postfixmynetworks );
$smarty->assign( 'postfixallowunauth', toboolstr($postfixallowunauth) );
$smarty->assign( 'postfixrelayhost', $postfixrelayhost );
$smarty->assign( 'postfixrelayhostmx', $postfixrelayhostmx );
$smarty->assign( 'kolabfilterverifyfrom', toboolstr($kolabfilterverifyfrom) );
$smarty->assign( 'kolabfilterallowsender', toboolstr($kolabfilterallowsender) );
$smarty->assign( 'kolabfilterrejectforgedfrom', toboolstr($kolabfilterrejectforgedfrom) );
$smarty->assign( 'kolabhost', $kolabhost );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', 
				 array_key_exists('submenu', 
								  $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'maincontent', 'service.tpl' );

$smarty->assign( 'systemaliasconf', false );
if( $auth->group() == 'admin' ) {
  if( !exists_group( 'hostmaster' ) ||
	  !exists_group( 'postmaster' ) ||
	  !exists_group( 'abuse' ) ||
	  !exists_group( 'virusalert' ) ||
	  !exists_group( 'MAILER-DAEMON' ) ) {
	// Ok, user did not set up system aliases
	$smarty->assign( 'systemaliasconf', true );
  }
}

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
