<?php
/*
 (c) 2004 Klarälvdalens Datakonsult AB

 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once 'admin/include/mysmarty.php';
require_once 'admin/include/headers.php';
require_once('admin/include/locale.php');
require_once 'admin/include/authenticate.php';
require_once 'admin/include/sieveutils.class.php';

$errors = array();
if( (@include_once 'admin/include/Sieve.php' ) === false ) {
  $errors[] = _('Net/Sieve.php is missing. Without that, vacation settings are not available');
  $errors[] = _("Suggest your system administrator to run \"$kolab_prefix/bin/pear install http://pear.php.net/get/Net_Sieve\" on the server");
}

/**** Authentication etc. ***/
$sidx = 'user';

require_once('admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Sieve handling ***/
$scriptname = 'kolab-vacation.siv';
if( !$errors ) {
  $obj = $ldap->read( $auth->dn() );
  $sieve =& new Net_Sieve( $auth->uid(), $auth->password(), $obj['kolabHomeServer'][0] );
  //$sieve->setDebug(true);

  // Update sieve script on server in case we have submit data
  if( $_REQUEST['submit'] ) {
	$addresses = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $_REQUEST['addresses'] ) ), 'strlen') );
	$maildomain = trim( $_REQUEST['maildomain'] );
	$reacttospam = isset( $_REQUEST['reacttospam'] );
	$script = 
	  "require \"vacation\";\r\n\r\n".
	  (!empty($maildomain)?"if not address :domain :contains \"From\" \"".$maildomain."\" { keep; stop; }\r\n":"").
	  ($reacttospam?"if header :contains \"X-Spam-Flag\" \"YES\" { keep; stop; }\r\n":"").
	  "vacation :addresses [ \"".join('", "', $addresses )."\" ] :days ".
	  $_REQUEST['days']." text:\r\n".
	  SieveUtils::dotstuff(trim($_REQUEST['text']))."\r\n.\r\n;\r\n\r\n";
	$active = isset($_REQUEST['active']);

	if( PEAR::isError( $res = $sieve->installScript( $scriptname, $script, $active ) ) ) {
	  $errors[] = $res->getMessage();
	  $errors[] = 'Script was:';
	  $errors[] = '<pre>'.MySmarty::htmlentities($script).'</pre>';
	}
	if( !$active && $sieve->getActive() === $scriptname ) {
	  $sieve->setActive( '' );
	}

	if( !$errors ) {
	  if( $active ) $messages[] = _('Vacation message successfully activated');
	  else $messages[] = _('Vacation message successfully deactivated');
	}
  }

  $addresses = $days = $text = false;
  $scripts = $sieve->listScripts();
  if( in_array( $scriptname, $scripts ) ) {
	$script = $sieve->getScript($scriptname);
	$maildomain = SieveUtils::getMailDomain( $script );
	$reacttospam = SieveUtils::getReactToSpam( $script );
	debug("reacttospam=".($reacttospam?"true":"false"));
	$addresses = SieveUtils::getVacationAddresses( $script );
	$days = SieveUtils::getVacationDays( $script );
	$text = SieveUtils::getVacationText( $script );
  } else $reacttospam = true;
  if( $addresses === false ) {
	$object = $ldap->read( $auth->dn() );
	$addresses = array_merge( $object['mail'], $object['alias'] );
  }
  if( $days === false ) $days = 7;
  if( $text === false ) {
	$date = strftime(_('%x'));
	$text = sprintf(
					_("I am out of office until %s.\r\n").
					_("In urgent cases, please contact Mrs. <vacation replacement>\r\n\r\n").
					_("email: <email address of vacation replacement>\r\n").
					_("phone: +49 711 1111 11\r\n").
					_("fax.:  +49 711 1111 12\r\n\r\n").
					_("Yours sincerely,\r\n").
					_("-- \r\n").
					_("<enter your name and email address here>"),
					$date);
  }
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
$smarty->assign( 'text', $text );
$smarty->assign( 'addresses', $addresses );
$smarty->assign( 'maildomain', $maildomain );
$smarty->assign( 'reacttospam', $reacttospam );
$smarty->assign( 'days', $days );
$smarty->assign( 'inbox', $inbox );
$smarty->assign( 'maincontent', 'vacation.tpl' );
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
