<?php
/*
 *  Copyright (c) 2004 Klarälvdalens Datakonsult AB
 *
 *    Written by Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 *    
 *	(c) 2011 Bogomil Shopov <shopov@kolabsys.com>
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

require_once('KolabAdmin/include/mysmarty.php');
require_once('KolabAdmin/include/headers.php');
require_once('KolabAdmin/include/locale.php');
require_once('KolabAdmin/include/authenticate.php');
require_once('KolabAdmin/include/form.class.php');
require_once('KolabAdmin/include/passwd.php');

//try to include ALL possible configuration files
@include_once '/kolab/var/kolab/www/z-push/config.php';
@include_once '/etc/z-push/config.php';
@include_once '/usr/share/z-push/config.php';
@include_once '/var/www/z-push/config.php';



//define errors array
$errors = array();

if((@include_once 'Horde/Kolab/Kolab_Zpush/lib/kolabActivesyncData.php') === false ) {
	//z-Push in not installed. Why don't you show some scarry warining?
	$errors[] =_('zPush in not enabled in your system.');
}

/*read from her value of the KOLAB_LAXPIC
 -1 = allow the user to select (or if the constant doesn't exist) 
 0  = same as the annotations (no lax mode just jpeg)
 1  = force the lax mode for all 
*/
if(!defined('KOLAB_LAXPIC')){define('KOLAB_LAXPIC',-1);}



//delete device
if(isset($_POST['deid']))
{
 $save = new ActiveSyncManager();
 $save->deleteDevice($_POST['deid']);
}

if (isset($_POST['actived']))
{

	$save = new ActiveSyncManager();
	$save->storeDevicenFolders($_POST);
}



 class ActiveSyncManager
{

 var $inbox;
 var $connectstring;
 var $orig_devs;
 var $base_folders;

function __construct()
{
	//not sure about that
	list($prefix, $domain) = split("@",$_SESSION['auth_user']);
	$this->connectstring= $_server = "{localhost:143/imap/notls/norsh}"; 
	$tmbox = imap_open($_server , $_SESSION['auth_user'], $_SESSION['auth_pw'], OP_HALFOPEN);
	  if ($tmbox) {
	             $this->inbox=$tmbox;
	            }
	            else {
	               return false;
	        }
}




//get devices from annotation

 function getDevices()
    {
	        
	    $result = imap_getannotation($this->inbox, "INBOX", "/vendor/kolab/activesync", "value.priv");
	   
		$gp=new GlobalParam();
		if (isset($result["value.priv"])) 
	    {
	         $gp->unserialize($result["value.priv"]);
	         
	         
	    }
	    $i=0;
	     foreach( $gp->deviceList() as $device)
	     {
	      //$devs[$device]=array("S"=>$gp->getDeviceMode($device),"Type"=>$gp->getDeviceType($device));
	      
	      
	      
	      $odevs[$device]['MODE']= $gp->getDeviceMode($device);
	      $odevs[$device]['TYPE'] = $gp->getDeviceType($device);
	      $odevs[$device]['ALIAS']= $gp->getDeviceAlias($device);
	      $odevs[$device]['LAXPIC'] = $gp->getDeviceLaxPic($device);
	      
	      //we need original devices in order to save the annotation later
	      $this->orig_devs = $odevs;
	     
	     //variable for pasring via Smarty
	      $devs[$i]['name'] = $device;
	      $devs[$i]['id'] = $i;
	      $devs[$i]['mode']= $gp->getDeviceMode($device);
	      $devs[$i]['type'] = $gp->getDeviceType($device);
	      $devs[$i]['alias']= $gp->getDeviceAlias($device);
	      $devs[$i]['laxpic'] = $gp->getDeviceLaxPic($device);
	      $i++;
	      
	     }
	     
	    return $devs;
    
    }

    
    function deleteDevice($serial)
    {
    	$result = imap_getannotation($this->inbox, "INBOX", "/vendor/kolab/activesync", "value.priv");
	   
		$gp=new GlobalParam();
		if (isset($result["value.priv"])) 
	    {
	         $gp->unserialize($result["value.priv"]);
	                  
	    }
	    foreach( $gp->deviceList() as $device)
	     {
	      $odevs[$device]['MODE']= $gp->getDeviceMode($device);
	      $odevs[$device]['TYPE'] = $gp->getDeviceType($device);
	      $odevs[$device]['ALIAS']= $gp->getDeviceAlias($device);
	      $odevs[$device]['LAXPIC'] = $gp->getDeviceLaxPic($device);
	     }	
	     
	     unset($odevs[$serial]);
	     
    
    //delete device
   $to_store=array('DEVICE'=>$odevs);
   $value_to_store= base64_encode(json_encode($to_store));
 
  //resource stream_id, string mailbox, string entry, string attr, string value
  imap_setannotation($this->inbox, "INBOX", "/vendor/kolab/activesync","value.priv",$value_to_store);
    
    
    }
    
    
    
    
    function getFolderAnn($folder)
    {
    
    $result = imap_getannotation($this->inbox, $folder, "/vendor/kolab/activesync", "value.priv");
    
    
    if (isset($result["value.priv"])) 
	    {
	     $res=  json_decode(base64_decode($result["value.priv"]),true);
	    }
	    
    return $res['FOLDER'];
    
    }
    
