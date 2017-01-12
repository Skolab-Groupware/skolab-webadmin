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

require_once 'admin/include/mysmarty.php';
require_once 'admin/include/headers.php';
require_once 'admin/include/authenticate.php';
require_once 'admin/include/sieveutils.class.php';

// Funny multiline string escaping in Sieve
function dotstuff( $str ) {
  return str_replace( "\n.", "\n..", $str );
}

function undotstuff( $str ) {
  return str_replace( "\n..", "\n.", $str );
}

$errors = array();
if( (@include_once 'admin/include/Sieve.php' ) === false ) {
  $errors[] = _('Net/Sieve.php is missing. Without that, filter settings are not available');
  $errors[] = _("Suggest your system administrator to run \"$kolab_prefix/bin/pear install http://pear.php.net/get/Net_Sieve\" on the server");
}

/**** Authentication etc. ***/
$sidx = 'user';

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Sieve handling ***/
// this is the name KDE client stores - so we can also change the setting from KDE client
$scriptname = "kolab-deliver.siv";
if( !$errors ) {
  $obj = $ldap->read( $auth->dn() );
  $sieve =& new Net_Sieve( $auth->uid(), $auth->password(), $obj['kolabHomeServer'][0] );
  
  // Update sieve script on server in case we have submit data
  if( $_REQUEST['submit'] ) {
	$inbox  = trim($_REQUEST['inbox']);
	$active = isset($_REQUEST['active']);

	  $script = 
		"require \"fileinto\";\r\nif header :contains [\"X-Kolab-Scheduling-Message\"] [\"FALSE\"] {\r\nfileinto \"INBOX/$inbox\";\r\n}\r\n";

	  if( PEAR::isError( $res = $sieve->installScript( $scriptname, $script, $active ) ) ) {
		$errors[] = $res->getMessage();
		$errors[] = _('Script was:');
		$errors[] = '<pre>'.MySmarty::htmlentities($script).'</pre>';
	  }
	  if( !$active && $sieve->getActive() === $scriptname ) {
		$sieve->setActive( '' );
	  }

	  if( !$errors ) {
		if( $active ) $messages[] = sprintf( _("Delivery to '%s' successfully activated"), $inbox );
		else $messages[] =  sprintf( _("Delivery to '%s' successfully deactivated"), $inbox );
	  }	
  }

  $scripts = $sieve->listScripts();
  $inbox = false;
  if( in_array( $scriptname, $scripts ) ) {
	// Fetch script data from server
	$script = $sieve->getScript($scriptname);
	$inbox = SieveUtils::getDeliverFolder( $script );
  }
  if( $inbox === false ) $inbox = 'Inbox';
  $active = ( $sieve->getActive() === $scriptname );  
}

/**** Insert into template and output ***/
$smarty = new MySmarty();
$smarty->assign( 'errors', $errors );
$smarty->assign( 'messages', $messages );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', 
				 array_key_exists('submenu', 
								  $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'active', $active );
$smarty->assign( 'inbox', $inbox );
$smarty->assign( 'maincontent', 'deliver.tpl' );
$smarty->display('page.tpl');

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
