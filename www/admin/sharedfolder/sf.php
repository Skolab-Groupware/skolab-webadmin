<?php
/*
 * (c) 2004 Klarälvdalens Datakonsult AB
 *     Written by Steffen Hansen <steffen@klaralvdalens-datakonsult.se>   
 *
 * This program is Free Software under the GNU General Public License (>=v2).
 * Read the file COPYING that comes with this packages for details.
*/

require_once('admin/include/mysmarty.php');
require_once('admin/include/headers.php');
require_once('admin/include/locale.php');
require_once('admin/include/authenticate.php');
require_once('admin/include/form.class.php');

/**** Authentication etc. ***/
$errors = array();
$messages = array();
$sidx = 'sf';
$contenttemplate = 'formcontainer.tpl';
$valid_actions = array('firstsave','save','modify','create','delete','kill');

if( $auth->group() != 'maintainer' && $auth->group() != 'admin') {
   array_push($errors, _("Error: You don't have Permissions to access this Menu"));
}

require_once('admin/include/menu.php');

function fill_form_for_modify( &$form, &$ldap_object ) {
  if (is_array($ldap_object['cn'])) $cn = $ldap_object['cn'][0];
  else $cn = $ldap_object['cn'];
  $form->entries['cn']['value'] = $cn;

  if (is_array($ldap_object['cyrus-userquota'])) $userquota = $ldap_object['cyrus-userquota'][0];
  else $userquota = $ldap_object['cyrus-userquota'];
  $form->entries['cyrus-userquota']['value'] = $userquota;

  if (is_array($ldap_object['kolabHomeServer'])) $kolabhomeserver = $ldap_object['kolabHomeServer'][0];
  $form->entries['kolabhomeserver']['value'] = $kolabhomeserver;

  $form->entries['action']['value'] = 'save';
  //debug("got userquota=$userquota<br/>cn=$cn<br/>");
  $aclcount = 0;
  //var_dump( $ldap_object['acl'] );
  foreach( $ldap_object['acl'] as $key => $acl ) {
	if( $key === 'count' ) continue;
	list($u, $p ) = split( ' ', $acl, 2 );
	if( !strncmp( "group:", $u, 6 ) ) $u = substr( $u, 6 );
	//debug( "u=$u, p=$p" );
	$form->entries['acl_'.$aclcount] = array( 'name' => _('Permission for UID/email/GID'),
											  'type' => 'aclselect',
											  'user' => $u,
											  'perm' => $p
											  );
	$aclcount++;
  }
  $form->entries['acl_'.$aclcount] = array( 'name' => _('Permission for UID/email/GID'),
											'type' => 'aclselect',
											'user' => '',
											'perm' => 'all'
											);  
}

// Check uid/gid and perm and massage into cyrus ACL
function process_acl( $uid, $perm )
{
  global $ldap;
  global $errors;
  debug("process_acl( $uid, $perm )");
  if( $uid == 'anyone' || $uid == 'anonymous' ) {
	// Special users allowed
	return "$uid $perm";
  }
  $res = $ldap->search( $_SESSION['base_dn'], '(&(|(uid='.$ldap->escape($uid).')(mail='.$ldap->escape($uid).')(alias='.$ldap->escape($uid).'))(objectClass=kolabInetOrgPerson))', 
						array('dn', 'mail' ) );
  if( $ldap->count($res) == 1 ) {
	// Ok, we have a regular user
	$entries = $ldap->getEntries();
	$mail = $entries[0]['mail'][0];
	$ldap->freeSearchResult();
	return "$mail $perm";
  }
  
  $regs = array();
  if( ereg('(.*)@(.*)', $uid, $regs ) ) {
	$cn = $regs[1];
	$res = $ldap->search( $_SESSION['base_dn'], '(&(cn='.$ldap->escape($cn).')(objectClass=kolabGroupOfNames))', 
						  array('dn') );
	if( $ldap->count($res) == 1 ) {
	  $objects = $ldap->getEntries();
	  $dcs = array_filter( split(',', $objects[0]['dn']), create_function( '$str', 'return !strncmp( "dc=", $str, 3 );') );
	  $dcs = array_map( create_function( '$str', 'return substr($str,3);'), $dcs );
	  $domain = join( '.', $dcs );
	  if( $domain == $regs[2] ) {
		// All OK, we have a group
		return "group:$uid $perm";
	  }
	}
  }
  $errors[] = _("No UID or GID $uid");
  return false;
}

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';
$heading = '';

