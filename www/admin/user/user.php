<?php
/*
 (c) 2004-2006 Klaraelvdalens Datakonsult AB
 (c) 2004 Martin Konold erfrakon <martin.konold@erfrakon.de>
 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once('skolab/admin/include/mysmarty.php');
require_once('skolab/admin/include/headers.php');
require_once('skolab/admin/include/locale.php');
require_once('skolab/admin/include/authenticate.php');
require_once('skolab/admin/include/form.class.php');
require_once('skolab/admin/include/passwd.php');

/**** Functions ***/
function comment( $s ) {
  return $s;
}

function is_unique ($a, $b) {
  global $ldap;
  if (($result = $ldap->search( $_SESSION['base_dn'],"(".$a."=".$ldap->escape($b).")")) &&
      (ldap_count_entries($ldap->connection,$result) <= 0))
    return true;
  return false;
}

function domain_dn()
{
  /*
  global $ldap;
  global $errors;
  if ($dattrs = $ldap->read( 'k=kolab,'.$_SESSION['base_dn'])) {
    $domain = $dattrs['postfix-mydomain'][0];
    $dcs = array_reverse(explode('.', $domain));
    $domain_dn = $_SESSION['base_dn'];
    foreach ($dcs as $dc) $domain_dn = "dc=$dc,".$domain_dn;

  } else {
    array_push($errors, "LDAP Error: could not determin domain");
    $domain_dn = $_SESSION['base_dn'];
  }
  return $domain_dn;
  */
  return $_SESSION['base_dn'];
}

