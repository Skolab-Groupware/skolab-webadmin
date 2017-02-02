<?php
/*
 *  Copyright (c) 2004,2005 Klarälvdalens Datakonsult AB
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

class KolabLDAP {
  function KolabLDAP() {
    $this->is_bound = false;
    $this->bind_dn = false;
    $this->search_result = false;
	$this->cached_domains = false;
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

  // Taken from PEAR_Net_LDAP2
  public function dn_escape($val)
  {
	  // Escaping of filter meta characters
	  $val = str_replace('\\', '\\\\', $val);
	  $val = str_replace(',',    '\,', $val);
	  $val = str_replace('+',    '\+', $val);
	  $val = str_replace('"',    '\"', $val);
	  $val = str_replace('<',    '\<', $val);
	  $val = str_replace('>',    '\>', $val);
	  $val = str_replace(';',    '\;', $val);
	  $val = str_replace('#',    '\#', $val);
	  $val = str_replace('=',    '\=', $val);

	  // ASCII < 32 escaping                                                                                                                                        
	  $val = KolabLDAP::asc2hex32($val);

	  // Convert all leading and trailing spaces to sequences of \20.
	  if (preg_match('/^(\s*)(.+?)(\s*)$/', $val, $matches)) {
		$val = $matches[2];
		for ($i = 0; $i < strlen($matches[1]); $i++) {
		  $val = '\20'.$val;
		}
		for ($i = 0; $i < strlen($matches[3]); $i++) {
		  $val = $val.'\20';
		}
	  }

	  if (null === $val) $val = '\0';  // apply escaped "null" if string is empty

	  return $val;
  }

  // Taken from PEAR_Net_LDAP2
  public function asc2hex32($string)
  {
	for ($i = 0; $i < strlen($string); $i++) {
	  $char = substr($string, $i, 1);
	  if (ord($char) < 32) {
		$hex = dechex(ord($char));
		if (strlen($hex) == 1) $hex = '0'.$hex;
		$string = str_replace($char, '\\'.$hex, $string);
	  }
	}
	return $string;
  }


  // Taken from PEAR_Net_LDAP2
  function unescape_dn_value($val)
  {
	  // strip slashes from special chars
	  $val = str_replace('\\\\', '\\', $val);
	  $val = str_replace('\,',    ',', $val);
	  $val = str_replace('\+',    '+', $val);
	  $val = str_replace('\"',    '"', $val);
	  $val = str_replace('\<',    '<', $val);
	  $val = str_replace('\>',    '>', $val);
	  $val = str_replace('\;',    ';', $val);
	  $val = str_replace('\#',    '#', $val);
	  $val = str_replace('\=',    '=', $val);

	  return KolabLDAP::hex2asc($val);
  }

  // Taken from PEAR_Net_LDAP2
  function hex2asc($string)
  {
	$string = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $string);
	return $string;
  }

  function bind( $dn = false , $pw = '' ) {
    if( !$dn ) {
      // Default ldap auth
      $dn = $_SESSION['php_dn'];
      $pw = $_SESSION['php_pw'];
    }
    $this->is_bound = @ldap_bind( $this->connection, $dn, $pw );
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
      return false;
    }
    $entry = ldap_first_entry($this->connection,$result);
    if( !$entry ) {
      ldap_free_result($result);
      return false;
    }
    $ldap_object = ldap_get_attributes($this->connection,$entry);
    ldap_free_result($result);
    return $ldap_object;
  }

  function add( $dn, $attr ) {
      return ldap_add($this->connection, $dn, $attr);
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
		$errors[] = sprintf( _("No such object %s"), $dn );
	  }
	} else {
	  $errors[] = sprintf( _("LDAP Error searching for DN %s: %s"), $dn, ldap_error($this->connection));
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
      echo sprintf( _("Error searching for DN for UID=%s"), $uid );
    }
    return false;
  }

  function mailForDn( $dn ) {
    global $errors;
    $res = ldap_read( $this->connection, $dn, '(objectclass=*)', array( 'mail' ) );
    if( $res ) {
      $entries = ldap_get_entries( $this->connection, $res );
      ldap_free_result( $res );
      if( $entries[0]['count'] == 1 ) {
        return $entries[0]['mail'][0]; 
      } else {
        $errors[] = sprintf( _("No such object %s"), $dn);
      } 
    } else {
      $errors[] = sprintf(_("LDAP Error searching for DN %s: %s"), $dn, ldap_error($this->connection));
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
        $errors[] = sprintf( _("No such object %s"), $dn);
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
      $errors[] = sprintf( _("Error searching for DN for mail or alias %s: %s"), $mail, ldap_error($this->connection));
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
      $result = $this->search( 'cn=domain-maintainer,cn=internal,'.$_SESSION['base_dn'], $filter);	  
      if (ldap_count_entries($this->connection, $result) > 0) $group = 'domain-maintainer';	  
      $result = $this->search( 'cn=maintainer,cn=internal,'.$_SESSION['base_dn'], $filter);
      if (ldap_count_entries($this->connection, $result) > 0) $group = 'maintainer';
      $result = $this->search( 'cn=admin,cn=internal,'.$_SESSION["base_dn"], $filter);
      if (ldap_count_entries($this->connection, $result) > 0) $group = 'admin';
      if ($result) $this->freeSearchResult();
    }
	debug("groupForUid( $uid) = $group");
    return $group;
  }

  function domainsForMaintainerDn( $dn ) {
    if( !$this->is_bound ) {
      return false;
    }
	debug("\tdn=$dn");
	$domains = array();
	$filter = '(member='.$this->escape($dn).')';
	debug("filter:$filter");
	$result = $this->search( 'cn=domains,cn=internal,'.$_SESSION['base_dn'], $filter);	  
	$entries = $this->getEntries();
	unset($entries['count']);
	if( count($entries) > 0) {
	  foreach( $entries as $val ) {
		debug("\tdomain=".$val['cn'][0]);
		$domains[] = $val['cn'][0];
	  }
	}
	sort($domains);
	return $domains;
  }

  function domainsForMaintainerUid( $uid ) {
	debug("domainsForMaintainer( $uid ):");
    $dn = $this->dnForUid($uid);
	if($dn) {
	  return $this->domainsForMaintainerDn($dn);
	}
	return false;
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
				 .ldap_error($this->connection) );	
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
		if( !isset($entries[$i]) || is_null( $entries[$i] ) ) continue;
		if( KolabLDAP::unescape_dn_value($entries[$i]['dn']) == KolabLDAP::unescape_dn_value($excludedn) ) continue;	   
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

  function domains( $reload = false ) {
	if( $reload || !$this->cached_domains ) {
	  $kolab_obj = $this->read( 'k=kolab,'.$_SESSION['base_dn'] );
	  if( !$kolab_obj ) return false;
	  unset($kolab_obj['postfix-mydestination']['count']);
	  sort($kolab_obj['postfix-mydestination']);
	  $this->cached_domains = $kolab_obj['postfix-mydestination'];
	  debug("loading domains");
	}
	debug("ldap->domains() returns ".join(", ", $this->cached_domains));
	return $this->cached_domains;
  }

  function addToDomainGroups( $member, $domains ) {
	if (empty($domains)) {
	  return true;
	}
	foreach( $domains as $domain ) {
	  $domgrpdn = 'cn='.$this->dn_escape($domain).',cn=domains,cn=internal,'.$_SESSION['base_dn'];
	  $dom_obj = $this->read( $domgrpdn );	  
	  if( !$dom_obj ) {
		debug("+Adding group $domgrpdn with member $member");
		if( !ldap_add($this->connection, $domgrpdn, 
					  array( 'objectClass' => array('top', 'kolabGroupOfNames'),
							 'cn' => $domain,
							 'member' => $member ) ) ) {
		  debug("Error adding domain group: ".ldap_error($this->connection));
		  return false;
		}
	  } else {
		if( !in_array( $member, $dom_obj['member'] ) ) {
		  debug("+Adding member $member to $domgrpdn");
		  if( !ldap_mod_add( $this->connection, $domgrpdn, array( 'member' => $member ) ) ) {
			debug("Error adding $member to domain $domgrpdn: ".ldap_error($this->connection));
			return false;
		  }
		}
	  }
	}
	return true;
  }

  function removeFromDomainGroups( $member, $domains ) {
	if (empty($domains)) {
	  return true;
	}
	foreach( $domains as $domain ) {
	  $domgrpdn = 'cn='.$this->dn_escape($domain).',cn=domains,cn=internal,'.$_SESSION['base_dn'];
	  $dom_obj = $this->read( $domgrpdn );
	  if( $dom_obj ) {
		if( count( $dom_obj['member'] == 1 ) ) {
		  debug("-Removing group $domgrpdn");
		  if( !ldap_delete( $this->connection, $domgrpdn ) ) {
			debug("Error deleting domain group $domgrpdn: ".ldap_error($this->connection));
			return false;			
		  }
		} else {
		  debug("-Removing member $member from group $domgrpdn");
		  if( !ldap_mod_del( $this->connection, $domgrpdn, array( 'member' => $member ) ) ) {
			debug("Error deleting $member from domain $domgrpdn: ".ldap_error($this->connection));
			return false;
		  }  
		}
	  }
	}	
	return true;
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

  /**
   * Get given (first) name
   * Just return it if available, otherwise calculate from cn and sn
   * (assumes that "$cn" is "$gn $sn")
   *
   * @param string  $cn The common name
   * @param string  $sn The last name
   * @param string  $gn The given name
   *
   * @return string The extracted given (first) name
   */
  function getGivenName($cn, $sn, $gn = '')
  {
	if( $gn == '' ) {
		return substr($cn, 0, strlen($cn) - strlen($sn) - 1);
	} else {
		return $gn;
	}
  }

  var $connection;
  var $is_bound;
  var $bind_dn;
  var $search_result;
  var $cached_domains;
};
