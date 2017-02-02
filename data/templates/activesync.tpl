{* Smarty Template *}
{*
  Local variables:
  buffer-file-coding-system: utf-8
  End:
*}
<h1>{tr msg="ActiveSync Configuration"}</h1>
<div id="wrapper" style="width:90%">
	<div id="heading">
					<ul id="buttons">

		    {section name=device loop=$devices}
		       <li>{if $devices[device].alias}{$devices[device].alias}{else}{$devices[device].name}{/if}</li>
		{/section}
		    </ul>
     </div>
     
     <div id="panes">
			<div id="content">
    {section name=device loop=$devices}
        <div id="tabs{$devices[device].id}" class="pane">
        <form name="babajaga-{$devices[device].id}" method="post">
        <input type="hidden" name="deid" value="{$devices[device].name}">
        </form>
        <form method="post">
        <input type="hidden" name="serial" value="{$devices[device].name}">
        <input type="hidden" name="type" value="{$devices[device].type}">
         <input type="hidden" name="actived" value="{$devices[device].id}">
        <table width="100%" cellpadding="8" cellspacing="8"><tr valign="top"><td style="border-top-style:dotted;border-right-style:dotted;border-bottom-style:dotted;border-left-style:dotted;border-width:1px;">
        {foreach from=$folders key=myId item=fol}
        <table border="0" width="100%">
        <tr bgcolor="#c8d2df"><td colspan="5"> <img src="/admin/images/{$myId}.png" style="vertical-align:middle;"> <span style="font-weight:bolder;">{tr msg=$pnames[$myId]} </span></td></tr>
        <tr>
        <td width="20">&nbsp;</td>
        <td width="5"><img src="/admin/images/sync.png" align="center" style="margin-left:5px;"></td>
        
        {if $myId eq 'EVENT' or $myId eq 'TASKS'}
	        <td width="5"><img src="/admin/images/alarm.png" align="center" style="margin-left:5px;"></td>
	       {else} 
	       <td width="25"></td>
        {/if}
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      
        </tr>
      {foreach from=$fol key=myIdfol item=fols}  
 
         <td width="20">&nbsp;</td>
        {kolabsync serial=$devices[device].name folder=$fols.realname}
       <td width="25"><img src="/admin/images/f.png" style="vertical-align:middle;padding:4px;"></td>
        <td>{$fols.name}</td>
       </tr>
  
       
         {/foreach}
            </table>
            <br/>
{/foreach}
  
        </td><td width="50%" style="border-top-style:dotted;border-right-style:dotted;border-bottom-style:dotted;border-left-style:dotted;border-width:1px;">
        <table width="100%" bgcolor="#c8d2df">
        <tr bgcolor="white" style="width:100%;height:35px;"><td><span style="font-weight:bolder;"> {tr msg="Device Alias"}</td></span></tr>
        <tr><td><br/><input type="text" name="alias" maxlength="25" value="{$devices[device].alias}" /><br/><br/></td></tr>
        <tr bgcolor="white" style="width:100%;height:35px;"><td><span style="font-weight:bolder;"> {tr msg="Device Mode"}</td></span></tr>
        <tr><td><br/>{html_options name=mode width=50 options=$modetypes selected=$devices[device].mode}<br/><br/></td></tr>
        <tr bgcolor="white" style="width:100%;height:35px;"><td><span style="font-weight:bolder;"> {tr msg="Picture Settings"}</td></span></tr>
{if $laxpicdef eq -1}
        <tr><td><br/><input type="checkbox" name="laxpic" value=1 {if $devices[device].laxpic}checked{/if}/> {tr msg="Enable PNG and GIF formats while syncing"}</td></tr>
{/if}
        </table>
 
        </td></tr></table>
        
         <input type="submit" value="{tr msg="Save"}" style="float:right;padding:6px;margin-right:22px;">
         <input type="reset" value="{tr msg="Clear"}" style="float:right;padding:6px;margin-right:22px;">
         <input type="button" name="delme" value="{tr msg="Forget Device"}" style="float:right;padding:6px;margin-right:132px;color:#ff0000;" onclick="DeleteThisDeviceMate({$devices[device].id});">
        
        </form>
       </div>
      {/section}  

    </div></div>
</div>

</div>

{literal}
<script type="text/javascript" charset="utf-8">
		window.addEvent('load', function () {
			myTabs = new SlidingTabs('buttons', 'panes',{startingSlide:"{/literal}tabs{$actived}{literal}"});
			
			// this sets up the previous/next buttons, if you want them
			//$('previous').addEvent('click', myTabs.previous.bind(myTabs));
			//$('next').addEvent('click', myTabs.next.bind(myTabs));
			
			// this sets it up to work even if it's width isn't a set amount of pixels
			window.addEvent('resize', myTabs.recalcWidths.bind(myTabs));
		});
		
		function DeleteThisDeviceMate(did)
		{
			if (confirm("{/literal}{tr msg="Are you sure?"}{literal}")) { 
 			document.forms["babajaga-"+did].submit();
 			//alert("babajaga-"+did);
		}
			
		}
		
	function stopRKey(evt) {
	  var evt = (evt) ? evt : ((event) ? event : null);
	  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	  if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
	}
	
	document.onkeypress = stopRKey;

	</script>
	{/literal}
