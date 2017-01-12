<?php
/*
 (c) 2004 Klarlvdalens Datakonsult AB
 (c) 2004 Martin Konold erfrakon <martin.konold@erfrakon.de>
 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');
require_once('admin/include/form.class.php');

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

// Check that a uid is unique
function checkuniquemail( $form, $key, $value ) {
  debug("checkuniquemail( $form, $key, $value )");
  global $ldap;
  $value = trim($value);
  if( $value == '' ) return _('Please enter an email address');

  // Check that we are in the domain
  $kolab = $ldap->read( 'k=kolab,'.$_SESSION['base_dn'] );
  $domain = trim($kolab['postfix-mydomain'][0]);
  debug("value=$value, domain=$domain");
  if( !endsWith( $value, '@'.$domain ) ) {	
	return _("Email address $value not in domain $domain");
  }

  if( $ldap->countMail( $_SESSION['base_dn'], $value ) > 0 ) {	
	return _('User, vCard or distribution list with this email address already exists');
  } else {
	return '';
  }
}

function checkuniquealias( $form, $key, $value ) {
  global $ldap;
  global $action;
  global $dn;
  $excludedn = false;
  if( $action == 'save' ) $excludedn = trim($dn);
  $lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
  $str = '';
  foreach( $lst as $alias ) {
	debug( "looking at $alias, exluding $dn" );
	if( $ldap->countMail( $_SESSION['base_dn'], $alias, $excludedn ) > 0 ) {
	  $str .= _('Email address ').MySmarty::htmlentities($alias)._(' collision <br />');
	}
  }
  return $str;
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
      $str .= _('UID ').MySmarty::htmlentities($uid)._(' collision <br />');
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
	  return _("Delegate $delegate does not exist");
	} 
  }
  return '';
}

// Check uid/gid used in invitation policy
// We're pretty relaxed about what is entered 
// here and only check some basic syntax
function checkpolicy( $form, $key, $value ) {
  foreach( $value as $v ) {
	$v = trim($v);
	if( !empty($v) && !ereg('^([0-9a-zA-Z._@ ]|-)*$', $v ) ) {
	  return _("Illegal user or group $v");
	}
  }
  return '';
}

// Check that password match
function checkpw( $form, $key, $value ) {
  global $action;
  if( $action == 'firstsave' ) {
    if( $key == 'password_0' ) {
      if( $value == '' ) return _('Password is empty');
    } else if( $key == 'password_1' ) {
      if( $value != $_POST['password_0'] ) {
        return _('Passwords dont match');
      }
    }
  } else {
    if( $value != $_POST['password_0'] ) {
      return _('Passwords dont match');
    }
  }
  return '';
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
  if ($sn) {
    $a = strlen($sn);
    if ($cn) {
      $b = strlen($cn);
      $givenname = substr($cn, 0, $b - $a);
    }
  }
  if (is_array($ldap_object['mail'])) $mail = $ldap_object['mail'][0];
  else $mail = $ldap_object['mail'];
  if (is_array($ldap_object['uid'])) $uid = $ldap_object['uid'][0];
  else $uid = $ldap_object['uid'];
  $form->entries['givenname']['value'] = $givenname;
  $form->entries['sn']['value'] = $sn;
  $form->entries['password_0']['value'] = '';
  $form->entries['password_1']['value'] = '';
  $form->entries['mail']['value'] = $mail;
  $form->entries['mail']['attrs'] = 'readonly';
  $form->entries['uid']['value'] = $uid;

  // accttype
  $dncomp = split( ',', $dn );
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

  // Automatic invitation handling
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

  foreach( array( 'title', 'o', 'ou', 'roomNumber', 'street', 
				  'postOfficeBox', 'postalCode', 'l', 'c', 
				  'telephoneNumber', 'facsimileTelephoneNumber' ) as $attr ) {
    if (is_array($ldap_object[$attr])) $v = $ldap_object[$attr][0];
    else $v = $ldap_object[$attr];
    $form->entries[$attr.'_0']['value'] = $v;
  }

  // alias
  if (is_array($ldap_object['alias'])) {
	$arr = $ldap_object['alias'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = "";
  $form->entries['alias']['value'] = $v;

  // kolabdelegate
  if (is_array($ldap_object['kolabDelegate'])) {
	$arr = $ldap_object['kolabDelegate'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = "";
  $form->entries['kolabdelegate']['value'] = $v;

  // kolabhomeserver
  if( is_array($ldap_object['kolabHomeServer']) ) {
	$form->entries['kolabhomeserver']['value'] = $ldap_object['kolabHomeServer'][0];
  }
  $form->entries['kolabhomeserver']['attrs'] = 'readonly';

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
  }
  $form->entries['kolabFreeBusyFuture_0']['value'] = $freebusyfuture;
}

/**** Authentication etc. ***/
$sidx = 'user';

