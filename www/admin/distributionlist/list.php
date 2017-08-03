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


/**** Authentication etc. ***/
$errors = array();
$messages = array();
$sidx = 'distlist';
$contenttemplate = 'formcontainer.tpl';
$valid_actions = array('firstsave','save','modify','create','delete','kill');

$group = $auth->group();
if( $group != 'maintainer' && $group != 'admin' && $group != 'domain-maintainer' ) {
   array_push($errors, _("Error: You don't have Permissions to access this Menu") );
}

require_once('KolabAdmin/include/menu.php');

function checkemaillist( $form, $key, $value ) {
  global $ldap;
  if( $key == 'members' ) {
	$lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
	if( count($lst) < 1 ) return _('Please add at least one member');
	foreach( $lst as $a ) {
	  debug("Trying $a");
	  ($dn = $ldap->dnForMail($a)) || ($dn = $ldap->dnForUid($a)) || ($dn = $ldap->dnForAlias($a));
	  if( !$dn ) return sprintf( _("No user with email address, UID or alias %s"), $a);
	}
  }
  return '';
}

function checkuniquemail( $form, $key, $value ) {
  global $ldap;
  if( $key == 'cn' ) {
	// Here we have the required hack again:
	// email address is <value of cn>@default-domain
	if( $ldap->countMail( $_SESSION['base_dn'], $value ) > 0 ) {
	  return _('User or distribution list with this email address already exists');
	}
  }
  return '';
}

function domain_dn()
{
  return $_SESSION['base_dn'];
}

function fill_form_for_modify( &$form, &$ldap_object ) {
  global $dn;

  if (is_array($ldap_object['cn'])) $cn = $ldap_object['cn'][0];
  else $cn = $ldap_object['cn'];
  $form->entries['cn']['value'] = $cn;
  if ($_REQUEST['action'] != "firstsave")
	$form->entries['cn']['attrs'] = "readonly";

  $form->entries['action']['value'] = 'save';
  $m = $ldap_object['member'];
  unset( $m['count'] );
  debug_var_dump( $m );
  $form->entries['members']['value'] = join("\r\n",
											array_map( create_function( '$dn',
											'global $ldap; return $ldap->mailForDn($dn);' ), $m) );
  $internaldn = 'cn=internal,'.domain_dn();
  debug("internaldn=\"$internaldn\"");
  debug("dn=\"$dn\"");
  debug("substr=\"".substr( $dn, strlen($dn)-strlen( $internaldn ) )."\"");
  if( substr( $dn, strlen($dn)-strlen( $internaldn ) ) === $internaldn
		|| preg_match("/cn=internal/",$dn) ) {
	$form->entries['hidden']['value'] = true;
  } else {
	$form->entries['hidden']['value'] = false;
  }
}


/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';
$heading = '';

/**** Form/data handling ***/
if (!empty($_REQUEST['action']) &&
    in_array($_REQUEST['action'],$valid_actions)) $action = trim($_REQUEST['action']);
else array_push($errors, _("Error: need valid action to proceed") );

$dn="";
if (!empty($_REQUEST['dn'])) $dn = trim($_REQUEST['dn']);

$entries = array( 'cn' => array( 'name' => _('List Name'),
								 'type' => 'email',
								 'domains' => ($auth->group()=='domain-maintainer')
								 ?$ldap->domainsForMaintainerDn($auth->dn())
								 :$ldap->domains(),
								 'validation' => 'notempty',
								 'comment' => _('Required') ),
				  'members' => array( 'name' => _('Members'),
									  'type' => 'textarea',
									  'comment' => _('One email address per line'),
									  'validation' => 'checkemaillist'));

$entries['hidden'] = array( 'name' => _('Hidden'),
							'type' => 'checkbox',
							'value' => false,
							'comment' => _('Check here to make this distribution list available only to authenticated users'));

$entries['action'] = array( 'name' => 'action',
							'type' => 'hidden' );

