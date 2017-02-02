<?php
/* -------------------------------------------------------------------
   Copyright (c) 2004 Klaraelvdalens Datakonsult AB
   Copyright (C) 2007 by Intevation GmbH
   Author(s):
   Sascha Wilde <wilde@intevation.de>
   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>

   This program is free software under the GNU GPL (>=v2)
   Read the file COPYING coming with the software for details.
   ------------------------------------------------------------------- */

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