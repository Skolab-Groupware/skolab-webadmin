<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *  Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
 *
 *    Originally written by
 *    Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
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

require_once 'Skolab/Admin/include/mysmarty.php';
require_once 'Skolab/Admin/include/headers.php';
require_once('Skolab/Admin/include/locale.php');
require_once 'Skolab/Admin/include/authenticate.php';
require_once 'Net/Sieve.php';
require_once 'Horde/String.php';
require_once 'Skolab/Admin/Sieve.php';
require_once 'Skolab/Admin/Sieve/Script.php';
require_once 'Skolab/Admin/Sieve/Segment.php';
require_once 'Skolab/Admin/Sieve/Segment/Delivery.php';
require_once 'Skolab/Admin/Sieve/Segment/Forward.php';
require_once 'Skolab/Admin/Sieve/Segment/Vacation.php';

// *** Authentication etc. ***
$sidx = 'user';

require_once('Skolab/Admin/include/menu.php');

// *** Submenu for current page ***
$menuitems[$sidx]['selected'] = 'selected';

// *** Sieve handling ***
	$obj = $ldap->read( $auth->dn() );
	$sieve = new Net_Sieve( $auth->uid(), $auth->password(), $obj['kolabHomeServer'][0] );

	if( $sieve->getError() ) {
		$errors[] = _('Error while connecting to Sieve service:');
		$errors[] = $sieve->getError();
		// Update sieve script on server in case we have submit data
} else {
	try {
		$handler = new SkolabAdmin_Sieve($sieve);

		if ($_REQUEST['submit']) {
			$handler->fetchForwardSegment()->setActive($_REQUEST['active']);
			$handler->fetchForwardSegment()->setForwardAddress($_REQUEST['address']);
			$handler->fetchForwardSegment()->setKeepOnServer($_REQUEST['keep']);
			$handler->store();

			if ($_REQUEST['active']) {
				$messages[] = sprintf(_("Forwarding to '%s' successfully activated"), $_REQUEST['address']);
			} else {
				$messages[] = sprintf(_("Forwarding to '%s' successfully deactivated"), $_REQUEST['address']);
			}

		} else {
			$result = $handler->checkUnknownScript();
			if ($result) {
				$errors[] = sprintf(_("Warning: You currently have a sieve script named %s active for your account."), $result);
				$errors[] = _("Warning: This script will be overwritten without further warnings if you press \"Update\"!");
			}
		}

	} catch (Exception $e) {
		$errors[] = $e->getMessage();
		$errors[] = 'Script was:';
		$errors[] = '<pre>' . $handler->getScript() . '</pre>';
	}
}

// *** Insert into template and output ***
$smarty = new MySmarty();
$smarty->assign( 'errors', $errors );
$smarty->assign( 'messages', $messages );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems',
                                array_key_exists('submenu',
                                $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array()
);

if (isset($handler)) {
	$smarty->assign( 'active', $handler->fetchForwardSegment()->isActive() );
	$smarty->assign( 'keep', $handler->fetchForwardSegment()->getKeepOnServer() );
	$smarty->assign( 'address', $handler->fetchForwardSegment()->getForwardAddress() );
}

$smarty->assign( 'maincontent', 'forward.tpl' );
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
