<?php
/*
 *  Copyright (c) 2005 Klarälvdalens Datakonsult AB
 *
 *    Written by Romain Pokrzywka <romain@klaralvdalens-datakonsult.se>
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

  //require_once("mysmarty.php");

// PENDING(romain,steffen): Clean up so this is not a mix of code and global functions

session_start();

function supported_lang($lang) {

    // REMEMBER TO UPDATE THIS WHEN ADDING NEW LANGUAGES
    $a = array("de"    => "de_DE",
			   "de_de" => "de_DE",
			   "fr"    => "fr_FR",
			   "fr_fr" => "fr_FR",
			   "it"    => "it_IT",
			   "it_it" => "it_IT",
			   "nl"    => "nl_NL",
			   "nl_nl" => "nl_NL",
			   "en"    => "en_US",
			   "en_gb" => "en_US",
			   "en_us" => "en_US",
			   "es"    => "es_ES",
			   "es_es" => "es_ES");

    // Locales must be in the format xx_YY to be recognized by xgettext
    $lang = strtolower(str_replace('-','_',$lang));
    if( !array_key_exists( $lang, $a ) ) return false;
    else return $a[$lang];
}

// This function is called in templates my Smarty
function translate($params) 
{
    $msg = $params["msg"];
    $domain = $params["domain"];
    if(empty($domain)) {
        return gettext($msg);
    } else {
        return dgettext($domain, $msg);
    }
}

# Returns the currently selected language
function getLanguage()
{
    if(empty($_SESSION["lang"])) 
    {
	    $acceptList = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
        if(empty($acceptList)) {
			$lang = "en";
        } else {
			// In case of multiple accept languages, keep the first one
			$acceptList = explode(",", $acceptList);
			foreach($acceptList as $l) {
				$pos = strpos($l, ';' );
				if( $pos !== false ) {
				    $l = explode(';',$l);
					$l = $l[0];
				}
				if( $tmp = supported_lang($l) ) {
				    $lang = $tmp;
				    break;
				}
			}
        }
		if( !$lang ) $lang = "en";
        setLanguage($lang);
    }    
    return supported_lang($_SESSION["lang"]);
}

# Allows languages to be set by users
function setLanguage($lang)
{   
    $lang = supported_lang($lang);
    $_SESSION["lang"] = $lang;
}

// Check if language was changed
if(!empty($_REQUEST["lang"])) {
    setLanguage($_REQUEST["lang"]);
}

// I18N support information
$language = getLanguage();
putenv("LANG=$language"); 
putenv("LANGUAGE=$language"); 
setlocale(LC_ALL, $language);

$domain = "messages";
//$tmpSmarty = new MySmarty();
bindtextdomain($domain, $locale_dir);
bind_textdomain_codeset($domain, "UTF-8");
textdomain($domain);

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
