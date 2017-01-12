<?php
require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');
require_once('admin/include/form.class.php');


/**** Authentication etc. ***/
$errors = array();
$messages = array();
$sidx = 'addressbook';
$contenttemplate = 'formcontainer.tpl';
$valid_actions = array('firstsave','save','modify','create','delete','kill');

if( $auth->group() != 'maintainer' && $auth->group() != 'admin') {
   array_push($errors, _("Error: You don't have Permissions to access this Menu") );
}

require_once('admin/include/menu.php');

function fill_form_for_modify( &$form, &$ldap_object ) {
  if (is_array($ldap_object['sn'])) $lastname = $ldap_object['sn'][0];
  else $lastname = $ldap_object['sn'];
  if (is_array($ldap_object['cn'])) $cn = $ldap_object['cn'][0];
  else $cn = $ldap_object['cn'];
  if ($lastname) {
    $a = strlen($lastname);
    if ($cn) {
      $b = strlen($cn);
      $firstname = trim(substr($cn, 0, $b - $a));
    }
  }
  if (is_array($ldap_object['mail'])) $mail_0 = $ldap_object['mail'][0];
  else $mail_0 = $ldap_object['mail'];
  $form->entries['firstname']['value'] = $firstname;
  $form->entries['lastname']['value'] = $lastname;
  $form->entries['mail']['value'] = $mail_0;

  foreach( array( 'title', 'o', 'ou', 'roomNumber', 'street', 'postOfficeBox',
                  'postalCode', 'l', 'c', 'telephoneNumber',
                  'facsimileTelephoneNumber' ) as $attr ) {	
    if (is_array($ldap_object[strtolower($attr)])) $v = $ldap_object[strtolower($attr)][0];
    else $v = $ldap_object[strtolower($attr)];
    $form->entries[$attr]['value'] = $v;
  }
  if (is_array($ldap_object['alias'])) {
	$arr = $ldap_object['alias'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = $ldap_object['alias'];
  $form->entries['alias']['value'] = $v;
  $form->entries['action']['value'] = 'save';
}

$dn="";
if (!empty($_REQUEST['dn'])) $dn = trim($_REQUEST['dn']);

function checkuniquemail( $form, $key, $value ) {
  global $ldap;
  global $dn;
  global $action;
  $value = trim($value);
  if( $value == '' ) return ''; // OK

  $excludedn = false;
  if( $action == 'save' ) $excludedn = trim($dn);

  if( $ldap->countMail( $_SESSION['base_dn'], $value, $excludedn ) > 0 ) {	
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

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';
$heading = '';

/**** Form/data handling ***/
if (!empty($_REQUEST['action']) && 
    in_array($_REQUEST['action'],$valid_actions)) $action = trim($_REQUEST['action']);
else array_push($errors, _("Error: need valid action to proceed") );

if (!$errors && $auth->group() != 'maintainer' && $auth->group() != 'admin') 
	 array_push($errors, _("Error: You don't have the required Permissions") );

$attributes = array( 'title', 'cn', 'sn', 'mail', 'alias', 'o',
                     'ou', 'roomNumber', 'street', 'postOfficeBox',
                     'postalCode', 'l', 'c', 'telephoneNumber',
                     'facsimileTelephoneNumber' );

$entries = array( 'firstname' => array( 'name' => _('First Name'),
										'validation' => 'notempty',
										'comment' => _('Required') ),
				  'lastname' => array( 'name' => _('Last Name'),
									   'validation' => 'notempty',
									   'comment' => _('Required') ),
				  'title' => array( 'name' => _('Title') ),
				  'mail'  => array( 'name' => _('Primary E-Mail Address'),
									'validation' => 'checkuniquemail' ),
				  'alias' => array( 'name' => _('E-Mail Aliases'),
									'type' => 'textarea',
									'validation' => 'checkuniquealias',
									'comment' => _('One address per line')),
				  'o' => array( 'name' => _('Organisation') ),
				  'ou' => array( 'name' => _('Organisational Unit') ),
				  'roomNumber' => array( 'name' => _('Room Number') ),
				  'street' => array( 'name' => _('Street Address') ),
				  'postOfficeBox' => array( 'name' => _('Post Box') ),
				  'postalCode' => array( 'name' => _('Postal Code') ),
				  'l' => array( 'name' => _('City') ),
				  'c' => array( 'name' => _('Country') ),
				  'telephoneNumber' => array( 'name' => _('Telephone Number') ),
				  'facsimileTelephoneNumber' => array( 'name' => _('Fax Number') ));
$entries['action'] = array( 'name' => 'action',
							'type' => 'hidden' );

$dn = '';
if( $action == 'modify' || $action == 'delete' || $action == 'save' || $action == 'kill' ) {
  if( $_REQUEST['dn'] ) {
	$dn = $_REQUEST['dn'];
  } else {  
	array_push($errors, _("Error: DN required for $action operation") );
  }
}

$form =& new KolabForm( 'vcard', 'createaddr.tpl', $entries );

if( !$errors ) {
  switch( $action ) {
  case 'create':
	$form->entries['action']['value'] = 'firstsave';
	$heading = _('Add External Address'); 
	$content = $form->outputForm();
	break;
  case 'firstsave':
  case 'save':	
	if( $form->isSubmitted() ) {
	  if( !$form->validate() ) {
		$form->setValues();
		$content = $form->outputForm();
	  } else {
		$addressbook_root = "cn=external,".$_SESSION['base_dn'];   
		$ldap_object = array('objectClass' => array( 'top', 'inetOrgPerson', 'kolabInetOrgPerson' ) );
		$firstname = trim($_POST['firstname']);
		$lastname = trim($_POST['lastname']);
		$ldap_object['sn'] = trim($lastname);
		$ldap_object['cn'] = trim( $firstname.' '.$ldap_object['sn']);
		$ldap_object['givenName'] = trim($firstname);
		foreach ($attributes as $attr) {
		  if ($attr == 'sn' || $attr == 'cn' || $attr == 'alias' ) continue;
		  $count = 0;
		  $key = $attr;
		  $args = array();
		  while (!empty($_POST[$key])) {
			$args[$count] = $_POST[$key];
			$count++;
			$key = $attr."_".$count;
		  }
		  if ($count > 0) $ldap_object[$attr] = $args;
		  elseif (!empty($_POST[$key])) $ldap_object[$attr] = $_POST[$key];  
		  else $ldap_object[$attr] = array();
		}
		$ldap_object['alias'] = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $_POST['alias'] ) ), 'strlen') );

		if ($action == "save") {
		  if (!$errors) {
			if (!empty($ldap_object['cn'])) $newdn = "cn=".$ldap_object['cn'].",".$addressbook_root;
			else $newdn = $dn;
			debug("action=save, dn=$dn, newdn=$newdn<br/>\n");
			if (strcmp($dn,$newdn) != 0) {
			  if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				  ($entry=ldap_first_entry($ldap->connection,$result)) &&
				  ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {
				foreach( $ldap_object as $k => $v ) if( $v == array() ) unset( $ldap_object[$k] );
				if (!ldap_add($ldap->connection,$newdn, $ldap_object) || !ldap_delete($ldap->connection,$dn)) {
				  array_push($errors, _("LDAP Error: could not rename ").$dn.
							 " to ".$newdn." ".ldap_error($ldap->connection));
				} else {
				  $messages[] = _("$newdn successfully updated");
				}
				$dn = $newdn;
			  } else {
				array_push($errors,_("LDAP Error: could not read ").$dn." ".ldap_error($ldap->connection));
			  }
			} else {
			  if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
				array_push($errors, _("LDAP Error: could not modify object ").$dn.": ".ldap_error($ldap->connection)); 
			  } else {
				$messages[] = _("$dn successfully updated");
			  }
			}
		  } 
		} else {
		  if (!$errors) {
			$dn = "cn=".$ldap_object['cn'].",".$addressbook_root;
			foreach( $ldap_object as $k => $v ) if( $v == array() ) unset( $ldap_object[$k] );
			if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object)) {
			  array_push($errors, _("LDAP Error: could not add object ").$dn.": ".ldap_error($ldap->connection));
			} else {
				  $messages[] = _("$dn successfully added");
			}
		  }
		  if ($errors) {
			//print("<div class=\"maintitle\"> Create New Address Book Entry </div>\n");
			$form->entries['action']['value'] = 'create';
			break;
		  }
		}
		$form->entries['action']['value'] = 'modify';
		$heading = _('Modify External Address');
		$form->setValues();
		$form->entries['dn'] = array( 'name' => 'dn',
									  'type' => 'hidden',
									  'value' => $dn );
		$content = $form->outputForm();		
		
	  }
	}
	break;
  case 'modify':
	$result = $ldap->search( $dn, '(objectClass=inetOrgPerson)' );
	if( $result ) {
	  $ldap_object = ldap_get_entries( $ldap->connection, $result );
	  if( $ldap_object['count'] == 1 ) {
		fill_form_for_modify( $form, $ldap_object[0] );
		$form->entries['action']['value'] = 'save';
		$form->entries['dn'] = array( 'name' => 'dn',
									  'type' => 'hidden',
									  'value' => $dn );
		$heading = _('Modify External Address'); 
		$content = $form->outputForm();
	  } else {
		array_push($errors, _("Error: Multiple results returned for DN $dn") );
	  }
	}
	break;
  case 'delete':
	foreach( $form->entries as $k => $v ) {
	  if( $v['type'] != 'hidden' ) {
		$form->entries[$k]['attrs'] = 'readonly';
	  }
	}
	$result = $ldap->search( $dn, '(objectClass=*)' );
	if( $result ) {
	  $ldap_object = ldap_get_entries( $ldap->connection, $result );
	  if( $ldap_object['count'] == 1 ) {
		fill_form_for_modify( $form, $ldap_object[0] );
		$form->entries['action']['value'] = 'kill';
		foreach( array_keys($form->entries) as $key ) {
		  $form->entries[$key]['attrs'] = 'readonly';
		}
		$form->submittext = _('Delete');
		$heading = _('Delete External Address');
		$content = $form->outputForm();
	  } else {
		array_push($errors, _("Error: Multiple results returned for DN $dn") );
	  }
	}
	break;
  case 'kill':
	if (!$errors) {    
	  if (!($ldap->deleteObject($dn))) {
		array_push($errors, _("LDAP Error: could not delete ").$dn.": ".ldap_error($ldap->connection));
	  } else {
		$heading = _('Entry Deleted');
		$messages[] = _("Address book entry with DN $dn  was deleted");
		$contenttemplate = 'addrdeleted.tpl';
	  }
	}
	break;
  }
}

/**** Insert into template and output ***/
$smarty =& new MySmarty();
$smarty->assign( 'errors', array_merge($errors,$form->errors) );
$smarty->assign( 'messages', $messages );
$smarty->assign( 'heading', $heading );
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