require_once('admin/include/menu.php');
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
    !($auth->group() == 'user' && $dn == $auth->dn() )) {
  array_push($errors, _("Error: You don't have the required Permissions") );
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
				     'validation' => 'notempty',
				     'comment' => $comment_mail ),
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
									 _('NOTE: For regular accounts to use this feature, give the \'calendar\' user access to the Calendar folder') ),
		  'title_0' => array( 'name' => _('Title') ) );
$entries['alias'] = array( 'name' => _('Email Aliases'), 
						   'type' => 'textarea',
						   'validation' => 'checkuniquealias',
						   'comment' => _('One address per line') );
$entries['kolabdelegate'] =array( 'name' => _('Delegates'),
							 'type' => 'textarea',
							 'validation' => 'checkdelegate',
							 'comment' => _('One Email address per line') );
$entries['o_0'] = array( 'name' => _('Organisation') );
$entries['ou_0'] = array( 'name' => _('Organisational Unit') );
$entries['roomNumber_0'] = array( 'name' => _('Room Number') );
$entries['street_0'] = array( 'name' => _('Street Address') );
$entries['postOfficeBox_0'] = array( 'name' => _('Postbox') );
$entries['postalCode_0'] = array( 'name' => _('Postal Code') );
$entries['l_0'] = array( 'name' => _('City') );
$entries['c_0'] = array( 'name' => _('Country') );
$entries['telephoneNumber_0'] = array( 'name' => _('Telephone Number') );
$entries['facsimileTelephoneNumber_0'] = array( 'name' => _('Fax Number') );
if( $auth->group() == "admin" || $auth->group() == "maintainer" ) {
  $entries['cyrus-userquota'] = array( 'name' => _('User Quota in MBytes'),
				 'comment' => _('Leave blank for unlimited') );
} else {
  $entries['givenname']['attrs'] = 'readonly';
  $entries['sn']['attrs'] = 'readonly';
  $entries['givenname']['comment'] = '';
  $entries['sn']['comment'] = '';
  $entries['alias']['attrs'] = 'readonly';
  $entries['kolabhomeserver']['attrs'] = 'readonly';
  $entries['accttype']['attrs'] = 'readonly';
  $entries['uid']['attrs'] = 'readonly';
}
$entries['kolabFreeBusyFuture_0'] = array( 'name' => _('Free/Busy interval in days'),
									 'comment' => _('Leave blank for default (60 days)') );
$entries['action'] = array( 'name' => 'action',
			    'type' => 'hidden' );

if( $dn ) {
  $ldap_object = $ldap->read( $dn );
  if( !$ldap_object ) {
    array_push($errors, _("LDAP Error: No such dn: $dn: ").ldap_error($ldap->connection));
  }
}

