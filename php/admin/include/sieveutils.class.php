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

class SieveUtils {
  // Funny multiline string escaping in Sieve
  /*static*/ function dotstuff( $str ) {
    return str_replace( "\n.", "\n..", $str );
  }
  
  /*static*/ function undotstuff( $str ) {
    return str_replace( "\n..", "\n.", $str );
  }
  
  /*static*/ function getDeliverFolder( $script ) {
    $inbox = false;      
	if( preg_match("/fileinto \"INBOX\/(.*)\";/", $script, $regs ) ) {
	  $inbox = $regs[1];
	}
    return $inbox;
  }

  /*static*/ function getVacationAddresses( $script ) {
    $addresses = false;
    if( preg_match("/:addresses \\[([^\\]]*)\\]/s", $script, $regs ) ) {
      $tmp = split(',', $regs[1] );
      $addresses = array();
      foreach( $tmp as $a ) {
		if( ereg('^ *"(.*)" *$', $a, $regs ) ) $addresses[] = $regs[1];
		else $addresses[] = $a;
      }
    }
    return $addresses;
  }

  /*static*/ function getMailDomain( $script ) {
	$maildomain = false;
	if( preg_match( '/if not address :domain :contains "From" "(.*)" { keep; stop; }/i', $script, $regs ) ) {
	  $maildomain = $regs[1];
	}
	return $maildomain;
  }
  
  /*static*/ function getReactToSpam( $script ) {
	$spam = false;
	if( preg_match('/if header :contains "X-Spam-Flag" "YES" { keep; stop; }/i', $script ) ) {
	  $spam = true;
	}
	return $spam;
  }

  /*static*/ function getVacationDays( $script ) {
    $days = false;
    if( preg_match("/:days ([0-9]+)/s", $script, $regs ) ) {
      $days = $regs[1];
    }
    return $days;
  }

  /*static*/ function getVacationText( $script ) {
    $text = false;
    if( preg_match("/text:(.*\r\n)\\.\r\n/s", $script, $regs ) ) {
      $text = $regs[1];
      $text = str_replace( '\n', "\r\n", $text );
      $text = SieveUtils::undotstuff($text);
    }
    return $text;
  }

  /*static*/ function getForwardAddress( $script ) {
    $address = false;
    if( preg_match("/redirect \"([^\"]*)\"/s", $script, $regs ) ) {
      $address = $regs[1];
    }
    return $address;
  }

  /*static*/ function getKeepOnServer( $script ) {
    return ereg('"; keep;', $script, $regs );    
  }

  function createScript( $scriptname ) {
    // TODO
  }
};

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