// return tru if $str ends with $sub
function endsWith( $str, $sub ) {
  return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

// check if the given dn is maintainable by the current user
function inMaintainerDomain($dn) {

  global $ldap;
  global $auth;

  // both groups have full access
  if ($auth->group() == 'maintainer' || $auth->group() == 'admin') {
	return true;
  }

  // user may not maintain anything
  if ($auth->group() == 'user') {
	return false;
  }

  // we have a domain maintainer.

  // Before creating new users the DN is empty
  if (!$dn) {
	return true;
  }

  // Check if the user's mail is within the domain maintainer's domains
  $mail = $ldap->mailForDn($dn);
  $domains = $ldap->domainsForMaintainerDn($auth->dn());
  foreach( $domains as $domain ) {
        if( endsWith( $mail, '@'.$domain ) ) {
          return true;
        }
  }
  return false;
}

// Check that a uid is unique
function checkuniquemail( $form, $key, $value ) {
  debug("checkuniquemail( $form, $key, $value )");
  global $ldap;
  global $auth;
  $value = trim($value);
  if( $value == '' ) return _('Please enter an email address');

  // Check that we are in the domain
  $kolab = $ldap->read( 'k=kolab,'.$_SESSION['base_dn'] );
  if( $auth->group() == 'domain-maintainer' ) {
	$domains = $ldap->domainsForMaintainerDn($auth->dn());
  } else {
	$domains = $kolab['postfix-mydestination'];
	unset($domains['count']);
  }
  debug("value=$value, domain=$domains");
  $ok = false;
  foreach( $domains as $domain ) {
	if( endsWith( $value, '@'.$domain ) ) {
	  $ok = true;
	}
  }
  if(!$ok) return sprintf(_("Email address %1\$s not in domains %2\$s"), $value, join(", ", $domains));

  if( $ldap->countMail( $_SESSION['base_dn'], $value ) > 0 ) {
	return _('User, vCard or distribution list with this email address already exists');
  } else {
	return '';
  }
}

function checkuid( $form, $key, $value ) {
  global $ldap;
  global $action;
  global $dn;
  $excludedn = false;
  if( $action == 'save' ) $excludedn = trim($dn);
  $lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
  $str = '';
  foreach( $lst as $uid ) {
    if( $ldap->countMail( $_SESSION['base_dn'], $uid, $excludedn ) > 0 ) {
      $str .= _('UID ').MySmarty::htmlentities($uid)._(' collides with an address already used for another user, a vCard or a distribution list.<br />');
    }
  }
  return $str;
}

function checkdelegate( $form, $key, $value ) {
  global $ldap;
  global $action;
  global $dn;

  $lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
  $str = '';
  foreach( $lst as $delegate ) {
	if( $ldap->count( $ldap->search( $_SESSION['base_dn'], '(mail='.$ldap->escape($delegate).')' ) ) == 0 ) {
	  return sprintf(_("Email-Delegate %s does not exist"), $delegate);
	}
  }
  return '';
}

function checksmtprecipient ( $form, $key, $value ) {
  $lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
  $str = '';
  require_once 'Mail/RFC822.php';
  foreach( $lst as $SMTPRecipient ) {
    $trimmed = ltrim($SMTPRecipient, "-."); // potentially every entry is negated with a '-'
    // $SMTPRecipient is either an
    // - email address
    // - local part of an email address with an @ suffix
    // - a domain part
	if (valid_domain($SMTPRecipient)) {
	  return '';
	}
	if (valid_local_part($SMTPRecipient)) {
      return sprintf(_("Syntax for Recipient %s is invalid"), $SMTPRecipient);
    }
    $result = valid_email_address($SMTPRecipient);
	if (is_a($result, 'PEAR_Error')) {
	  return $result->getMessage();
	} else {
	  return '';
	}
  }
  return '';
}


function valid_email_address($address) {
// the following addresses are invalid
// email1..@kolab.org
// email1.-@kolab.org
// email1._@kolab.org
// email1@2sub.kolab.org
// email1@sub.sub.2sub.kolab.org
  $check = new Mail_RFC822($address);
  return $check->parseAddressList(null, null, null, true);
}

function valid_domain($domain) {
// the following subdomains are invalid
// 2sub.kolab.org
// sub.sub.2sub.kolab.org
  $check = new Mail_RFC822();
  return $check->_validateDomain($domain);
}

function valid_local_part($local_part) {
  // the local part always has an @ appended
  $local_part = rtrim($local_part, '@');
  $check = new Mail_RFC822();
  return $check->_validateLocalPart($local_part);
}

// Check uid/gid used in invitation policy
// We're pretty relaxed about what is entered
// here and only check some basic syntax
function checkpolicy( $form, $key, $value ) {
  foreach( $value as $v ) {
	$v = trim($v);
	if( !empty($v) && !ereg('^([0-9a-zA-Z._@ ]|-)*$', $v ) ) {
	  return sprintf(_("Illegal user or group %s"), $v);
	}
  }
  return '';
}

function checkfreebusyfuture( $form, $key, $value )
{
	if( empty($value) ) return ''; // OK
	else if( $value < 0 ) return _('Free/Busy interval can not be negative');
	else if( !is_numeric($value) ) return _('Free/Busy interval must be a number');
	else if( (int) $value != $value ) return _('Free/Busy interval must be an integer');
	else return '';
}

function policy2number( $pol, $default = 3 )
{
  // Translate policy to number
  switch ($pol) {
  case 'ACT_ALWAYS_ACCEPT': return 0;
  case 'ACT_ALWAYS_REJECT': return 1;
  case 'ACT_REJECT_IF_CONFLICTS': return 2;
  case 'ACT_MANUAL_IF_CONFLICTS': return 3;
  case 'ACT_MANUAL': return 4;
  default: return $default;
  }
}

function apply_attributeaccess( &$entries ) {
  global $params;
  $attributeaccess =& $params['attribute_access'];
  foreach( $entries as $key=>$value ) {
	if( ereg( '(.*)_[0-9]', $key, $regs ) ) {
	  $akey = $regs[1];
	} else {
	  $akey = $key;
	}
	if( isset($attributeaccess[$akey] ) ) {
	  if( $attributeaccess[$akey] == 'ro' ) {
		$entries[$key]['attrs'] = 'readonly';
	  } else if( $attributeaccess[$akey] == 'hidden' ) {
		  //$entries[$key]['attrs'] = 'hidden';
		unset($entries[$key]);
	  } else if( $attributeaccess[$akey] == 'mandatory' ) {
		if( isset( $entries[$key]['validation'] ) ) {
		  if( is_array( $entries[$key]['validation'] ) ) {
			$entries[$key]['validation'][] = 'notempty';
		  } else {
			$entries[$key]['validation'][] = array( $entries[$key]['validation'], 'notempty' );
		  }
		} else {
		  $entries[$key]['validation'] = 'notempty';
		}
	  }
	}
  }
}

function fill_form_for_modify( &$form, $dn, &$ldap_object ) {
  global $auth;
  if (is_array($ldap_object['sn'])) $sn = $ldap_object['sn'][0];
  else $sn = $ldap_object['sn'];
  if (is_array($ldap_object['cn'])) $cn = $ldap_object['cn'][0];
  else $cn = $ldap_object['cn'];
  if ($cn && $sn) $givenname = KolabLDAP::getGivenName($cn, $sn);
  if (is_array($ldap_object['initials'])) $initials = $ldap_object['initials'][0];
  else $initials = $ldap_object['initials'];
  if (is_array($ldap_object['mail'])) $mail = $ldap_object['mail'][0];
  else $mail = $ldap_object['mail'];
  if (is_array($ldap_object['uid'])) $uid = $ldap_object['uid'][0];
  else $uid = $ldap_object['uid'];
  if(array_key_exists('givenname',$form->entries)) $form->entries['givenname']['value'] = $givenname;
  if(array_key_exists('initials',$form->entries)) $form->entries['initials']['value'] = $initials;
  if(array_key_exists('sn',$form->entries)) $form->entries['sn']['value'] = $sn;
  if(array_key_exists('password_0',$form->entries))  $form->entries['password_0']['value'] = '';
  if(array_key_exists('password_1',$form->entries))  $form->entries['password_1']['value'] = '';
  if(array_key_exists('mail',$form->entries))  $form->entries['mail']['value'] = $mail;
  if(array_key_exists('mail',$form->entries))  $form->entries['mail']['attrs'] = 'readonly';
  if(array_key_exists('uid',$form->entries))  $form->entries['uid']['value'] = $uid;
  // accttype
  $dncomp = split( ',', $dn );
  if(array_key_exists('accttype',$form->entries)) {
	  if( in_array('cn=groups',$dncomp) ) {
		  $form->entries['accttype']['value'] = 2;
	  } else if( in_array('cn=resources',$dncomp) ) {
		  $form->entries['accttype']['value'] = 3;
	  } else if( in_array('cn=internal',$dncomp) ) {
		  $form->entries['accttype']['value'] = 1;
	  } else {
		  $form->entries['accttype']['value'] = 0;
	  }
	  if( $auth->group() == 'user' ) $form->entries['accttype']['attrs'] = 'readonly';
  }

  // Automatic invitation handling
  if(array_key_exists('kolabinvitationpolicy',$form->entries)) {
	  $policies = array();
	  for( $i = 0; $i < $ldap_object['kolabInvitationPolicy']['count']; $i++ ) {
		  $resact = $ldap_object['kolabInvitationPolicy'][$i];
		  debug("resact=$resact");
		  if( ereg( '(.*):(.*)', trim($resact), $regs ) ) {
			  $user = trim($regs[1]);
			  $pol  = trim($regs[2]);
			  if( empty($user) ) continue;
		  } else {
			  $user = 'anyone';
			  $pol = trim($resact);
		  }
		  if( $form->entries['accttype']['value'] == 1 ) {
			  // default for groups
			  $pol = policy2number( $pol, 3 /*ACT_MANUAL_IF_CONFLICTS*/ );
		  } else {
			  // default for resources
			  $pol = policy2number( $pol, 2 /*ACT_REJECT_IF_CONFLICTS*/ );
		  }
		  $policies[$user] = $pol;
	  }
	  if( !isset( $policies['anyone'] ) ) $policies['anyone'] = 4 /*ACT_MANUAL*/;
	  $form->entries['kolabinvitationpolicy']['policies'] = $policies;
  }

  foreach( array( 'title', 'o', 'ou', 'roomNumber', 'street',
				  'postOfficeBox', 'postalCode', 'l', 'c',
				  'telephoneNumber', 'facsimileTelephoneNumber' ) as $attr ) {
	if(!array_key_exists($attr.'_0',$form->entries)) continue;
    if (is_array($ldap_object[$attr])) $v = $ldap_object[$attr][0];
    else $v = $ldap_object[$attr];
    $form->entries[$attr.'_0']['value'] = $v;
  }

  // alias
  if(array_key_exists('alias',$form->entries)) {
	  if (is_array($ldap_object['alias'])) {
		  $arr = $ldap_object['alias'];
		  unset( $arr['count'] );
		  $v = join("\n", $arr );
	  }
	  else $v = "";
	  $form->entries['alias']['value'] = $v;
  }

  // kolabdelegate
  if (is_array($ldap_object['kolabDelegate'])) {
	$arr = $ldap_object['kolabDelegate'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = "";
  if(array_key_exists('kolabdelegate',$form->entries)) $form->entries['kolabdelegate']['value'] = $v;

  // kolabAllowSMTPRecipient
  if (is_array($ldap_object['kolabAllowSMTPRecipient'])) {
	$arr = $ldap_object['kolabAllowSMTPRecipient'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = "";
  if(array_key_exists('kolabAllowSMTPRecipient',$form->entries)) $form->entries['kolabAllowSMTPRecipient']['value'] = $v;

  // kolabhomeserver
  if(array_key_exists('kolabhomeserver',$form->entries)) {
	  if( is_array($ldap_object['kolabHomeServer']) ) {
		  $form->entries['kolabhomeserver']['value'] = $ldap_object['kolabHomeServer'][0];
	  }
	  $form->entries['kolabhomeserver']['attrs'] = 'readonly';
  }
  $form->entries['action']['value'] = 'save';

  // userquota
  if( isset( $form->entries['cyrus-userquota'] ) ) {
    if (is_array($ldap_object['cyrus-userquota'])) $userquota = $ldap_object['cyrus-userquota'][0];
    else $userquota = $ldap_object['cyrus-userquota'];
    if( $userquota > 0 ) {
      $form->entries['cyrus-userquota']['value'] = $userquota;
    } else {
      $form->entries['cyrus-userquota']['value'] = '';
    }
  }

  // freebusyfuture
  if( isset( $form->entries['kolabFreeBusyFuture_0'] ) ) {
	if( is_array( $ldap_object['kolabFreeBusyFuture'] ) )
	  $freebusyfuture = $ldap_object['kolabFreeBusyFuture'][0];
	else $freebusyfuture = $ldap_object['kolabFreeBusyFuture'];
	$form->entries['kolabFreeBusyFuture_0']['value'] = $freebusyfuture;
  }
}

/**** Authentication etc. ***/
$sidx = 'user';

require_once('skolab/admin/include/menu.php');
$menuitems[$sidx]['selected'] = 'selected';

/**** Logic ***/
$errors = array();
$messages = array();
$valid_actions = array('save','firstsave','modify','create','delete','kill');
$contenttemplate = 'formcontainer.tpl';

// Get request data
if (!empty($_REQUEST['action']) &&
    in_array($_REQUEST['action'],$valid_actions)) $action = trim($_REQUEST['action']);
else array_push($errors, _("Error: need valid action to proceed") );
$dn="";
if (!empty($_REQUEST['dn'])) $dn = trim($_REQUEST['dn']);

if( $auth->group() == 'user' ) {
	$dn = $auth->dn();
}

// Check auth
if (!$errors && $auth->group() != 'maintainer' && $auth->group() != 'admin' &&
	$auth->group() != 'domain-maintainer' &&
    !($auth->group() == 'user' && $dn == $auth->dn() )) {
  array_push($errors, _("Error: You don't have the required Permissions") );
} else if( $auth->group() == 'domain-maintainer' ) {
  if (!inMaintainerDomain($dn)) {
    array_push($errors, _("Error: You don't have the required Permissions") );
  }
}


if( !$errors && $auth->group() == 'user' && ($action == 'firstsave' || $action == 'kill' ) ) {
  $errors[] = _("Error: You don't have the required Permissions");
}

// Fill in data
if ($action == "create") {
  $comment_mail = _('Required, non volatile');
  $comment_password = _('Required');
  $comment_kolabhomeserver = _('Required, non volatile');
} else {
  $comment_mail = _('Non volatile');
  $comment_password = _('Leave blank to keep password unchanged');
  $comment_kolabhomeserver = _('Non volatile');
}

$entries = array( 'givenname' => array( 'name' => _('First Name'),
					'validation' => 'notempty',
					'comment' => _('Required') ),
		  'initials' => array( 'name' => _('Middle Name')),
		  'sn' => array( 'name' => _('Last Name'),
				       'validation' => 'notempty',
				       'comment' => _('Required') ),
		  'password_0' => array( 'name' => _('Password'),
					 'type' => 'password',
					 'validation' => 'checkpw',
					 'comment' => $comment_password ),
		  'password_1' => array( 'name' => _('Verify Password'),
					 'type' => 'password',
					 'validation' => 'checkpw',
					 'comment' => $comment_password ),
		  'mail' => array( 'name' => _('Primary Email Address'),
					 'type'       => 'email',
					 'domains'    => ($auth->group()=='domain-maintainer')?$ldap->domainsForMaintainerDn($auth->dn()):$ldap->domains(),
				     'validation' => 'notempty',
				     'comment'    => $comment_mail ),
		  'uid'    => array( 'name' => _('Unique Identity (UID)'),
                                     'validation' => 'checkuid',
                                     'comment' => _('Optional - Defaults to Primary Email Address') ),
		  'kolabhomeserver' => array( 'name' => _('Mailbox Home Server'),
									  'validation' => 'notempty',
									  'comment' => $comment_kolabhomeserver,
									  'value' => $_SESSION['fqdnhostname'] ),
		  'accttype' => array( 'name' => _('Account Type'),
							   'type' => 'select',
							   'options' => array( _('User Account'), _('Internal User Account'), _('Group Account'), _('Resource Account') ),
							   'value'   => 0,
							   'comment' => _('NOTE: An internal user is a user that will not be visible in the address book')),
		  'kolabinvitationpolicy' => array( 'name' => _('Invitation Policy'),
									 'type' => 'resourcepolicy',
									 'policies' => array('anyone' => 4),
									 'validation' => 'checkpolicy',
									 'comment' => _('For automatic invitation handling') . '<br/>' .
									 _("NOTE: For regular accounts to use this feature, give the 'calendar' user access to the Calendar folder") ));

$entries['alias'] = array( 'name' => _('Email Aliases'),
						   'type' => 'textarea',
						   'validation' => 'checkuniquealias',
						   'comment' => _('One address per line') );

$entries['kolabdelegate'] =array( 'name' => _('Email-Delegates'),
		'type' => 'textarea',
		'validation' => 'checkdelegate',
		'comment' => _('Others allowed to send emails with a "from" address of this account.') . '<br/>' .
			_('One email address per line.') );

$entries['kolabAllowSMTPRecipient'] =array( 'name' => _('Allowed Recipients'),
                'type' => 'textarea',
                'validation' => 'checksmtprecipient',
                'comment' => _('Restrict allowed recipients of SMTP messages') . '<br/>' .
                        _('One entry per line.') );

$entries['title_0'] = array( 'name' => _('Title') );
$entries['o_0'] = array( 'name' => _('Organisation') );
$entries['ou_0'] = array( 'name' => _('Organisational Unit') );
$entries['roomNumber_0'] = array( 'name' => _('Room Number') );
$entries['street_0'] = array( 'name' => _('Street Address') );
$entries['postOfficeBox_0'] = array( 'name' => _('Postbox') );
$entries['postalCode_0'] = array( 'name' => _('Postal Code') );
$entries['l_0'] = array( 'name' => _('City') );
$entries['c_0'] = array( 'name' => _('Country'),
		'comment' => _('2 letter code from <a href="http://www.iso.org/iso/english_country_names_and_code_elements" target="_blank">ISO 3166</a>') );
$entries['telephoneNumber_0'] = array( 'name' => _('Telephone Number'),
						'validation' => 'checkphone' );
$entries['facsimileTelephoneNumber_0'] = array( 'name' => _('Fax Number'),
						'validation' => 'checkphone' );
if( $auth->group() == 'admin' || $auth->group() == 'maintainer' || $auth->group() == 'domain-maintainer' ) {
  $entries['cyrus-userquota'] = array( 'name' => _('User Quota in MBytes'),
				       'comment' => _('Leave blank for unlimited'),
				       'validation' => 'checkquota');
} else {
  $entries['givenname']['attrs'] = 'readonly';
  $entries['givenname']['comment'] = '';
  $entries['initials']['attrs'] = 'readonly';
  $entries['sn']['attrs'] = 'readonly';
  $entries['sn']['comment'] = '';
  $entries['alias']['attrs'] = 'readonly';
  $entries['kolabdelegate']['attrs'] = 'readonly';
  $entries['kolabhomeserver']['attrs'] = 'readonly';
  $entries['kolabAllowSMTPRecipient']['attrs'] = 'readonly';
  $entries['accttype']['attrs'] = 'readonly';
  $entries['uid']['attrs'] = 'readonly';
}
$entries['kolabFreeBusyFuture_0'] = array( 'name' => _('Free/Busy interval in days'),
					   'comment' => _('Leave blank for default (60 days)'),
					   'validation' => 'checkfreebusyfuture' );
$entries['action'] = array( 'name' => 'action',
			    'type' => 'hidden' );

$oc = array('top', 'inetOrgPerson','kolabInetOrgPerson');

if( $dn ) {
  $ldap_object = $ldap->read( $dn );
  if( !$ldap_object ) {
    array_push($errors, sprintf(_("LDAP Error: No such dn: %s: %s"), $dn, ldap_error($ldap->connection)));
  }
  $oc = $ldap_object['objectClass'];
  unset($oc['count']);
}

if( $auth->group() == 'user' ) {
  apply_attributeaccess( $entries );
}
$form = new KolabForm( 'user', 'createuser.tpl', $entries );
/***************** Main action switch **********************/
switch( $action ) {
 case 'firstsave':
   debug("adding checkuniquemail to validation");
   $ldap_object['objectClass'] = array('top', 'inetOrgPerson','kolabInetOrgPerson');
   $form->entries['mail']['validation'] = array( $form->entries['mail']['validation'], 'checkuniquemail');
 case 'save':
   if( $form->isSubmitted() ) {
     if( !$form->validate() ) {
	   if($action != "firstsave")
		fill_form_for_modify($form, $ldap_object);
       $form->setValues();
       $content = $form->outputForm();
     } else {
       $ldap_object = array();
       $ldap_object['objectClass'] = $oc;
       $ldap_object['sn'] = trim($_POST['sn']);
       $ldap_object['cn'] = trim($_POST['givenname']).' '.$ldap_object['sn'];
	   $ldap_object['givenName'] = trim($_POST['givenname']);
	   if (!empty($_POST['initials'])) {
		 $ldap_object['initials'] = trim($_POST['initials']);
	   } else {
		 $ldap_object['initials'] = array();
	   }
       if( !empty( $_POST['password_0'] ) ) {
	         $ldap_object['userPassword'] = ssha( $_POST['password_0'], gensalt());
		 if( $action == 'save' && $auth->dn() == $dn ) {
		   // We are editing our own password, let's update the session!
		   $auth->setPassword($_POST['password_0']);
		 }
       }
       $ldap_object['mail'] = trim( strtolower( $_POST['user_mail'] ) ).'@'.trim( strtolower( $_POST['domain_mail'] ) );
       $ldap_object['uid'] = trim( strtolower( $_POST['uid'] ) );
       if( $action == 'firstsave' ) {
		 if ($ldap_object['uid'] == "") $ldap_object['uid'] = $ldap_object['mail'];
		 $ldap_object['kolabHomeServer'] = trim($_POST['kolabhomeserver']);
	   } else {
		 unset($ldap_object['kolabHomeServer']);
	   }
       foreach( array( 'title', 'o', 'ou', 'roomNumber', 'street', 'postOfficeBox',
		       'postalCode', 'l', 'c', 'telephoneNumber',
		       'facsimileTelephoneNumber', 'kolabFreeBusyFuture' ) as $attr ) {
		 $count = 0;
		 $key = $attr."_0";
		 $args = array();
		 while (!empty($_POST[$key])) {
		   $args[$count] = trim($_POST[$key]);
		   $count++;
		   $key = $attr."_".$count;
		 }
		 if ($count > 0) $ldap_object[$attr] = $args;
		 elseif (!empty($_POST[$key])) $ldap_object[$attr] = $_POST[$key];
		 else/*if (in_array($key,$_POST))*/ $ldap_object[$attr] = array();
       }
	   {
		 // Handle group/resource policies
		 $i = 0;
		 $ldap_object['kolabInvitationPolicy'] = array();
		 while( isset( $_POST['user_kolabinvitationpolicy_'.$i] ) ) {
		   $user = $_POST['user_kolabinvitationpolicy_'.$i];
		   $pol  = (int)$_POST['policy_kolabinvitationpolicy_'.$i];
		   debug("Looking at $user:$pol");
		   $i++;
		   if( !empty($user) && 0 <= $pol && $pol < 5  ) {
			 $ra = array('ACT_ALWAYS_ACCEPT',
						 'ACT_ALWAYS_REJECT',
						 'ACT_REJECT_IF_CONFLICTS',
						 'ACT_MANUAL_IF_CONFLICTS',
						 'ACT_MANUAL' );
			 if( $ra[$pol] ) {
			   $ldap_object['kolabInvitationPolicy'][] = ($user=='anyone'?"":"$user:").$ra[$pol];
			 }
		   }
		 }
	   }
	   $dn_add = "";

	   // kolabdelegate
	   $ldap_object['kolabDelegate'] = array_unique( array_filter( array_map( 'trim',
												preg_split( '/\n/', $_POST['kolabdelegate'] ) ), 'strlen') );
	   if( !$ldap_object['kolabDelegate'] && $action == 'firstsave' ) unset($ldap_object['kolabDelegate']);

	   // kolabAllowSMTPRecipient
	   $ldap_object['kolabAllowSMTPRecipient'] = array_unique( array_filter( array_map( 'trim',
												preg_split( '/\n/', $_POST['kolabAllowSMTPRecipient'] ) ), 'strlen') );
	   if( !$ldap_object['kolabAllowSMTPRecipient'] && $action == 'firstsave' ) unset($ldap_object['kolabAllowSMTPRecipient']);


       if ($auth->group() == "maintainer" || $auth->group() == "admin") {
		 // alias
		 $ldap_object['alias'] = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $_POST['alias'] ) ), 'strlen') );
		 if( !$ldap_object['alias'] && $action == 'firstsave' ) unset($ldap_object['alias']);

		 // userquota
		 if( isset( $_POST['cyrus-userquota'] ) ) {
		   $ldap_object['cyrus-userquota'] = trim($_POST['cyrus-userquota']);
		   if( empty( $ldap_object['cyrus-userquota'] ) ) {
			 $ldap_object['cyrus-userquota'] = array();
		   }
		 }
       }
	   if( $_POST['accttype'] == 0 ) $dn_accttype='';
	   else if( $_POST['accttype'] == 1 ) $dn_accttype='cn=internal,';
	   else if( $_POST['accttype'] == 2 ) $dn_accttype='cn=groups,';
	   else if( $_POST['accttype'] == 3 ) $dn_accttype='cn=resources,';
       $domain_dn = $dn_accttype.domain_dn();

       if ($action == "save") {
		 if (!$errors) {
           // We need the unmodified uid rdn for renaming
           $new_uid = "uid=" . $ldap->dn_escape($ldap_object['uid']);

		   if (!empty($ldap_object['uid'])) $newdn = "uid=".$ldap->dn_escape($ldap_object['uid']).",".$domain_dn;
		   else $newdn = $dn;
		   if (strcmp($dn,$newdn) != 0) {
			 // Check for distribution lists with this user as member
			 $ldap->search( $_SESSION['base_dn'],
							'(&(objectClass=kolabGroupOfNames)(!(kolabDeleteFlag=*))(member='.$ldap->escape($dn).'))',
							array( 'dn', 'mail' ) );
			 $distlists = $ldap->getEntries();

			 if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				 ($entry=ldap_first_entry($ldap->connection,$result)) &&
				 ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {

               // This is no longer necessary.
			   //$ldap_object['uid'] = $oldattrs['uid'][0];

			   $ldap_object['mail'] = $oldattrs['mail'][0];
			   unset( $oldattrs['count'] );
			   foreach( $oldattrs as $k => $v ) {
				 if( is_int($k) ) continue;
				 if( !$ldap_object[$k] ) {
				   unset($v['count'] );
				   if( count($v) > 1 ) {
					 $ldap_object[$k] = $v;
				   } else {
					 $ldap_object[$k] = $v[0];
				   }
				 }
			   }
			   if( !$ldap_object['userPassword'] ) $ldap_object['userPassword'] = $oldattrs['userPassword'][0];
			   foreach( $ldap_object as $k => $v ) {
				 if( $v == array() ) unset($ldap_object[$k]);
			   }
			   $tmprdn = "cn=".str_rand(16);
			   $explodeddn = ldap_explode_dn( $dn, 0 );
			   unset($explodeddn['count']);
			   unset($explodeddn[0]);
			   $tmpbasedn = join(",",$explodeddn);

			   if ( !$errors ) {
				 // Try to rename the object
				 if (!ldap_rename($ldap->connection, $dn, $new_uid, $domain_dn, true)) {
				   array_push($errors, sprintf(_("LDAP Error: could not rename %s to %s: %s"), $dn,
											   $newdn, ldap_error($ldap->connection)));
				 }
				 if( !$errors ) {
				   // Renaming was ok, now try to modify the object accordingly
				   if (!ldap_modify($ldap->connection, $newdn, $ldap_object)) {
					 // While this should not happen, in case it does, we need to revert the
					 // renaming
					 array_push($errors, sprintf(_("LDAP Error: could not modify %s: %s"), $newdn,
												 ldap_error($ldap->connection)));
					 $old_dn = substr($dn, 0, strlen($dn) - strlen($domain_dn) - 1);
					 ldap_rename($ldap->connection, $newdn, $old_dn, $domain_dn, true);
				   } else {
					 // everything is fine and we can move on
					 $messages[] = sprintf( _("%s successfully updated"), $newdn);
					 $dn = $newdn;
				   }
				 }
				 $dn = $newdn;
			   }
			 } else array_push($errors, sprintf(_("LDAP Error: Could not read %s: %s"), $dn,
												ldap_error($ldap->connection)));
		   } else {
			 //$ldap_object = fill_up($ldap_object);
			 if ($auth->group() == "user") {
			   unset($ldap_object['sn']);
			   unset($ldap_object['cn']);
			   unset($ldap_object['mail']);
			   unset($ldap_object['uid']);
			   unset($ldap_object['kolabHomeServer']);
			   unset($ldap_object['kolabAllowSMTPRecipient']);
			   unset($ldap_object['kolabDelegate']);
			 }
			 if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
			   array_push($errors, sprintf(_("LDAP Error: Could not modify object %s: %s"), $dn,
										   ldap_error($ldap->connection)));
			   debug_var_dump( $ldap_object );
			 }
		   }
		   // Check for collisions on alias
		   for( $i = 0; $i < count($ldap_object['alias']); ++$i ) {
			 if( $ldap->countMail( $_SESSION['base_dn'], $alias, $dn ) > 0 ) {
			   // Ups!!!
			   $alias = $ldap_object['alias'][$i];
			   $newalias = md5( $dn.$alias ).'@'.substr( $alias, 0, strpos( $alias, '@' ) );
			   $ldap_object['alias'][$i] = $newalias;
			   if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
				 $errors[] = sprintf(_("LDAP Error: Could not modify object %s: %s"), $dn,
									 ldap_error($ldap->connection));
			   }
			   $error[] = sprintf(_("Mid-air collision detected, alias %1\$s renamed to %2\$s"),
								  $alias, $newalias);
			 }
		   }
		 }
		 $heading = _('Modify User');
		 if( !$errors ) $messages[] = sprintf(_("User '%s' successfully modified"), $dn);
		 $form->setValues();
		 if(array_key_exists('password_0',$form->entries))  $form->entries['password_0']['value'] = '';
		 if(array_key_exists('password_1',$form->entries))  $form->entries['password_1']['value'] = '';
		 $form->entries['mail']['attrs'] = 'readonly';
		 $form->entries['kolabhomeserver']['attrs'] = 'readonly';
		 $form->entries['action']['value'] = 'save';
		 $form->entries['dn'] = array( 'name' => 'dn',
									   'type' => 'hidden',
									   'value' => $dn );
		 $content = $form->outputForm();
       } else {
		 // firstsave
		 if (!$errors) {
		   $dn = "uid=".$ldap->dn_escape($ldap_object['uid']).$dn_add.",".$domain_dn;
		   foreach( $ldap_object as $k => $v ) {
			 if( $v == array() ) unset($ldap_object[$k]);
		   }
		   debug("Calling ldap_add with dn=$dn");
		   if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object))
			 array_push($errors, sprintf(_("LDAP Error: could not add object %s: %s"), $dn,
										 ldap_error($ldap->connection)));

		   // Check for mid-air collisions on mail
		   if( $ldap->countMail( $_SESSION['base_dn'], $ldap_object['mail'], $dn ) > 0 ) {
			 // Ups!!!
			 $mail = $ldap_object['mail'];
			 $newmail = md5( $dn.$mail ).'@'.substr( $mail, 0, strpos( $mail, '@' ) );
			 $ldap_object['uid'] = $ldap_object['mail'] = $newmail;
			 if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
			   $errors[] = sprintf(_("LDAP Error: Could not modify object %s: %s"), $dn,
								   ldap_error($ldap->connection));
			 }
			 $error[] = sprintf(_("Mid-air collision detected, email address %1\$s renamed to %2\$s"),
								$mail, $newmail);
		   }

		   // Check for collisions on alias
		   for( $i = 0; $i < count($ldap_object['alias']); ++$i ) {
			 if( $ldap->countMail( $_SESSION['base_dn'], $alias, $dn ) > 0 ) {
			   // Ups!!!
			   $alias = $ldap_object['alias'][$i];
			   $newalias = md5( $dn.$alias ).'@'.substr( $alias, 0, strpos( $alias, '@' ) );
			   $ldap_object['alias'][$i] = $newalias;
			   if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
				 $errors[] = sprintf(_("LDAP Error: Could not modify object %s: %s"), $dn,
									 ldap_error($ldap->connection));
			   }
			   $error[] = sprintf(_("Mid-air collision detected, alias %1\$s renamed to %2\$s"),
								  $alias, $newalias);
			 }
		   }

		   if( !$errors ) {
			 $messages[] = _('User ').$ldap_object['dn']._(' successfully created');
			 $heading = _('Create New User');
			 $form->entries['action']['value'] = 'firstsave';
			 $content = $form->outputForm();
			 break;
		   }
		 } else {
		   $heading = _('Create New User');
		   $blacklist = array('mail');
		   $form->entries['action']['value'] = 'firstsave';
		   $content = $form->outputForm();
		   break;
		 }
       }
     }
     break;
   }
 case 'create':
   $heading = _('Create New User');
   if( !$dn ) {
     $form->entries['action']['value'] = 'firstsave';
   } else {
     $form->entries['action']['value'] = 'save';
   }
   $content = $form->outputForm();
   break;
 case 'modify':
   $heading = _('Modify User');
   fill_form_for_modify( $form, $dn, $ldap_object );
   $form->entries['action']['value'] = 'save';
   $content = $form->outputForm();
   break;
 case 'delete':
   $heading = _('Delete User');
   foreach( $form->entries as $k => $v ) {
     if( $v['type'] != 'hidden' ) {
       $form->entries[$k]['attrs'] = 'readonly';
     }
   }
   fill_form_for_modify( $form, $dn, $ldap_object );
   $form->entries['action']['value'] = 'kill';
   $form->submittext = _('Delete');
   $content = $form->outputForm();
   break;
 case 'kill':
   if (!$dn) {
	 array_push($errors, _("Error: need DN for delete operation"));
   } elseif ( $auth->group() == 'domain-maintainer' ) {
	 if (!inMaintainerDomain($dn)) {
	   array_push($errors, _("Error: You don't have the required Permissions") );
	 }
   } elseif ($auth->group() != "maintainer" && $auth->group() != "admin") {
	 array_push($errors, _("Error: you need administrative permissions to delete users"));
   }

   // Check for distribution lists with only this user as member
   $ldap->search( $_SESSION['base_dn'],
				  '(&(objectClass=kolabGroupOfNames)(member='.$ldap->escape($dn).'))',
				  array( 'dn', 'cn', 'mail', 'member' ) );
   $distlists = $ldap->getEntries();
   unset($distlists['count']);
   foreach( $distlists as $distlist ) {
	 $dlmail = $distlist['mail'][0];
	 if( !$dlmail ) $dlmail = $distlist['cn'][0]; # Compatibility with old stuff
	 if( $distlist['member']['count'] == 1 ) {
	   $errors[] = sprintf(_("Account could not be deleted, distribution list '%s' depends on it."), $dlmail);
	 }
   }
   if( !$errors ) foreach( $distlists as $distlist ) {
	 $dlmail = $distlist['mail'][0];
	 if( !$dlmail ) $dlmail = $distlist['cn'][0]; # Compatibility with old stuff
	 if( ldap_mod_del( $ldap->connection, $distlist['dn'], array('member' => $dn ) ) ) {
	   $messages[] = sprintf(_("Account removed from distribution list '%s'."), $dlmail);
	 } else {
	   $errors[] = sprintf(_("Failure to remove account from distribution list '%s', account will not be deleted."),
						   $dlmail);
	   break;
	 }
   }

   if( !$errors ) {
	 if (!$ldap->deleteObject($dn)) {
	   array_push($errors, sprintf(_("LDAP Error: could not mark '%s' for deletion: %s"), $dn,
								   $ldap->error()));
	 } else {
	   $heading = _("User Deleted");
	   $contenttemplate = 'userdeleted.tpl';
	 }
   }

   if( $errors ) {
	 $heading = _('Delete User');
	 foreach( $form->entries as $k => $v ) {
	   if( $v['type'] != 'hidden' ) {
		 $form->entries[$k]['attrs'] = 'readonly';
	   }
	 }
	 fill_form_for_modify( $form, $dn, $ldap_object );
	 $form->entries['action']['value'] = 'kill';
	 $form->submittext = _('Delete');
	 $content = $form->outputForm();
   }
   break;
}


$smarty = new MySmarty();
$smarty->assign( 'topdir', $topdir );
$smarty->assign( 'errors', array_merge((array)$errors,(array)$form->errors) );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems',
				 array_key_exists('submenu',
								  $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'heading', $heading );
$smarty->assign( 'form', $content );
if( isset( $dn ) ) $smarty->assign( 'dn', $dn );
if( count($messages)>0) $smarty->assign( 'messages', $messages );
$smarty->assign( 'maincontent', $contenttemplate );
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
