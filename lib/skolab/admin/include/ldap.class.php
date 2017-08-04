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

require_once('mysmarty.php');
require_once('skolab/admin/Ldap.php');
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
