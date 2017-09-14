<?php

/*
 *  Copyright (c) 2004 Klaraelvdalens Datakonsult AB
 *  Copyright (C) 2007 by Intevation GmbH
 *  Copyright (c) 2017 Mike Gabriel <mike.gabriel@das-netzwerkteam.de>
 *
 *    Originally written by
 *    Sascha Wilde <wilde@intevation.de>
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

// Generate OpenLDAP style SSHA password strings
function ssha($string, $salt)
{
  return "{SSHA}" . base64_encode(pack("H*", sha1($string . $salt)) . $salt);
}

// return 4 random bytes
function gensalt()
{
  $salt = '';
  while (strlen($salt) < 4)
    $salt = $salt . chr(mt_rand(0,255));
  return $salt;
}

// Check that passwords from form input match
function checkpw( $form, $key, $value ) {
  global $action;
  if( $action == 'firstsave' ) {
    if( $key == 'password_0' ) {
      if( $value == '' ) return _('Password is empty');
    } else if( $key == 'password_1' ) {
      if( $value != $_POST['password_0'] ) {
        return _('Passwords dont match');
      }
    }
  } else {
    if( $value != $_POST['password_0'] ) {
      return _('Passwords dont match');
    }
  }
  return '';
}

?>
