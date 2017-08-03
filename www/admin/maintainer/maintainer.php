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

require_once('KolabAdmin/include/mysmarty.php');
require_once('KolabAdmin/include/headers.php');
require_once('KolabAdmin/include/locale.php');
require_once('KolabAdmin/include/authenticate.php');
require_once('KolabAdmin/include/form.class.php');
require_once('KolabAdmin/include/passwd.php');

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
  return $_SESSION['base_dn'];
}

// Check that a uid is unique
function checkuniquemail( $form, $key, $value ) {
  debug("checkuniquemail( $form, $key, $value )");
  if( is_unique( 'uid', $value ) ) {
	return '';
  } else {
	return _('Account with this UID already exists');
  }
}

function fill_form_for_modify( &$form, &$ldap_object ) {
  global $auth;
  if (is_array($ldap_object['sn'])) $lastname = $ldap_object['sn'][0];
  else $lastname = $ldap_object['sn'];
  if (is_array($ldap_object['cn'])) $cn = $ldap_object['cn'][0];
  else $cn = $ldap_object['cn'];
  $firstname = KolabLDAP::getGivenName($cn, $lastname);
  if (is_array($ldap_object['uid'])) $uid = $ldap_object['uid'][0];
  else $uid = $ldap_object['uid'];
  $form->entries['firstname']['value'] = $firstname;
  $form->entries['lastname']['value'] = $lastname;
  $form->entries['password_0']['value'] = '';
  $form->entries['password_1']['value'] = '';
  $form->entries['uid']['value'] = $uid;
  $form->entries['uid']['attrs'] = 'readonly';
  if( $auth->group() != 'admin' ) {
    $form->entries['firstname']['attrs'] = 'readonly';
	$form->entries['lastname']['attrs'] = 'readonly';
  }
  /*
  foreach( array( 'title', 'o', 'ou', 'street', 'postOfficeBox',
                  'postalCode', 'l', 'c', 'telephoneNumber',
                  'facsimileTelephoneNumber' ) as $attr ) {
    if (is_array($ldap_object[$attr])) $v = $ldap_object[$attr][0];
    else $v = $ldap_object[$attr];
    $form->entries[$attr.'_0']['value'] = $v;
  }
  if (is_array($ldap_object['alias'])) {
	$arr = $ldap_object['alias'];
	unset( $arr['count'] );
	$v = join("\n", $arr );
  }
  else $v = $ldap_object[$attr];
  $form->entries['alias']['value'] = $v;
  $form->entries['action']['value'] = 'save';
  if( isset( $form->entries['userquota'] ) ) {
    if (is_array($ldap_object['userquota'])) $userquota = $ldap_object['userquota'][0];
    else $userquota = $ldap_object['userquota'];
    if( $userquota > 0 ) {
      $form->entries['userquota']['value'] = $userquota;
    } else {
      $form->entries['userquota']['value'] = '';
    }
  }
  */
}

/**** Authentication etc. ***/
$sidx = 'maintainer';

require_once('KolabAdmin/include/menu.php');
$menuitems[$sidx]['selected'] = 'selected';

/**** Logic ***/
$errors = array();
$messages = array();
$valid_actions = array('save','firstsave','modify','create','delete','kill');
$contenttemplate = 'formcontainer.tpl';

// Get request data
if (!empty($_REQUEST['action']) &&
    in_array($_REQUEST['action'],$valid_actions)) $action = trim(urldecode($_REQUEST['action']));
else array_push($errors, _("Error: need valid action to proceed"));
$dn="";
if (!empty($_REQUEST['dn'])) $dn = trim(urldecode($_REQUEST['dn']));

// Check auth
if (!$errors && $auth->group() != 'admin' && $auth->group() != 'maintainer' ) {
  array_push($errors, _("Error: You don't have the required Permissions"));
}