if( $action == 'modify' || $action == 'delete' || $action == 'kill' ) {
  if( $_REQUEST['dn'] ) {
	$dn = $_REQUEST['dn'];
  } else {
	array_push($errors, sprintf(_("Error: DN required for %s operation"), $action ) );
  }
}

$form =& new KolabForm( 'vcard', 'createaddr.tpl', $entries );

if( !$errors ) {
  switch( $action ) {
  case 'create':
	$form->entries['action']['value'] = 'firstsave';
	$heading = _('Add Distribution List');
	$content = $form->outputForm();
	break;
  case 'firstsave':
	$form->entries['cn']['validation'] = array('notempty', 'checkuniquemail');
  case 'save':
	if( $form->isSubmitted() ) {
	  if( !$form->validate() ) {
		fill_form_for_modify( $form, $ldap_object );
		$form->setValues();
		$content = $form->outputForm();
	  } else {
		$dl_root = domain_dn();

		if (!empty($_POST['hidden']) && $_POST['hidden'] == "on")
		  $visible = false;
		else $visible = true;
		if (!$visible) $dl_root = "cn=internal,".$dl_root;

		$ldap_object = array('objectClass' => 'kolabGroupOfNames');
		$cn = strtolower(trim( trim($_POST['user_cn']).'@'.$_POST['domain_cn']));

		// Keep cn and mail in sync
		$ldap_object['cn'] = $cn;
		$ldap_object['mail'] = $cn;

		$ldap_object['member'] = array();
		$lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', trim($_POST['members']) ) ), 'strlen') );
		foreach( $lst as $a ) {
		  debug("Translating $a");
		  ($memberdn = $ldap->dnForMail($a)) || ($memberdn = $ldap->dnForUid($a)) || ($memberdn = $ldap->dnForAlias($a));
		  debug("Found $memberdn");
		  if( $memberdn ) {
			$ldap_object['member'][] = $memberdn;
		  } else {
			$errors[] = sprintf(_("No user with address %s"), $a);
			break;
		  }
		}
		if( !$ldap_object['member'] ) unset($ldap_object['member']);

		if ($action == "save") {
		  if (!$errors) {
			if (!empty($ldap_object['cn'])) $newdn = "cn=".$ldap->dn_escape($ldap_object['cn']).",".$dl_root;
			else $newdn = $dn;
			if (strcmp($dn,$newdn) != 0) {
			  if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				  ($entry=ldap_first_entry($ldap->connection,$result)) &&
				  ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {

				// Try to rename the object
				if (!ldap_rename($ldap->connection, $dn, "cn=" . $ldap->dn_escape($ldap_object['cn']), $dl_root, true)) {
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
					$old_dn = substr($dn, 0, strlen($dn) - strlen($dl_root) - 1);
					ldap_rename($ldap->connection, $newdn, $old_dn, $dl_root, true);
				  } else {
					// everything is fine and we can move on
					$messages[] = _('Distribution List updated');
					$dn = $newdn;
				  }
				}

				$dn = $newdn;
			  } else array_push($errors, sprintf( _("LDAP Error: Could not read %s: %s"), $dn,
												  ldap_error($ldap->connection)));
			} else {
			  if (!ldap_modify($ldap->connection, $dn, $ldap_object))
				array_push($errors, sprintf( _("LDAP Error: Could not modify object %s: %s"), $dn,
											 ldap_error($ldap->connection)));
			  else $messages[] = _('Distribution List updated');
			}
		  }
		} else {
		  // firstsave
		  if (!$errors) {
			if( !$ldap_object['member'] ) unset($ldap_object['member']);
			$dn = "cn=".$ldap->dn_escape($ldap_object['cn']).",".$dl_root;
			if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object)) {
			  array_push($errors, sprintf( _("LDAP Error: Could not add object %s: %s"), $dn,
										   ldap_error($ldap->connection)));
			  debug("dn is $dn");
			  debug_var_dump( $ldap_object );
			}

			if( !$errors ) {
			  // Check for mid-air collisions on mail
			  $kolab = $ldap->read( 'k=kolab,'.$_SESSION['base_dn'] );
			  for( $i = 0; $i < $kolab['postfix-mydomain']['count']; $i++ ) {
				$domain = $kolab['postfix-mydomain'][$i];
				if( $ldap->countMail( $_SESSION['base_dn'], $ldap_object['cn'].'@'.$domain, $dn ) > 0 ) {
				  // Ups!!!
				  $cn = $ldap_object['cn'];
				  $newcn = md5( $dn.$cn );
				  $ldap_object['cn'] = $newcn;
				  $ldap_object['dn'] = 'cn='.$ldap->escape($newcn).','.$dl_root;
				  if (!ldap_rename($ldap->connection, $dn, 'cn='.$ldap->escape($newcn), $dl_root,true)) {
					$errors[] = sprintf(_("LDAP Error: Could not modify object %s: %s"), $dn,
										ldap_error($ldap->connection));
				  }
				  $error[] = sprintf( _("Mid-air collision detected, email address %1\$s renamed to %2\$s"),
									  $mail, $newmail);
				  break;
				}
			  }
			}
		  }
		  if( !$errors ) {
			$messages[] = sprintf(_("Distribution List '%s' added"), $cn );
		  }
		}
		if ($errors) {
		  //print("<div class=\"maintitle\"> Create New Address Book Entry </div>\n");
		  $form->entries['action']['value'] = 'create';
		  fill_form_for_modify( $form, $ldap_object );
		  $content = $form->outputForm();
		} else {
		  $form->entries['action']['value'] = 'save';
		  $form->entries['dn'] = array( 'type' => 'hidden', 'value' => $dn );
		  $form->entries['cn']['attrs'] = 'readonly';
		  $heading = _('Modify Distribution List');
		  fill_form_for_modify( $form, $ldap_object );
		  $content = $form->outputForm();
		}
	  }
	}
	break;
  case 'modify':
	$ldap_object = $ldap->read( $dn );
	if( $ldap_object ) {
		fill_form_for_modify( $form, $ldap_object );
		$form->entries['action']['value'] = 'save';
		$form->entries['dn'] = array( 'type' => 'hidden', 'value' => $dn );
		$form->entries['cn']['attrs'] = 'readonly';
		$heading = _('Modify Distribution List');
		$content = $form->outputForm();
	} else {
	  array_push($errors, sprintf( _("Error: No results returned for DN '%s'"), $dn));
	}
	break;
  case 'delete':
	$ldap_object = $ldap->read( $dn );
	if( $ldap_object ) {
	  fill_form_for_modify( $form, $ldap_object );
	  $form->entries['action']['value'] = 'kill';
	  foreach( $form->entries as $key => $val ) {
		$form->entries[$key]['attrs'] = 'readonly';
	  }
	  $form->submittext = _('Delete');
	  $heading = _('Delete Distribution List');
	  $content = $form->outputForm();
	} else {
	  array_push($errors, sprintf( _("Error: No results returned for DN '%s'"), $dn));
	}
	break;
  case 'kill':
	if (!$errors) {
	  /* Just delete the object and let kolabd clean up */
	  if ($ldap->deleteGroupOfNames($dn)) {
		$messages[] = _('Distribution List ').$_REQUEST['cn']._(' deleted');
		$heading = _('Entry Deleted');
		$contenttemplate = 'distlistdeleted.tpl';
	  } else {
		array_push($errors, sprintf(_("LDAP Error: Could not delete %s: %s"), $dn, $ldap->error()));
	  }
	}
	break;
  }
}

/**** Insert into template and output ***/
$smarty =& new MySmarty();
$smarty->assign( 'errors', array_merge((array)$errors,(array)$form->errors) );
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
