<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.kolabsync.php
 * Type:     function
 * Name:     Kolabsync
 * Purpose:  Check folder settings
 * -------------------------------------------------------------
 */
global $tmbox;
@include_once 'Horde/Kolab/Kolab_Zpush/lib/kolabActivesyncData.php';

function smarty_function_kolabsync($params, &$smarty)
{

    $result = checkFolder($params['folder'],$params['serial']);
    if ($result=="") $result="0";
    $folder_type=getFolderType($params['folder']);

    //array
    if ($result==1)
    {
     $checked="checked=checked";


    }elseif($result==2)
    {

    $checked2="checked=checked";
    $checked="checked=checked";
    }

    else
    {
     $cheked="";
     $checked2="";
    }
    $rnd=rand(5, 121);

    //stupid smarty hack
    $params['folder']=preg_replace('/( )/','@',$params['folder']);
    $params['folder']=preg_replace('/\./','*',$params['folder']);

    $ch = "<td><input type=checkbox name='".$params['folder']."' value='1' $checked></td><td width='20'></td>";


	if(preg_match("/(event)/",$folder_type) OR preg_match("/(task)/",$folder_type))
    {
    	$ch = "<td><input type='checkbox' id='".$rnd."-1' name='".$params['folder']."' value='1' $checked onclick='synced(this)'></td>
    	<td><input type='checkbox' id='".$rnd."-2' name='".$params['folder']."' value='2' $checked2 onclick='synced(this)'></td>";
    }




 return $ch;
}


function connect()
{
global $tmbox;

		$connectstring= $_server = "{localhost:143/imap/notls/norsh}";
		$tmbox = imap_open($_server , $_SESSION['auth_user'], $_SESSION['auth_pw'], OP_HALFOPEN);
		  if ($tmbox) {
		  return $tmbox;
		  }else
		  {
		  return false;
		  }

}

function checkFolder($folder,$serial)
{
	$gp=new FolderParam();
    $result = imap_getannotation(connect(), $folder, "/vendor/kolab/activesync", "value.priv");


    if (isset($result["value.priv"]))
    {
     $res=  json_decode(base64_decode($result["value.priv"]),true);


     }

     //is default folder for syncing

     $def=is_default($folder);


	if($def==true)
	{
    //all default folders
	$r=1;
	}

	     if((isset($res['FOLDER'][$serial]['S'])) and $res['FOLDER'][$serial]['S'] ==0)
	     {

	     	$r=0;
	     }else  if((isset($res['FOLDER'][$serial]['S'])) and $res['FOLDER'][$serial]['S'] !=0)
	     {
	     return $r=$res['FOLDER'][$serial]['S'];

	     }


	     return $r;

}

	function is_default($folder)
	{

	 if(preg_match("/(^INBOX)/", $folder))
	 {

	  return true;
	 }else
	 {
	 	return false;
	 }
	}

 //get folder type

function getFolderType($folder)
  {
  global $tmbox;

        $result = imap_getannotation($tmbox, $folder, "/vendor/kolab/folder-type", "value.shared");

	    if (isset($result["value.shared"]))
	    {
	     return $result["value.shared"];
	    }

  }


/* vim: set expandtab: */

?>
