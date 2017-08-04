<?php
/*
 (c) 2004 Klarälvdalens Datakonsult AB

 This program is Free Software under the GNU General Public License (>=v2).
 Read the file COPYING that comes with this packages for details.
*/

require_once 'skolab/admin/include/mysmarty.php';
require_once 'skolab/admin/include/headers.php';
require_once('skolab/admin/include/locale.php');
require_once 'skolab/admin/include/authenticate.php';
require_once 'Net/Sieve.php';
require_once 'Horde/String.php';
require_once 'skolab/admin/Sieve.php';
require_once 'skolab/admin/Sieve/Script.php';
require_once 'skolab/admin/Sieve/Segment.php';
require_once 'skolab/admin/Sieve/Segment/Delivery.php';
require_once 'skolab/admin/Sieve/Segment/Forward.php';
require_once 'skolab/admin/Sieve/Segment/Vacation.php';

/**** Authentication etc. ***/
$sidx = 'user';

require_once('skolab/admin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';

/**** Sieve handling ***/
  $obj = $ldap->read( $auth->dn() );
  $sieve =& new Net_Sieve( $auth->uid(), $auth->password(), $obj['kolabHomeServer'][0] );
  //$sieve->setDebug(true);

  if( $sieve->getError() ) {
    $errors[] = _('Error while connecting to Sieve service:');
    $errors[] = $sieve->getError();
  // Update sieve script on server in case we have submit data
  } else {
	try {
	  $handler = new KolabAdmin_Sieve($sieve);

	  if ($_REQUEST['submit']) {
		$handler->fetchVacationSegment()->setActive($_REQUEST['active']);
		$handler->fetchVacationSegment()->setDomain(trim($_REQUEST['maildomain']));
		$handler->fetchVacationSegment()->setReactToSpam(!isset($_REQUEST['reacttospam']));
		$handler->fetchVacationSegment()->setResendAfter($_REQUEST['days']);
		$handler->fetchVacationSegment()->setResponse(trim($_REQUEST['text']));

		$addresses = array_unique(array_filter(array_map('trim', preg_split('/\n/', $_REQUEST['addresses'])), 'strlen'));
		$handler->fetchVacationSegment()->setRecipientAddresses($addresses);

		$handler->store();
		if ($_REQUEST['active']) {
		  $messages[] = _('Vacation message successfully activated');
		} else {
		  $messages[] = _('Vacation message successfully deactivated');
		}
	  } else {
		$result = $handler->checkUnknownScript();
		if ($result) {
		  $errors[] = sprintf(_("Warning: You currently have a sieve script named %s active for your account."), $result);
		  $errors[] = _("Warning: This script will be overwritten without further warnings if you press \"Update\"!");
		}

		$addresses = $handler->fetchVacationSegment()->getRecipientAddresses();
		if (empty($addresses)) {
		  $object = $ldap->read($auth->dn());
		  $addresses = array_merge((array) $object['mail'], (array) $object['alias']);
		}
	  }
	} catch (Exception $e) {
	  $errors[] = $e->getMessage();
	  $errors[] = 'Script was:';
	  $errors[] = '<pre>' . $handler->getScript() . '</pre>';
	}
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
if (isset($handler)) {
    $smarty->assign( 'active', $handler->fetchVacationSegment()->isActive() );
    $smarty->assign( 'text', $handler->fetchVacationSegment()->getResponse() );
    $smarty->assign( 'addresses', $addresses );
    $smarty->assign( 'maildomain', $handler->fetchVacationSegment()->getDomain() );
    $smarty->assign( 'reacttospam', !$handler->fetchVacationSegment()->getReactToSpam() );
    $smarty->assign( 'days', $handler->fetchVacationSegment()->getResendAfter() );
}

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