    function getFolders()
    {
    
		    $list = imap_getmailboxes($this->inbox, $this->connectstring, "*");
		    
		if (is_array($list)) {
		
			$i=0;
			//print_r($list);
		    foreach ($list as $key => $val) {
		      
		     //we need realname in order to check for annotations
		     
		     $last= strrpos($val->name,'}');
		     
		     //foldername
		     
		     $folder=substr($val->name,($last+1),strlen($val->name));
		     
		     //put in array folders
		     $folders[$i]['realname']=$folder;
		     
		     //fic the encoding
		     $folder = mb_convert_encoding($folder,"UTF8", "UTF7-IMAP");
		     
		     //remove Inbox/ part from the name
		     $folder_chunk=explode("/",$folder);
		     
		     //if it's only inbox/ show it
		     
		     
		     if($folder_chunk[1]!="" and count($folder_chunk)==2 and $folder_chunk[0]=="INBOX"){
		     //else is inbox/somepart - show only somepart
		     $folder=$folder_chunk[1];
		     
		     }if($folder_chunk[2]!="" and count($folder_chunk)==3 and $folder_chunk[0]=="user")
		     {
		      //user folder
		      $folder= preg_replace(array('/INBOX\//'),array(''),$folder);
		     }else
			 	{
			 		$folder= preg_replace(array('/INBOX\//'),array(''),$folder);
					
			 	}
		     //for saving annot
		     $part_folder[]=$folders[$i]['realname'];
		     
		     
		     //for smarty
		     $folders[$i]['name']=$folder;
			 $folders[$i]['type']=$this->getFolderType($folders[$i]['realname']);
			 
			 $this->base_folders=$part_folder;
		     $i++;
		        
		      
		    }
		} else {
		    echo "imap_getmailboxes failed: " . imap_last_error() . "\n";
		}
		
		//start sorting
		//define array
		$types = array("MAIL","CONTACT","EVENT","TASKS");
		
		
		
		
		foreach ($types as $key => $row) {
		        for($im=0;$im<count($folders);$im++) {
				 if ($folders[$im]['type']==$row)
		    		 $foldern[$row][$im] = $folders[$im];
		    		
		        }
			}
		//array_multisort($type, SORT_DESC, $folders);
		
		return $foldern;
		//return $folders;
    }
    
function getFolderType($folder)
  {
  
  
        $result = imap_getannotation($this->inbox, $folder, "/vendor/kolab/folder-type", "value.shared");
		
	    if (isset($result["value.shared"])) 
	    {
	    
	     if(preg_match("/(event)/",$result["value.shared"]))
	     {
	     	return "EVENT";
	     
	     }else if(preg_match("/(task)/",$result["value.shared"]))
	     {
	     
	      return "TASKS";
	     }else if(preg_match("/(contact)/",$result["value.shared"]))
	     {
	     
	      return "CONTACT";
	     }
	    else if(preg_match("/(note)/",$result["value.shared"]))
	     {
	     
	      return "NOTES";
	     }
	    }else{
	    
	    return "MAIL";
	    }
  
  }
  
  
  
  function storeDevicenFolders($device){
  

  //get current annotation devices
  $devices= $this->getDevices();
  
  //assign the array to an variable
  $old_devices= $this->orig_devs;
  
  //change the data on this device, based on $POST_data
  $old_devices[$device['serial']]['MODE']=$device['mode'];
  $old_devices[$device['serial']]['ALIAS']=$device['alias'];
  $old_devices[$device['serial']]['LAXPIC']=$device['laxpic'];
  $old_devices[$device['serial']]['TYPE']=$device['type'];
  $to_store=array('DEVICE'=>$old_devices);
  $value_to_store= base64_encode(json_encode($to_store));
 
  //resource stream_id, string mailbox, string entry, string attr, string value
  imap_setannotation($this->inbox, "INBOX", "/vendor/kolab/activesync","value.priv","");
  if($old_devices[$device['serial']]['TYPE']=$device['type'])
  {
  	imap_setannotation($this->inbox, "INBOX", "/vendor/kolab/activesync","value.priv",$value_to_store);
  }
  
  //Folders
  //1. Get All folders
    $this->GetFolders();
  
  //set all folders -> array
    $allfolders= $this->base_folders;
  
    foreach($allfolders as $index=>$folder)
    {
    
    $smfolder=preg_replace(array('/( )/','/\./'),array('@','*'),$folder);
    
    
    
      //is folder is default and ! in POST array = set s=0;
      if($this->is_default($folder) and !isset($device[$smfolder]))
      {
    		$this->SetUnset($folder,$device); 
    		
      
      }else if($this->is_default($folder) and isset($device[$smfolder]))
      {
        $this->SetSet($folder,$device); 
        
      }else if(isset($device[$smfolder]))
      {
     
             // is not defaul - just we have it in post with value == put in annot this value
      $this->SetSet($folder,$device);
      
      
      }else
      {
      //is not default folder - we don't have it == unset this value in annot
       $this->SetUnset($folder,$device); 
      
      }
    
    }
   
  
  }
  
