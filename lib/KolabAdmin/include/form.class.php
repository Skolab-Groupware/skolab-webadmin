<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *
 *    Writen by Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
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

function checkuniquealias( $form, $key, $value ) {
  global $ldap;
  global $action;
  global $dn;
  $excludedn = false;
  if( $action == 'save' ) $excludedn = trim($dn);
  $lst = array_unique( array_filter( array_map( 'trim', preg_split( '/\n/', $value ) ), 'strlen') );
  $str = '';
  foreach( $lst as $alias ) {
	debug( "looking at $alias, exluding $dn" );
	if( $ldap->countMail( $_SESSION['base_dn'], $alias, $excludedn ) > 0 ) {
	  $str .= _('Email address ').MySmarty::htmlentities($alias)._(' collides with an address already used for another user, a vCard or a distribution list.<br />');
	}
  }
  return $str;
}

// Due to a cyrus imapd bug we can't currently
// support quotas over 4095 MB, see issue1262
function checkquota( $form, $key, $value )
{
	if( empty($value) ) return ''; // OK
	else if( $value < 0 ) return _('Quota can not be negative');
	else if( !is_numeric($value) ) return _('Quota must be a number');
	else if( (int) $value != $value ) return _('Quota must be an integer');
	else return '';
}

function checkphone( $form, $key, $value )
{
	if( empty($value) ) return ''; // OK
	else if( !preg_match('/^[a-zA-Z0-9 ()+\/.=?:-]*$/',$value) ) return _('Phone entries may only contain a-z, numbers, spaces and the characters ()-+/.=?:');
	else return '';
}

class KolabForm {
  /*
   * $entries should be an array of the form:
   * array( "fieldname" => array( "name" => "uservisible text",
   *                              "value" => "some value",
   *                              "comment" => "some text",
   *                              "type" => "input/textarea/...",
   *                              "validation" => "notempty/<callbackfnct>" ),
   *        "fieldname2" => array( ... ) )
   *
   * The "callbackfnct" function should be a global function with 3 parameters:
   * (form, key, value)
   */
  function KolabForm( $name, $template, $entries ) {
    $this->name = $name;
    $this->template = $template;
    $this->entries = $entries;
    $this->submittext = _('Submit');
    foreach( $this->entries as $key => $value ) {
      if( !isset( $value['type'] ) ) $this->entries[$key]['type'] = '';
      if( !isset( $value['comment'] ) ) $this->entries[$key]['comment'] = '';
      if( !isset( $value['attrs'] ) ) $this->entries[$key]['attrs'] = '';
    }
  }

  /*
   * this function will be called if an instance of KolabForm will be treated
   * as a string
   */
  function __toString() {
    return sprintf("Debuginformation for class: %s; name: %s; template: %s;", get_class($this), $this->name, $this->template);
  }

  /*private*/
  function comment_helper( &$value ) {
    $ast = '';
    if( isset($value['validation'])){
      if( is_array( $value['validation'] ) && in_array( 'notempty', $value['validation']) ) {
	$ast = '<span class="required_asterisk">*</span> ';
      } else if( $value['validation'] == 'notempty' ) {
	$ast = '<span class="required_asterisk">*</span> ';
      }
    }
    return $ast.$value['comment'];
  }

