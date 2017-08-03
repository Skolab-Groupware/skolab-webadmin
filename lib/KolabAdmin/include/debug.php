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

function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
function debug($str) {
    #print $str.'<br/>';
}
function debug_var_dump($var) {
    #print '<pre>';
    #var_dump($var);
	#print '</pre>';
}
function backtrace() {
	$debug_array = debug_backtrace();
	$counter = count($debug_array);
	for($tmp_counter = 0; $tmp_counter != $counter; ++$tmp_counter) {
	?>
 <table width="558" height="116" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
    <td height="38" bgcolor="#D6D7FC"><font color="#000000">function <font color="#FF3300"><?
    echo($debug_array[$tmp_counter]["function"]);?>(</font> <font color="#2020F0">
    <?php
    //count how many args a there
    $args_counter = count($debug_array[$tmp_counter]["args"]);
    //print them
    for($tmp_args_counter = 0; $tmp_args_counter != $args_counter; ++$tmp_args_counter) {
		echo($debug_array[$tmp_counter]["args"][$tmp_args_counter]);

		if(($tmp_args_counter + 1) != $args_counter) {
			echo(", ");
		} else {
			echo(" ");
		}
    }
    ?></font><font color="#FF3300">)</font></font></td></tr><tr>
    <td bgcolor="#5F72FA"><font color="#FFFFFF">{</font><br>
      <font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;file:
       <?php echo($debug_array[$tmp_counter]["file"]);?></font><br>
        <font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;line:
       <?php echo($debug_array[$tmp_counter]["line"]);?></font><br>
	<font color="#FFFFFF">}</font></td></tr></table>
       <?php
	   if(($tmp_counter + 1) != $counter) {
		   echo("<br>was called by:<br>");
	   }
	}
	//exit();
}

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