/**** Form/data handling ***/
if (!empty($_REQUEST['action']) && 
    in_array($_REQUEST['action'],$valid_actions)) $action = trim($_REQUEST['action']);
else array_push($errors, _("Error: need valid action to proceed"));

$dn="";
if (!empty($_REQUEST['dn'])) $dn = trim($_REQUEST['dn']);

if (!$errors && $auth->group() != 'maintainer' && $auth->group() != 'admin') 
	 array_push($errors, _("Error: You don't have the required Permissions"));

$entries = array( 'cn' => array( 'name' => _('Folder Name'),
								 'validation' => 'notempty',
								 'comment' => _('Required') ),
				  'kolabhomeserver' => array( 'name' => _('Folder Location'),
										 'validation' => 'notempty',
										 'comment' => ($action=='create')?_('Required, non volatile'):_('Non volatile'),
										 'value' => $_SESSION['fqdnhostname'] ),
				  'cyrus-userquota' => array( 'name' => _('Quota Limit'),
										'comment' => _('MBytes (empty for unlimited)') ),
				  'acl_0' => array( 'name' => _('Permission for UID/GID'),
									'type' => 'aclselect',
									'user' => 'anyone',
									'perm' => 'all' ));


$entries['action'] = array( 'name' => 'action',
							'type' => 'hidden' );

if( $action == 'modify' || $action == 'delete' || $action == 'kill' ) {
  if( $_REQUEST['dn'] ) {
	$dn = $_REQUEST['dn'];
  } else {  
	array_push($errors, _("Error: DN required for $action operation"));
  }
}

$form =& new KolabForm( 'vcard', 'createaddr.tpl', $entries );

