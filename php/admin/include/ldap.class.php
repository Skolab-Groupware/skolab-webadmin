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

require_once('mysmarty.php');
require_once('session_vars.php');
require_once('debug.php');

/* We dont have any better place to put this right now... */
function str_rand($length = 8, $seeds = 'abcdefghijklmnopqrstuvwxyz0123456789') {
     $str = '';
     $seeds_count = strlen($seeds);
  
     // Seed
     //list($usec, $sec) = explode(' ', microtime());
     //$seed = (float) $sec + ((float) $usec * 100000);
     //mt_srand($seed);
  
     // Generate
     for ($i = 0; $length > $i; $i++) {
         $str .= $seeds{mt_rand(0, $seeds_count - 1)};
     }
  
     return $str;
 }

class KolabLDAP {
  function KolabLDAP() {
    $this->is_bound = false;
    $this->bind_dn = false;
    $this->search_result = false;
	// Always connect to master server
    $this->connection=ldap_connect($_SESSION['ldap_master_uri']);
	if (ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3)) {
	  // Good, we really neeed v3!
	} else {
	  echo _("Error setting LDAP protocol to v3. Please contact your system administrator");
	  return false;
	}
  }

  function close() {
    if( $this->search_result ) {
      ldap_free_result( $this->search_result );
      $this->search_result;
    }
    $rc = ldap_close( $this->connection );
    $this->connection = $this->is_bound = false;
  }

  function error() {
    return ldap_error( $this->connection );
  }

  function escape( $str ) {
    /*
      From RFC-2254:

      If a value should contain any of the following characters

      Character       ASCII value
      ---------------------------
      *               0x2a
      (               0x28
      )               0x29
      \               0x5c
      NUL             0x00

     the character must be encoded as the backslash '\' character (ASCII
     0x5c) followed by the two hexadecimal digits representing the ASCII
     value of the encoded character. The case of the two hexadecimal
     digits is not significant.
     */
    $str = str_replace( '\\', '\\5c', $str );
    $str = str_replace( '*',  '\\2a', $str );
    $str = str_replace( '(',  '\\28', $str );
    $str = str_replace( ')',  '\\29', $str );
    $str = str_replace( '\0', '\\00', $str );
    return $str;
  }

  function dn_escape( $str ) {
	/*
	 DN component escaping as described in RFC-2253
	 */
	$str = str_replace( '\\', '\\\\', $str );
	$str = str_replace( ',', '\\,', $str );
	$str = str_replace( '+', '\\,', $str );
	$str = str_replace( '<', '\\<', $str );
	$str = str_replace( '>', '\\>', $str );
	$str = str_replace( ';', '\\;', $str );
	if( $str[0] == '#' ) $str = '\\'.$str;
	// PENDING(steffen): Escape leading/trailing spaces
	return $str;
  }
  
  function bind( $dn = false , $pw = '' ) {
    if( !$dn ) {
      // Default ldap auth
      $dn = $_SESSION['php_dn'];
      $pw = $_SESSION['php_pw'];
    }
    $this->is_bound = ldap_bind( $this->connection, $dn, $pw );
    if( $this->is_bound ) {
      $this->bind_dn = $dn;
    } else {
      $this->bind_dn = false;
    }
    return $this->is_bound;
  }

  function read( $dn ) {
    $result = ldap_read($this->connection, $dn, "(objectclass=*)");
    if( !$result ) {
      print $this->error();
      return false;
    }
    $entry = ldap_first_entry($this->connection,$result);
    if( !$entry ) {
      print $this->error();
      ldap_free_result($result);
      return false;
    }
    $ldap_object = ldap_get_attributes($this->connection,$entry);
    if( !$ldap_object ) {
      print $this->error();
    }
    ldap_free_result($result);
    return $ldap_object;
  }

  function search( $base, $filter, $attrs = false ) {
    $this->freeSearchResult();
    if( $attrs ) {
	  $this->search_result = ldap_search( $this->connection, $base, $filter, $attrs );
	} else {
	  $this->search_result = ldap_search( $this->connection, $base, $filter );	  
	}
    return $this->search_result;
  }

  function count( $result ) {
	return ldap_count_entries($this->connection, $result);
  }

  function firstEntry() {
    return ldap_first_entry( $this->connection, $this->search_result );
  }

  function getEntries() {
	return ldap_get_entries( $this->connection, $this->search_result );
  }

  function freeSearchResult() {
    if( $this->search_result ) {
      ldap_free_result( $this->search_result );
      $this->search_result = false;
    }
  }

  function uidForDn( $dn ) {
	global $errors;
    $res = ldap_read( $this->connection, $dn,
					  '(objectclass=*)',
					  array( 'uid' ) );
	if( $res ) {
	  $entries = ldap_get_entries( $this->connection, $res );
	  ldap_free_result( $res );
	  if( $entries['count'] == 1 ) {
		return $entries[0]['uid'][0];
	  } else {
		$errors[] = sprintf(_("No such object %s"), $dn);
	  }
	} else {
	  $errors[] = sprintf(_("LDAP Error searching for DN %s: %s"), $dn, ldap_error($this->connection) );
	}
    return false;	
  }

  function dnForUid( $uid ) {
    if( $this->search( $_SESSION['base_dn'],
		       '(&(objectclass=kolabInetOrgPerson)(uid='.$this->escape($uid).'))' ) ) {
      $entry = $this->firstEntry();
      if( $entry ) {
	return ldap_get_dn( $this->connection, $entry );
      }
    } else {
      echo sprintf( _("Error searching for DN for UID=%s"), $uid);
    }
    return false;
  }

  function mailForDn( $dn ) {
    global $errors;
    $res = ldap_read( $this->connection, $dn, '(objectclass=*)', array( 'mail' ) );
    if( $res ) {
      $entries = ldap_get_entries( $this->connection, $res );
      ldap_free_result( $res );
      if( $entries['count'] == 1 ) {
        return $entries[0]['mail'][0]; 
      } else {
        $errors[] = sprintf(_("No such object %s"), $dn); 
      } 
    } else {
      $errors[] = sprintf( _("LDAP Error searching for DN %s: %s"), $dn, ldap_error($this->connection) );
    }
    return false;
  }

  function dnForMail( $mail ) {
    if( $this->search( $_SESSION['base_dn'],
                       '(&(objectclass=kolabInetOrgPerson)(mail='.$this->escape($mail).'))' ) ) {
      $entry = $this->firstEntry();
      if( $entry ) {
        return ldap_get_dn( $this->connection, $entry );
      }
    } else {
      echo sprintf( _("Error searching for DN for Mail=%s"), $mail);
    }
    return false;
  }

  function aliasForDn( $dn ) {
    global $errors;
    $res = ldap_read( $this->connection, $dn, '(objectclass=*)', array( 'alias' ) );
    if( $res ) {
      $entries = ldap_get_entries( $this->connection, $res );
      ldap_free_result( $res );
      if( $entries['count'] == 1 ) {
        return $entries[0]['alias'][0];
      } else {
        $errors[] = _("No such object $dn");
      }
    } else {
      $errors[] = sprintf( _("LDAP Error searching for DN %s: %s"), $dn, ldap_error($this->connection) );
    }
    return false;
  }

  function dnForAlias( $mail ) {
    if( $this->search( $_SESSION['base_dn'],
                       '(&(objectclass=kolabInetOrgPerson)(alias='.$this->escape($mail).'))' ) ) {
      $entry = $this->firstEntry();
      if( $entry ) {
        return ldap_get_dn( $this->connection, $entry );
      }
    } else {
      $errors[] = sprintf( _("Error searching for DN for alias=%s: %s"), $mail, ldap_error($this->connection));
    }
    return false;
  }

  function dnForMailOrAlias( $mail ) {
    if( $this->search( $_SESSION['base_dn'],
                       '(&(objectclass=kolabInetOrgPerson)(|(mail='.$this->escape($mail).')(alias='.$this->escape($mail).')))' ) ) {
      $entry = $this->firstEntry();
      if( $entry ) {
        return ldap_get_dn( $this->connection, $entry );
      }
    } else {
      $errors[] = sprintf(_("Error searching for DN for mail_or_alias=%s: %s"), $mail, ldap_error($this->connection));
    }
    return false;
  }

  function groupForUid( $uid ) {
    $group = false;
    if( !$this->is_bound ) {
      return false;
    }
    $dn = $this->dnForUid($uid);
    if ($dn) {
      $group = 'user';
      $filter = '(member='.$this->escape($dn).')';
      $result = $this->search( 'cn=maintainer,cn=internal,'.$_SESSION['base_dn'], $filter);
      if (ldap_count_entries($this->connection, $result) > 0) $group = 'maintainer';
      $result = $this->search( 'cn=admin,cn=internal,'.$_SESSION["base_dn"], $filter);
      if (ldap_count_entries($this->connection, $result) > 0) $group = 'admin';
      if ($result) $this->freeSearchResult();
    }
    return $group;
  }

  // Get members of a group as an array of DNs
  function groupMembers( $base, $group ) {
    global $errors;
    $privmembers = array();
    $mybase = 'cn='.$group.','.$base;
    $filter = '(objectClass=kolabGroupOfNames)';
    $res = ldap_search( $this->connection, $mybase, $filter, array('member') );
    if( !$res ) {
      array_push($errors, _("LDAP Error: Can't read maintainers group: ")
				 .ldap_error($conn) );
      return array();
    }
    $entries = ldap_get_entries( $this->connection, $res );
    foreach($entries as $key=>$val) {
      if( $key === 'count' ) {
		// Do nothing
      } else if( is_array( $val ) && is_array($val['member']) ) {
		foreach( $val['member'] as $member ) {
		  $privmembers[$member] = true;
		}
      }
    }
    ldap_free_result( $res );
    return $privmembers;
  }

  // Count the number of occurences of an email address
  // in users' mail and alias attributes and in dist. lists.
  // This can be used to check for uniqueness etc.
  function countMail( $base, $mail , $excludedn=false ) {
	// First count users
        $filter = '(|(|(mail='.$this->escape($mail).')
                       (alias='.$this->escape($mail).')
                     )
                     (uid='.$this->escape($mail).')
                   )';
	$res = $this->search( $base, $filter, array( 'dn' ) );
	$count = 0;

	$entries = ldap_get_entries( $this->connection, $res );
	if( $excludedn ) {
	  for ( $i = 0; $i < count( $entries ); $i++ ) {
		if( is_null( $entries[$i] ) ) continue;
		if( $entries[$i]['dn'] == $excludedn ) continue;	   
		debug("found ".$entries[$i]['dn'] );
		$count++;
	  } 
	} else $count += $entries['count'];
	
	/* Distribution lists have a mail attr now too,
	   so it looks like we count them twice.
	   For some reason I've not seen any problems
	   with it though, so I dare not remove the code
	   below... /steffen
	*/

	// Now count dist. lists
	$cn = substr( $mail, 0, strpos( $mail, '@' ) );
	$filter = '(&(objectClass=kolabGroupOfNames)(cn='.$this->escape($cn).'))';
	$res = $this->search( $base, $filter, array( 'dn' ) );
	
	$entries = ldap_get_entries( $this->connection, $res );
	if( $excludedn ) {
	  for ( $i = 0; $i < count( $entries ); $i++ ) {
		if( is_null( $entries[$i] ) ) continue;
		if( $entries[$i]['dn'] == $excludedn ) continue;
		debug("found ".$entries[$i]['dn'] );
		$count++;
	  } 
	} else $count += $entries['count'];
	
	debug("Got $count addresses");

	$this->freeSearchResult();
	return $count;
  }

  // Set deleflag on object, or if $delete_now is
  // true, just delete it
  function deleteObject( $dn, $delete_now = false ) {
	return $this->_doDeleteObject( $dn, $delete_now, true );
  }

  function deleteSharedFolder( $dn, $delete_now = false ) {
	return $this->_doDeleteObject( $dn, $delete_now, false );
  }

  function deleteGroupOfNames( $dn, $delete_now = false ) {
	return $this->_doDeleteObject( $dn, $delete_now, false );
  }

  // Private
  function _doDeleteObject( $dn, $delete_now = false, $nuke_password = false ) {
	if( $delete_now ) {
	  if( !ldap_delete( $this->connection, $dn ) ) {
		return false;
	  }
	} else {
	  // Look up hostnames in this setup
	  $kolab_obj = $this->read( 'k=kolab,'.$_SESSION['base_dn'] );
	  if( !$kolab_obj ) return false;
	  $delete_template = array();
	  $delete_template['kolabDeleteflag'] = $kolab_obj['kolabHost'];	  
	  unset($delete_template['kolabDeleteflag']['count']);
	  if( $nuke_password ) {
		// Write random garbage into passwd field to lock the user out
		$delete_template['userPassword'] = '{sha}'.base64_encode( pack('H*', 
																	   sha1( str_rand( 32 ) )));
	  }
	  if( !ldap_modify($this->connection,$dn,$delete_template) ) {
		return false;
	  }
	}
	return true;
  }

  var $connection;
  var $is_bound;
  var $bind_dn;
  var $search_result;
};

$ldap =& new KolabLDAP;

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