 //if the folder is yes for sync by default -return true; 
  
  
  function SetUnset($folder,$device)
  {
  
 
  $result = imap_getannotation($this->inbox, $folder, "/vendor/kolab/activesync", "value.priv");
    
    
    if (isset($result["value.priv"])) 
	    {
	   $res=  json_decode(base64_decode($result["value.priv"]),true);
	    }
	    
	    $res['FOLDER'][$device['serial']]['S']=0;
	   
	  imap_setannotation($this->inbox, $folder, "/vendor/kolab/activesync","value.priv",base64_encode(json_encode($res)));
	    
	 
 
  
  }
  
  function SetSet($folder,$device)
  {
  $result = imap_getannotation($this->inbox, $folder, "/vendor/kolab/activesync", "value.priv");
    //echo "$folder<br/>";
    
    if (isset($result["value.priv"])) 
	    {
	   $res=  json_decode(base64_decode($result["value.priv"]),true);
	    }
	//    echo $folder."<pre>";
	 //   print_r($res);
	  //  echo "</pre><hr>";
	    $smfolder=preg_replace(array('/( )/','/\./'),array('@','*'),$folder);
	    
	    $res['FOLDER'][$device['serial']]['S']=$device[$smfolder];
	    imap_setannotation($this->inbox, $folder, "/vendor/kolab/activesync","value.priv",base64_encode(json_encode($res)));
	   // echo $folder."<pre>";
	   // print_r($res);
	  //  echo "</pre><hr>";
	    
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
  
  
  function __destruct()
	{
	imap_close($this->inbox);
	$this->inbox = "";
	 
	
	}
}



/**** Authentication etc. ***/
$sidx = 'activesync';
require_once('KolabAdmin/include/menu.php');

/**** Submenu for current page ***/
$menuitems[$sidx]['selected'] = 'selected';



//ActiveSync part
if(count($errors) <1)
{
$d= new ActiveSyncManager;

$devs= $d->getdevices();
$folds=$d->getfolders();
$ola=true;

if(count($devs)<1)
{
	$errors[] =_("There are currently no devices known for your user.<br/><br/>In order to register a device, please connect it to the server first, using <a href='http://wiki.kolab.org/Z_push#Clients'>the instructions in the Wiki</a>. Afterwards the device should become available for configuration in this dialogue.");
	$ola = false;
}
}



/**** Insert into template and output ***/
$smarty = new MySmarty();
//add the plugin
$smarty->plugins_dir[] = 'KolabAdmin/include/';
$smarty->assign( 'errors', $errors );
$smarty->assign( 'messages', $messages );
$smarty->assign( 'uid', $auth->uid() );
$smarty->assign( 'group', $auth->group() );
$smarty->assign( 'page_title', $menuitems[$sidx]['title'] );
$smarty->assign( 'menuitems', $menuitems );
$smarty->assign( 'submenuitems', 
				 array_key_exists('submenu', 
								  $menuitems[$sidx])?$menuitems[$sidx]['submenu']:array() );
$smarty->assign( 'active', $active );


//print_r($d->getdevices());
if ($ola !=false)
{
 //select list of modes
 
$publicnames = array("MAIL"=>_("E-mail"),"CONTACT"=>_("Address Books"),"EVENT"=>_("Calendars"),"TASKS"=>_("Tasks"),"NOTES"=>_("Notes"));


$smarty->assign('modetypes', array(
                                -1 => _('Automatic'),
                                0 => _('Flat Mode'),
                                1 => _('Folder Mode')
                                ));
                                
$smarty->assign('picsettings', array(
                                0 => _('No'),
                                1 => _('Yes')
                                ));
                                

 $smarty->assign( 'devices',$devs);
 $smarty->assign( 'pnames',$publicnames);
 $smarty->assign( 'folders',$folds);
 if ($_POST['actived']){$ac=$_POST['actived'];}else{$ac=0;}
 $smarty->assign( 'actived',$ac );
 $smarty->assign( 'laxpicdef',KOLAB_LAXPIC);
 $smarty->assign( 'maincontent', 'activesync.tpl' );
}


$smarty->display('page-ajax.tpl');

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