if( !$errors ) {
  switch( $action ) {
  case 'create':
	$form->entries['action']['value'] = 'firstsave';
	$heading = _('Add Shared Folder');
	$content = $form->outputForm();
	break;
  case 'firstsave':
  case 'save':	
	if( $form->isSubmitted() ) {
	  if( !$form->validate() ) {
		$form->setValues();
		$content = $form->outputForm();
	  } else {
		$sf_root = $_SESSION['base_dn'];   
		$ldap_object = array('objectClass' => 'kolabSharedFolder');
		// OK, we need to get the name down to lowercase ascii only
		// we handle a few common cases here
		// Really cheesy, but strtolower is latin1 only :-(
		$cn = trim($_POST['cn']);
		debug("cn=$cn");
		$cn = strtolower(utf8_decode($cn));
		debug("cn=$cn");
		$cn = str_replace( utf8_decode('æ'), 'ae', $cn );
		$cn = str_replace( utf8_decode('ø'), 'oe', $cn );
		$cn = str_replace( utf8_decode('å'), 'aa', $cn );
		$cn = str_replace( utf8_decode('ä'), 'ae', $cn );
		$cn = str_replace( utf8_decode('ö'), 'oe', $cn );
		$cn = str_replace( utf8_decode('ü'), 'ue',  $cn );
		$cn = str_replace( utf8_decode('ß'), 'ss', $cn );
		debug("cn=$cn");
		$ldap_object['cn'] = utf8_encode($cn);
		foreach ( array( 'cyrus-userquota') as $attr) {
		  $count = 0;
		  $key = $attr;
		  $args = array();
		  while (!empty($_POST[$key])) {
			$args[$count] = trim($_POST[$key]);
			$count++;
			$key = $attr."_".$count;
		  }
		  if ($count > 0) $ldap_object[$attr] = $args;
		  else if (!empty($_POST[$key])) $ldap_object[$attr] = $_POST[$key];
		}
		if( $ldap_object['cyrus-userquota'] == '' ) unset($ldap_object['cyrus-userquota']);
		$aclcount = 0;
		while( isset($_POST['user_acl_'.$aclcount] )) {
		  if( !empty( $_POST['user_acl_'.$aclcount] ) ) {
			$acl = process_acl( $_POST['user_acl_'.$aclcount], $_POST['perm_acl_'.$aclcount] );
			if( $acl ) {
			  $ldap_object['acl'][] = $acl;			  
			} else {
			  break;
			}
		  }
		  $aclcount++;
		}

		if ($action == "save") {
		  if (!$errors) {
			if (!empty($ldap_object['cn'])) $newdn = "cn=".$ldap_object['cn'].",".$sf_root;
			else $newdn = $dn;
			if (strcmp($dn,$newdn) != 0) {
			  if (($result=ldap_read($ldap->connection,$dn,"(objectclass=*)")) &&
				  ($entry=ldap_first_entry($ldap->connection,$result)) &&
				  ($oldattrs=ldap_get_attributes($ldap->connection,$entry))) {
				if (!ldap_add($ldap->connection,$newdn, $ldap_object) 
					|| !ldap_delete($ldap->connection,$dn)) {
				  array_push($errors, _("LDAP Error: could not rename $dn to $newdn: ")
							 .ldap_error($ldap->connection));
				} else {
				  $messages[] = _('Shared folder updated');
				}
				$dn = $newdn;
			  } else array_push($errors,_("LDAP Error: could not read $dn: ")
								.ldap_error($ldap->connection));
			} else {
			  if (!ldap_modify($ldap->connection, $dn, $ldap_object))
				array_push($errors, _("LDAP Error: could not modify object $dn: ")
						   .ldap_error($ldap->connection)); 
			  else $messages[] = _('Shared folder updated');
			}
		  } 
		} else {
		  if (!$errors) {
			$dn = "cn=".$ldap_object['cn'].",".$sf_root;
			$ldap_object['kolabHomeServer'] = trim($_POST['kolabhomeserver']);
			if ($dn && !ldap_add($ldap->connection, $dn, $ldap_object)) 
			  array_push($errors, _("LDAP Error: could not add object $dn: ")
						 .ldap_error($ldap->connection));
			else $messages[] = _("Shared folder '$cn' added");
		  }
		  if ($errors) {
			//print("<div class=\"maintitle\"> Create New Address Book Entry </div>\n");
			$form->entries['action']['value'] = 'create';
			break;
		  }
		}
		$form->entries['action']['value'] = 'save';
		$form->entries['dn'] = array( 'type' => 'hidden', 'value' => $dn );
		$form->entries['cn']['attrs'] = 'readonly';
		$form->entries['kolabhomeserver']['attrs'] = 'readonly';
		$heading = _('Modify Shared Folder');
		$ldap_object = $ldap->read( $dn );
		if( $ldap_object ) {
		  fill_form_for_modify( $form, $ldap_object );
		}
		$content = $form->outputForm();		
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
	  $form->entries['kolabhomeserver']['attrs'] = 'readonly';
	  $heading = _('Modify Shared Folder');
	  $content = $form->outputForm();
	} else {
	  array_push($errors, _("Error: No results returned for DN $dn"));
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
	  $heading = _('Delete Shared Folder');
	  $content = $form->outputForm();
	} else {
	  array_push($errors, _("Error: No results returned for DN $dn"));
	}
	break;
  case 'kill':
	if (!$errors) {
	  if( $ldap->deleteSharedFolder($dn) ) {
		$messages[] = _('Shared folder ').$_REQUEST['cn']._(' marked for deletion');
		$heading = _('Entry Deleted');
		$contenttemplate = 'sfdeleted.tpl';
	  } else {
		array_push($errors, _("LDAP Error: Could not mark $dn for deletion: ").ldap_error($link));		
	  }
	}
	break;
  }
}

/**** Insert into template and output ***/
$smarty =& new MySmarty();
$smarty->assign( 'errors', array_merge($errors,$form->errors) );
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