if( $auth->group() == 'user' ) {
  apply_attributeaccess( $entries );
}
$form =& new KolabForm( 'user', 'createuser.tpl', $entries );
/***************** Main action swicth **********************/
switch( $action ) {
 case 'firstsave':
   debug("adding checkuniquemail to validation");
   $form->entries['mail']['validation'] = 'checkuniquemail';   
 case 'save':
   if( $form->isSubmitted() ) {
     if( !$form->validate() ) {
       $form->setValues();
       $content = $form->outputForm();
     } else {
       $ldap_object = array();
       $ldap_object['objectClass'] = array('top', 'inetOrgPerson','kolabInetOrgPerson');
       $ldap_object['sn'] = trim($_POST['sn']);
       $ldap_object['cn'] = trim($_POST['givenname']).' '.$ldap_object['sn'];
	   $ldap_object['givenName'] = trim($_POST['givenname']);
       if( !empty( $_POST['password_0'] ) ) {
		 $ldap_object['userPassword'] = '{sha}'.base64_encode( pack('H*', 
																	sha1( $_POST['password_0'])));
		 if( $action == 'save' && $auth->dn() == $dn ) {
		   // We are editing our own password, let's update the session!
		   $auth->setPassword($_POST['password_0']);
		 }
		 if( isset( $_POST['accttype'] ) && $_POST['accttype'] > 1 ) {
		   // We have a group or resource, create encrypted pw
		   $pubkeydata=file_get_contents("$kolab_prefix/etc/kolab/res_pub.pem" );		   
		   $pkey = openssl_pkey_get_public( $pubkeydata );
		   if( $pkey === false ) {
			 $sslerr = _("Could not read resource encryption public key file://$kolab_prefix/etc/kolab/res_pub.pem: ");
			 while( $msg = openssl_error_string() )
			   $sslerr .= $msg.' ';
			 $errors[] = $sslerr;
		   } else {
			 if( !openssl_public_encrypt( $_POST['password_0'], $encpw, $pkey ) ) {
			   $sslerr = _("Could not encrypt password: ");
			   while( $msg = openssl_error_string() )
				 $sslerr .= $msg.' ';
			   $errors[] = $sslerr;
			 } else {
			   $ldap_object['kolabEncryptedPassword'] = base64_encode( $encpw );
			 }
			 openssl_free_key( $pkey );
		   }
		 }
       }
       $ldap_object['mail'] = trim( strtolower( $_POST['mail'] ) );
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
		 else $ldap_object[$attr] = array();
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
		   if (!empty($ldap_object['cn'])) $newdn = "cn=".$ldap->dn_escape($ldap_object['cn']).",".$domain_dn;
		   else $newdn = $dn;
		   if (strcmp($dn,$newdn) != 0) {
			 // Check for distribution lists with this user as member
			 $ldap->search( $_SESSION['base_dn'], 
							'(&(objectClass=kolabGroupOfNames)(member='.$ldap->escape($dn).'))',
							array( 'dn', 'mail' ) );
			 $distlists = $ldap->getEntries();
			 unset( $distlists['count'] );
			 foreach( $distlists as $distlist ) {
				 $dlcn = $distlist['mail'][0];
				 $errors[] = _("Account DN could not be modified, distribution list <a href='/admin/distributionlist/list.php?action=modify&dn=")
				   .urlencode($distlist['dn']).
				   _("'>'$dlcn'</a> depends on it. To modify this account, first remove it from the distribution list.");
			 }

			 if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				 ($entry=ldap_first_entry($ldap->connection,$result)) &&
				 ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {
			   $ldap_object['uid'] = $oldattrs['uid'][0];
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
			   if ( !$errors && !ldap_rename($ldap->connection,$dn,$tmprdn,$tmpbasedn,false) ) {
				 array_push($errors, _("LDAP Error: Could not rename $dn to $tmprdn: ")
							.ldap_error($ldap->connection));				 
			   }
			   if ( !$errors && !ldap_add($ldap->connection,$newdn, $ldap_object) ) {
				 array_push($errors, _("LDAP Error: Could not rename $dn to $newdn: ")
							.ldap_error($ldap->connection));
			   }
			   if( !$errors ) {
				 if( !ldap_delete($ldap->connection,$tmprdn.','.$tmpbasedn)) {
				   array_push($errors, _("LDAP Error: Could not remove old entry $tmprdn,$tmpbasedn: ")
							  .ldap_error($ldap->connection));
				 }
			   }
			   $dn = $newdn;
			 } else array_push($errors,_("LDAP Error: Could not read $dn: ")
							   .ldap_error($ldap->connection));
		   } else {
			 //$ldap_object = fill_up($ldap_object);
			 if ($auth->group() == "user") {
			   unset($ldap_object['sn']);
			   unset($ldap_object['cn']);
			   unset($ldap_object['mail']);
			   unset($ldap_object['uid']);
			   unset($ldap_object['kolabHomeServer']);
			 }
			 debug_var_dump($ldap_object);
			 if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {			   
			   array_push($errors, _("LDAP Error: Could not modify object $dn: ")
						  .ldap_error($ldap->connection));
			 }
		   }
		   // Check for collisions on alias
		   for( $i = 0; $i < count($ldap_object['alias']); ++$i ) {
			 if( $ldap->countMail( $_SESSION['base_dn'], $alias, $dn ) > 0 ) {
			   // Ups!!!
			   $alias = $ldap_object['alias'][$i];
			   $newalias = md5sum( $dn.$alias ).'@'.substr( $alias, 0, strpos( $alias, '@' ) );
			   $ldap_object['alias'][$i] = $newalias;
			   if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
				 $errors[] = _("LDAP Error: Could not modify object $dn: ").ldap_error($ldap->connection);
			   }
			   $error[] = _("Mid-air collision detected, alias $alias renamed to $newalias");
			 }
		   }
		 }
		 $heading = _('Modify User');
		 if( !$errors ) $messages[] = _("User '$dn' successfully modified");
		 $form->setValues();
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
		   $dn = "cn=".$ldap->dn_escape($ldap_object['cn']).$dn_add.",".$domain_dn;
		   foreach( $ldap_object as $k => $v ) {
			 if( $v == array() ) unset($ldap_object[$k]);
		   }
		   debug("Calling ldap_add with dn=$dn");
		   if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object)) 
			 array_push($errors, _("LDAP Error: could not add object $dn: ").ldap_error($ldap->connection));

		   // Check for mid-air collisions on mail
		   if( $ldap->countMail( $_SESSION['base_dn'], $ldap_object['mail'], $dn ) > 0 ) {
			 // Ups!!!
			 $mail = $ldap_object['mail'];
			 $newmail = md5sum( $dn.$mail ).'@'.substr( $mail, 0, strpos( $mail, '@' ) );
			 $ldap_object['uid'] = $ldap_object['mail'] = $newmail;
			 if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
			   $errors[] = _("LDAP Error: Could not modify object $dn: ").ldap_error($ldap->connection);
			 }
			 $error[] = _("Mid-air collision detected, email address $mail renamed to $newmail");
		   }

		   // Check for collisions on alias
		   for( $i = 0; $i < count($ldap_object['alias']); ++$i ) {
			 if( $ldap->countMail( $_SESSION['base_dn'], $alias, $dn ) > 0 ) {
			   // Ups!!!
			   $alias = $ldap_object['alias'][$i];
			   $newalias = md5sum( $dn.$alias ).'@'.substr( $alias, 0, strpos( $alias, '@' ) );
			   $ldap_object['alias'][$i] = $newalias;
			   if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
				 $errors[] = _("LDAP Error: Could not modify object $dn: ").ldap_error($ldap->connection);
			   }
			   $error[] = _("Mid-air collision detected, alias $alias renamed to $newalias");
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
		   $form->outputForm();
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
   if (!$dn) array_push($errors, _("Error: need dn for delete operation"));
   elseif ($auth->group() != "maintainer" && $auth->group() != "admin") 
     array_push($errors, _("Error: you need administrative permissions to delete users"));

   // Check for distribution lists with only this user as member
   $ldap->search( $_SESSION['base_dn'], 
				  '(&(objectClass=kolabGroupOfNames)(member='.$ldap->escape($dn).'))',
				  array( 'dn', 'cn', 'member' ) );
   $distlists = $ldap->getEntries();
   foreach( $distlists as $distlist ) {
	 if( $distlist['member']['count'] == 1 ) {
	   $dlcn = $distlist['cn'][0];
	   $errors[] = _("Account could not be deleted, distribution list '$dlcn' depends on it.");
	 }
   }

   if( !$errors ) {
	 if (!$ldap->deleteObject($dn)) {
	   array_push($errors, _("LDAP Error: could not mark '$dn' for deletion: ").$ldap->error());
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
$smarty->assign( 'errors', array_merge($errors,$form->errors) );
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
