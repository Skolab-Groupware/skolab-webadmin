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

require_once('config.php');
require_once('locale.php');

include_once('smarty3/Smarty.class.php');

// PENDING: Remove this before production(!)
//function count_bytes($tpl_output, &$smarty) {
//  return $tpl_output.strlen($tpl_output);
//}

class MySmarty extends Smarty {
  function __construct() {
	global $topdir;
	global $php_dir;
	global $language;
	global $smarty_templates_dir;
	global $smarty_compiledir;
	global $webserver_web_prefix;
	global $params;
	global $smarty_debugging;

	Smarty::__construct();
	$this->debugging = $smarty_debugging;

	$this->template_dir = $smarty_templates_dir;
	$this->compile_dir = $smarty_compiledir;

	//$this->register_outputfilter("count_bytes");

	$this->assign('webserver_web_prefix', $webserver_web_prefix);
	$this->assign('skolab_webmailer_url', $params['skolab_webmailer_url']);
	$this->assign('topdir', $topdir);
	$this->assign('self_url', $_SERVER['REQUEST_URI']);

	$cleanurl = preg_replace('/(\?|&)lang=(.*)(&|$)/', '', $_SERVER['REQUEST_URI']);
	$this->assign( 'lang_url',
				   strpos($cleanurl,'?')===false?
				   ($cleanurl.'?lang='):
				   ($cleanurl.'&lang=') );

	// If you add a translation,
	// add the new language here
	$this->assign( 'currentlang', $language );
	$this->assign( 'languages', array(
									  array( 'name' => 'Deutsch',
											 'code' => 'de_DE' ),
									  array( 'name' => 'English',
											 'code' => 'en_US' ),
									  array( 'name' => 'Français',
											 'code' => 'fr_FR' ),
									  array( 'name' => 'Italiano',
											 'code' => 'it_IT' ),
									  array( 'name' => 'Nederlands',
											 'code' => 'nl_NL' ),
									  array( 'name' => 'Español',
											 'code' => 'es_ES' ),
									  ));
  }

  /** UTF-8 friendly htmlentities() */
  /* static */ function htmlentities( $str ) {
	return htmlentities( $str, ENT_QUOTES, "UTF-8");
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