  function outputForm() {
    debug_var_dump($this->entries);
    $str = '<div class="contentform">';
    /*
    if( count( $this->errors ) > 0 ) {
      $str .= '<div class="contenterror">';
      foreach( $this->errors as $err ) {
	$str .= $err.'<br/>';
      }
      $str .= '</div>';
    }*/
    $str .= '<form name="'.$this->name.'" method="post">';
    $str .= '<table class="contentformtable">';
    $str .= _('<tr><th>Attribute</th><th>Value</th><th>Comment</th></tr>')."\n";

    $size = 60;
    foreach( $this->entries as $key => $value ) {
      if( !isset( $value['type'] ) || $value['type']=='' ) $value['type'] = 'text';
      if( !isset( $value['comment'] ) ) $value['comment'] = '';
      if( !isset( $value['attrs'] ) ) $value['attrs'] = '';
      if( !isset( $value['value'] ) ) $value['value'] = '';
      switch( $value['type'] ) {
      case 'hidden': continue;
      case 'password':
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  // If readonly, skip it -- passwords are at most write-only
	  break;
	}
      case 'input':
      case 'text':
	$str .= '<tr>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><p class="ctrl">'.MySmarty::htmlentities($value['value']).'</p><input name="'
	    .$key.'" type="hidden" value="'.MySmarty::htmlentities($value['value']).'" /></td>';
	} else {
	  $str .= '<td><label for="'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><input name="'.$key.'" id="'.$key.'" type="'.$value['type'].'" value="'.MySmarty::htmlentities($value['value']).'" '
	    .MySmarty::htmlentities($value['attrs']).' size="'.$size.'" /></td>';
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'email':
	$str .= '<tr>';
	if( strpos($value['value'],'@')===false ) {
	  $uname = $value['value'];
	  $domain = '';
	} else {
	  list($uname,$domain) = split('@',$value['value']);
	}
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><p class="ctrl">'.MySmarty::htmlentities($value['value']).'</p><input name="user_'.$key.'" type="hidden" value="'.
	    MySmarty::htmlentities($uname).'" /><input name="domain_'.$key.'" type="hidden" value="'.
	    MySmarty::htmlentities($domain).'" /></td>';
	} else {
	  $str .= '<td><label for="user_'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><input name="user_'.$key.'" id="user_'.$key.'" type="text" value="'.MySmarty::htmlentities($uname).'" '
	    .$value['attrs'].' size="'.($size-40).'" />';
	  if( count($value['domains']) == 1 ) {
	    $str .= '<input name="domain_'.$key.'" type="hidden" value="'.MySmarty::htmlentities($value['domains'][0]).'" />';
	    $str .= '<span class="ctrl">@'.MySmarty::htmlentities($value['domains'][0]).'</span></td>';
	  } else {
	    $str .= '<label for="domain_'.$key.'">@</label><select name="domain_'.$key.'" id="domain_'.$key.'" '.$value['attrs']." >\n";
	    foreach( $value['domains'] as $dom ) {
	      if( $dom == $domain ) $s = 'selected="selected"';
	      else $s = '';
	      $str .= '<option value="'.MySmarty::htmlentities($dom).'" '.$s.'>'.MySmarty::htmlentities($dom).'</option>'."\n";
	    }
	    $str .= '</select>';
	    $str .= '</td>';
	  }
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'comment':
	$str .= '<tr>';
	$str .= '<td>'.$value['name'].'</td>';
	$str .= '<td>'.$value['value'].'</td>';
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'textarea':
	$str .= '<tr>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><p class="ctrl">'.MySmarty::htmlentities($value['value']).'</p></td>';
	} else {
	  $str .= '<td><label for="'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><textarea name="'.$key.'" id="'.$key.'" rows="5" cols="'.$size.'" '.$value['attrs'].' onkeypress="javascript:textareakeypress()">'.MySmarty::htmlentities($value['value']).'</textarea></td>';
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'checkbox':
	$str .= '<tr>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><span class="ctrl">'.($value['value']?_('Yes'):_('No')).'</span></td>';
	} else {
	  $str .= '<td><label for="'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><input name="'.$key.'" id="'.$key.'" type="'.$value['type'].'" value="on" '.($value['value']?'checked':'').' '.$value['attrs'].' /></td>';
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'checklist':
	$str .= '<tr>';
	$str .= '<td><label>'.$value['name'].'</label></td>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><div class="ctrl">'.(join('<br/>',$value['options'])).'</div></td>';
	} else {
	  $str .= '<td><table class="contentform">';
	  foreach( $value['options'] as $opt ) {
	    if( is_array( $value['value'] ) ) $checked = ( in_array($opt ,$value['value'] ))?"checked":"";
	    else $checked = "";
	    //debug("Checking if $opt is in ".join(",",$value['value'])." :$checked");
	    $str .= '<tr><td><input name="'.$key.'[]" type="checkbox" value="'.MySmarty::htmlentities($opt).'" '.$value['attrs']." $checked /></td><td>"
	      .MySmarty::htmlentities($opt).'</td></tr>';
	  }
	  $str .= '</table></td>';
	}
	$str .= '<td>'.$value['comment'].'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'select':
	$str .= '<tr>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><p class="ctrl">'.MySmarty::htmlentities($value['options'][$value['value']]).
	    '<input type="hidden" name="'.$key.'" value="'.MySmarty::htmlentities($value['value']).'" /></p></td>';
	} else {
	  $str .= '<td><label for="'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><select name="'.$key.'" id="'.$key.'" '.$value['attrs'].' >'."\n";

	  for( $i = 0; $i < count($value['options']); ++$i) {
	    if( $i == $value['value'] ) $s = 'selected="selected"';
	    else $s = '';
	    $str .= '<option value="'.$i.'" '.$s.'>'.MySmarty::htmlentities($value['options'][$i]).'</option>'."\n";
	  }
	  $str .= '</select>';
	  $str .= '</td>';
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'foldertypeselect':
	$str .= '<tr>';
	$entries = array ( '' => _('Unspecified'), 'mail' => _('Mails'), 'task' => _('Tasks'),
			   'journal' => _('Journals'), 'event' => _('Events'),
			   'contact' => _('Contacts'), 'note' => _('Notes'));
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  $str .= '<td><p class="ctrl">'.MySmarty::htmlentities($entries[$value['value']]).
	    '<input type="hidden" name="'.$key.'" value="'.MySmarty::htmlentities($value['value']).'" /></p></td>';
	} else {
	  $str .= '<td><label for="'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><select name="'.$key.'" id="'.$key.'" '.$value['attrs'].' >'."\n";
	  foreach ($entries as $id => $title) {
	    if ( $value['value'] == $id )
	      $s = 'selected="selected"';
	    else
	      $s = '';

	    $str .= '<option value="'.$id.'" '.$s.'>'.MySmarty::htmlentities($title).'</option>'."\n";
	  }
	  $str .= '</select>';
	  $str .= '</td>';
	}
	$str .= '<td>'.$value['comment'].'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'aclselect': // Special Kolab entry for ACLs
	$str .= '<tr>';
	if( ereg( 'readonly', $value['attrs'] ) ) {
	  $str .= '<td><label>'.$value['name'].'</label></td>';
	  if( $value['user'] ) $str .= '<td><span class="ctrl">'.MySmarty::htmlentities($value['user']).'</span> <span class="ctrl">'.$value['perm'].'</span></td>';
	} else {
	  $str .= '<td><label for="user_'.$key.'">'.$value['name'].'</label></td>';
	  $str .= '<td><input name="user_'.$key.'" id="user_'.$key.'" type="'.$value['type'].'" size="'.($size-15).'" value="'
	    .MySmarty::htmlentities($value['user']).'" '.$value['attrs'].' />';
	  $str .= '<select name="perm_'.$key.'">'."\n";
	  if( $value['perm'] ) $selected_perm = $value['perm'];
	  else $selected_perm = 'all';
	  foreach( array( 'none',
			  'post',
			  'read', 'read/post',
			  'append',
			  'write',
			  'read anon',
			  'read anon/post',
			  'read hidden',
			  'read hidden/post',
			  'all' ) as $perm ) {
	    if( $perm == $selected_perm ) $s = 'selected="selected"';
	    else $s = '';
	    $str .= '<option value="'.$perm.'"'.$s.' >'.$perm.'</option>'."\n";
	  }
	  $str .= '</select>';
	  $str .= '</td>';
	}
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      case 'resourcepolicy': // Special Kolab entry for group/resource policies
	debug("resourcepolicy");
	$ro = ereg( 'readonly', $value['attrs'] );
	$str .= '<tr>';
	$str .= '<td><label>'.$value['name'].'</label></td>';
	$str .= '<td>';
	$str .= '<table>';
	$i = 0;
	$tmppol = $value['policies'];
	unset($tmppol['']);
	ksort($tmppol);
	$tmppol[''] = 0;
	$policies = array( _('Always accept'),
			   _('Always reject'),
			   _('Reject if conflicts'),
			   _('Manual if conflicts'),
			   _('Manual') );
	foreach( $tmppol as $user => $pol ) {
	  debug("form: ".$user." => ".$pol);
	  if( $ro ) {
	    if( !$user ) continue;
	    $str .= '<tr><td>';
	    if( $user == 'anyone' ) $str .= '<p class="ctrl">'._('Anyone').'</p>';
	    else $str .= '<p class="ctrl">'.MySmarty::htmlentities($user).'</p>';
	    $str .= '</td><td><p class="ctrl">'.MySmarty::htmlentities($policies[$pol]).'</p></td></tr>'."\n";
	  } else {
	    $str .= '<tr><td>';
	    if( $user == 'anyone' ) {
	      $str .= _('Anyone').'<input type="hidden" name="user_'.$key.'_'.$i.'" value="'.MySmarty::htmlentities($user).'" '.$value['attrs'].' />';
	    } else {
	      $str .= '<input name="user_'.$key.'_'.$i.'" type="text" size="'.($size-20)
		.'" value="'.MySmarty::htmlentities($user).'" '.$value['attrs'].' />';
	    }
	    $str .= '</td><td><select name="policy_'.$key.'_'.$i.'">'."\n";
	    $j = 0;
	    foreach( $policies as $p ) {
	      if( $j == $pol ) {
		$str .= '<option value="'.$j++.'" selected="selected">'.$p.'</option>'."\n";
	      } else {
		$str .= '<option value="'.$j++.'">'.$p.'</option>'."\n";
	      }
	    }
	    $i++;
	    $str .= '</select></td></tr>'."\n";
	  }
	}
	$str .= '</table></td>';
	$str .= '<td>'.KolabForm::comment_helper($value).'</td>';
	$str .= '</tr>'."\n";
	break;
      }
    }
    $str .= '<tr><td colspan="3" align="center"><input type="submit" name="submit_'.$this->name.'" value="'
      .$this->submittext.'" '.$value['attrs'].' /></td></tr>';
    $str .= '</table>'."\n";
    foreach( $this->entries as $key => $value ) {
      if( !isset( $value['type'] ) ) $value['type'] = '';
      if( !isset( $value['comment'] ) ) $value['comment'] = '';
      if( !isset( $value['attrs'] ) ) $value['attrs'] = '';
      if( $value['type'] == 'hidden' ) {
	$str .= '<input name="'.$key.'" type="hidden" value="'.MySmarty::htmlentities($value['value']).'" '.$value['attrs'].' />';
      }
    }
    $str .= '</form>';
    $str .= '<div class="required_asterisk">'._('* Required field.').'</div>';
    $str .= '</div>';
    return $str;
  }

  function validate() {
    $this->errors = array();
    foreach( $this->entries as $key => $value ) {
      if( !empty( $value['validation'] ) && !ereg( 'readonly', $value['attrs'] ) ) {
	$vv = $value['validation'];
	if( !is_array($vv) ) $va = array($vv);
	else $va = $vv;
	foreach( $va as $v ) {
	  //print "validating using $v <br/>";
	  if( $v == 'notempty' ) {
	    debug("checking nonemptiness of $key: ".KolabForm::getRequestVar($key)." len=".strlen(trim(KolabForm::getRequestVar($key))));
	    if( $value['type'] == 'aclselect' ) {
	      // ignore
	    } else if( $value['type'] == 'email' ) {
	      debug('Checking '.$value['name'].': '.$_REQUEST['user_'.$key]);
	      if( strlen(trim($_REQUEST['user_'.$key])) == 0 ) {
		$this->errors[] = _('Required field ').$value['name']._(' is empty');
	      }
	    } else if( (!is_array($_REQUEST[$key]) && strlen( trim($_REQUEST[$key]) ) == 0) || empty($_REQUEST[$key]) ) {
	      $this->errors[] = _('Required field ').$value['name']._(' is empty');
	    }
	  } else {
	    if( $value['type'] == 'aclselect' ) {
	      $data = $_REQUEST['user_'.$key].' '.$_REQUEST['perm_'.$key];
	    } else if( $value['type'] == 'email' ) {
	      $data = trim($_REQUEST['user_'.$key]).'@'.trim($_REQUEST['domain_'.$key]);
	    } else if( $value['type'] == 'resourcepolicy' ) {
	      $i = 0;
	      $data = array();
	      while( isset($_REQUEST['user_'.$key.'_'.$i] ) ) {
		$data[] = $_REQUEST['user_'.$key.'_'.$i++];
	      }
	    } else {
	      $data = $_REQUEST[$key];
	    }
	    $errstr = $v( $this, $key, $data );
	    if( !empty( $errstr ) ) {
	      $this->errors[] = $errstr;
	    }
	  }
	}
      }
    }
    //print_r( $this->errors );
    return (count($this->errors) == 0);
  }

  function isSubmitted() {
    return isset( $_REQUEST['submit_'.$this->name] );
  }

  function value( $key ) {
    if( isset( $_REQUEST[$key] ) ) {
      return $_REQUEST[$key];
    } else {
      return $this->entries[$key]['value'];
    }
  }

  function setValues() {
    foreach( $this->entries as $k => $v ) {
      if( $this->entries[$k]['type'] == 'aclselect' ) {
	$this->entries[$k]['user'] = trim($this->value('user_'.$k));
	$this->entries[$k]['perm'] = $this->value('perm_'.$k);
      } else if( $this->entries[$k]['type'] == 'resourcepolicy' ) {
	$i = 0;
	$pols = array();
	while( isset($_REQUEST['user_'.$k.'_'.$i]) ) {
	  $pols[trim($_REQUEST['user_'.$k.'_'.$i])]
	    = trim($_REQUEST['policy_'.$k.'_'.$i]);
	  $i++;
	}
	$this->entries[$k]['policies'] = $pols;
      } else if( $this->entries[$k]['type'] == 'checkbox' ) {
	$this->entries[$k]['value'] = isset( $_REQUEST[$k] );
      } else if( $this->entries[$k]['type'] == 'checklist' ) {
	$this->entries[$k]['value'] = $_REQUEST[$k];
      } else if( $this->entries[$k]['type'] == 'password' ) {
	$this->entries[$k]['value'] = $this->value($k);
      } else if( $this->entries[$k]['type'] == 'email' ) {
	$this->entries[$k]['value'] = trim($this->value('user_'.$k)).'@'.trim($this->value('domain_'.$k));
      } else {
	$this->entries[$k]['value'] = trim($this->value($k));
      }
    }
  }

  /* static */ function getRequestVar($var, $default = false)
  {
    if( isset($_REQUEST[$var]) ) return $_REQUEST[$var];
    else return $default;
  }


  var $name;
  var $template;
  var $errors;
  var $entries;
  var $submittext;
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
