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
require_once('Smarty/Smarty.class.php');
require_once('locale.php');

// PENDING: Remove this before production(!)
//function count_bytes($tpl_output, &$smarty) {
//  return $tpl_output.strlen($tpl_output);
//}

class MySmarty extends Smarty {
  function MySmarty() {
	global $topdir;
	global $php_dir;
	global $language;
	$this->Smarty();
	
	$basedir = "$php_dir/admin/";
	$this->template_dir = $basedir.'templates/';
	$this->compile_dir = $basedir.'templates_c/';
	$this->config_dir = $basedir.'configs/';
	//$this->cache_dir = $basedir.'cache/';
	// Added for i18n management (Romain 05-03-03)
	$this->register_function("tr", "translate");

	//$this->register_outputfilter("count_bytes");

	$this->assign( 'topdir', $topdir );
	$this->assign( 'self_url', $_SERVER['REQUEST_URI'] );
	$this->assign( 'lang_url', 
				   strpos($_SERVER['REQUEST_URI'],'?')===false?
				   ($_SERVER['REQUEST_URI'].'?lang='):
				   ($_SERVER['REQUEST_URI'].'&lang=') );

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
									  array( 'name' => 'Néerlandais',
											 'code' => 'nl_NL' )  
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