// Fill in data
if ($action == "create") {
  $comment_mail_0 = _('Required, non volatile');
  $comment_password = _('Required');
} else {
  $comment_mail_0 = _('non volatile');
  $comment_password = _('Leave blank to keep password unchanged');
}

$entries = array( 'firstname' => array( 'name' => _('First Name'),
					'validation' => 'notempty',
					'comment' => _('Required') ),
		  'lastname' => array( 'name' => _('Last Name'),
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
		  'uid' => array( 'name' => _('Unique User ID'),
				     'validation' => 'notempty',
				     'comment' => $comment_mail_0 ));

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

$form =& new KolabForm( 'maintainer', 'createmaintainer.tpl', $entries );
/***************** Main action swicth **********************/
switch( $action ) {
 case 'firstsave':
   debug("adding checkuniquemail to validation");
   $ldap_object['objectClass'] = array( 'top', 'inetOrgPerson', 'kolabInetOrgPerson');
   $form->entries['uid']['validation'] = 'checkuniquemail';
 case 'save':
   if( $form->isSubmitted() ) {
     if( !$form->validate() ) {
       $form->setValues();
	   if ($action != "firstsave")
			fill_form_for_modify( $form, $ldap_object );
       $content = $form->outputForm();
     } else {
       debug("Process...");
       $ldap_object = array();
       $ldap_object['objectClass'] = $oc;
       $ldap_object['sn'] = trim($_POST['lastname']);
       $ldap_object['cn'] = trim($_POST['firstname']).' '.$ldap_object['sn'];
       if( !empty( $_POST['password_0'] ) ) {
		 $ldap_object['userPassword'] = ssha( $_POST['password_0'], gensalt());
       }
       if( $action == 'firstsave' ) $ldap_object['uid'] = trim( strtolower( $_POST['uid'] ) );

	   debug_var_dump( $ldap_object );

       $domain_dn = domain_dn();

       if ($action == "save") {
		 if (!$errors) {
		   if (!empty($ldap_object['cn'])) $newdn = "cn=".$ldap->dn_escape($ldap_object['cn']).",cn=internal,".$domain_dn;
		   else $newdn = $dn;
		   if (!$visible && !strstr($newdn,$dn_add)) {
			 list($cn,$rest) = split(',', $newdn, 2);
			 $newdn = $cn.$dn_add.",".$rest;
		   }
		   if (strcmp($dn,$newdn) != 0) {
			 if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				 ($entry=ldap_first_entry($ldap->connection,$result)) &&
				 ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {
			   $ldap_object['uid'] = $oldattrs['uid'][0];
			   if( empty($ldap_object['userPassword']) )
				 $ldap_object['userPassword'] = $oldattrs['userPassword'][0];

			   // Try to rename the object
			   if (!ldap_rename($ldap->connection, $dn, "cn=" . $ldap->dn_escape($ldap_object['cn']), "cn=internal,".$domain_dn, true)) {
				 array_push($errors, sprintf(_("LDAP Error: could not rename %s to %s: %s"), $dn,
											 $newdn, ldap_error($ldap->connection)));
			   }
			   if( !$errors ) {
				 // Renaming was ok, now try to modify the object accordingly
				 if (!ldap_modify($ldap->connection, $newdn, $ldap_object)) {
				   // While this should not happen, in case it does, we need to revert the
				   // renaming
				   array_push($errors, sprintf(_("LDAP Error: could not modify %s to %s: %s"), $newdn,
											   ldap_error($ldap->connection)));
				   $old_dn = substr($dn, 0, strlen($dn) - strlen("cn=internal,".$domain_dn) - 1);
				   ldap_rename($ldap->connection, $newdn, $old_dn, "cn=internal,".$domain_dn, true);
				 } else {
				   // everything is fine and we can move on
				   $messages[] = sprintf( _("%s successfully updated"), $newdn);
				   $dn = $newdn;
				 }
			   }
			 } else array_push($errors, sprintf(_("LDAP Error: could not read %s: %s"), $dn,
												ldap_error($ldap->connection)));
		   } else {
			 if (!ldap_modify($ldap->connection, $dn, $ldap_object)) {
			   array_push($errors, sprintf(_("LDAP Error: could not modify object %s: %s"), $dn,
										   ldap_error($ldap->connection)));
			 }
		   }
		 }
		 $heading = _('Modify Maintainer');
		 $messages[] = _('Maintainer ').$ldap_object['dn']._(' successfully modified');
		 $form->setValues();
		 $form->entries['password_0']['value'] = '';
		 $form->entries['password_1']['value'] = '';
		 if( $auth->group() != 'admin' ) {
		   $form->entries['firstname']['attrs'] = 'readonly';
		   $form->entries['lastname']['attrs'] = 'readonly';
		 }
		 $form->entries['uid']['attrs'] = 'readonly';
		 $form->entries['action']['value'] = 'save';
		 $content = $form->outputForm();
		 break;
       } else {
		 // firstsave
		 if (!$errors) {
		   $dn = "cn=".$ldap->dn_escape($ldap_object['cn']).",cn=internal,".$domain_dn;
		   debug("Calling ldap_add with dn=$dn");
		   if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object))
			 array_push($errors, sprintf( _("LDAP Error: could not add object %s: %s"), $dn,
										  ldap_error($ldap->connection)));
		   if( $dn && !ldap_mod_add($ldap->connection, 'cn=maintainer,cn=internal,'.$domain_dn,
									array( 'member' => $dn ) ) ) {
			 array_push($errors, sprintf(_("LDAP Error: could not add object %s to maintainer group: %s"), $dn,
										 ldap_error($ldap->connection)));
		   }
		   if( !$errors ) {
			 $messages[] = _('Maintainer ').$ldap_object['dn']._(' successfully created');
			 $heading = _('Create New Maintainer');
			 $form->entries['action']['value'] = 'firstsave';
			 $content = $form->outputForm();
			 break;
		   }
		 } else {
		   $heading = _('Create New Maintainer');
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
   $heading = _('Create New Maintainer');
   if( !$dn ) {
     $form->entries['action']['value'] = 'firstsave';
   } else {
     $form->entries['action']['value'] = 'save';
   }
   $content = $form->outputForm();
   break;
 case 'modify':
   $heading = _('Modify Maintainer');
   fill_form_for_modify( $form, $ldap_object );
   $form->entries['action']['value'] = 'save';
   $content = $form->outputForm();
   break;
 case 'delete':
   $heading = _('Delete Maintainer');
   foreach( $form->entries as $k => $v ) {
     if( $v['type'] != 'hidden' ) {
       $form->entries[$k]['attrs'] = 'readonly';
     }
   }
   fill_form_for_modify( $form, $ldap_object );
   $form->entries['action']['value'] = 'kill';
   $form->submittext = 'Delete';
   $content = $form->outputForm();
   break;
 case 'kill':
   if (!$dn) array_push($errors, _("Error: need DN for delete operation"));
   elseif ($auth->group() != "maintainer" && $auth->group() != "admin")
     array_push($errors, _("Error: you need administrative permissions to delete users"));

   if (!$errors) {
	 if(!ldap_mod_del($ldap->connection, 'cn=maintainer,cn=internal,'.domain_dn(), array('member' => $dn ) )) {
	   $errors[] = sprintf(_("LDAP Error: Could not remove %s from maintainer group: %s"), $dn,
						   ldap_error($ldap->connection));
	 }
	 if( !$errors ) {
	   $delete_template['kolabDeleteflag'] = 'TRUE';
	   if( !$ldap->deleteObject($dn)) {
		 array_push($errors, sprintf(_("LDAP Error: could not mark %s for deletion: %s"), $dn.
									 ldap_error($ldap->connection)));
	   }
	 }
   }
   if( !$errors ) {
	 $heading = _('Maintainer Deleted');
	 $contenttemplate = 'maintainerdeleted.tpl';
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
