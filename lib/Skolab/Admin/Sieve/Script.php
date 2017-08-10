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

class SkolabAdmin_Sieve_Script {

    // Funny multiline string escaping in Sieve
    static function dotstuff( $str ) {
        return str_replace( "\n.", "\n..", $str );
    }

    static function undotstuff( $str ) {
        return str_replace( "\n..", "\n.", $str );
    }

    static function getDeliverFolder( $script ) {
        $inbox = false;
        if( preg_match("/fileinto \"INBOX\/([^\"]*)\";/", $script, $regs ) ) {
            $inbox = $regs[1];
        }
        return $inbox;
    }

    static function getVacationAddresses( $script ) {
        $addresses = false;
        if( preg_match("/:addresses \\[([^\\]]*)\\]/s", $script, $regs ) ) {
            $tmp = preg_split('/,/', $regs[1] );
            $addresses = array();
            foreach( $tmp as $a ) {
                if( preg_match('/^ *"(.*)" *$/', $a, $regs ) ) $addresses[] = $regs[1];
                else $addresses[] = $a;
            }
        }
        return $addresses;
    }

    static function getMailDomain( $script ) {
        $maildomain = false;
        if( preg_match( '/address :domain :contains "From" "(.*)"/i', $script, $regs ) ) {
            $maildomain = $regs[1];
        }
        return $maildomain;
    }

    static function getReactToSpam( $script ) {
        $spam = false;
        if( preg_match('/header :contains "X-Spam-Flag" "YES"/i', $script ) ) {
            $spam = true;
        }
        return $spam;
    }

    static function getVacationDays( $script ) {
        $days = false;
        if( preg_match("/:days ([0-9]+)/s", $script, $regs ) ) {
            $days = $regs[1];
        }
        return $days;
    }

    static function getVacationText( $script ) {
        $text = false;
        if( preg_match("/text:(.*\r\n)\\.\r\n/s", $script, $regs ) ) {
            $text = $regs[1];
            $text = str_replace( '\n', "\r\n", $text );
            $text = SkolabAdmin_Sieve_Script::undotstuff($text);
        }
        return $text;
    }

    static function getForwardAddress( $script ) {
        $address = false;
        if( preg_match("/redirect \"([^\"]*)\"/s", $script, $regs ) ) {
            $address = $regs[1];
        }
        return $address;
    }

    static function getKeepOnServer( $script ) {
        return preg_match('/"; keep;/', $script, $regs ) > 0;
    }

    static function getScriptInfo($script) {
        return array('maildomain'        => SkolabAdmin_Sieve_Script::getMailDomain($script),
                     'vacationaddresses' => SkolabAdmin_Sieve_Script::getVacationAddresses($script),
                     'days'              => SkolabAdmin_Sieve_Script::getVacationDays($script),
                     'reacttospam'       => SkolabAdmin_Sieve_Script::getReactToSpam($script),
                     'vacationtext'      => SkolabAdmin_Sieve_Script::getVacationText($script),
                     'vacationenabled'   => SkolabAdmin_Sieve_Script::isVacationEnabled($script),
                     'deliveryfolder'    => SkolabAdmin_Sieve_Script::getDeliverFolder($script),
                     'deliveryenabled'   => SkolabAdmin_Sieve_Script::isDeliveryEnabled($script));
    }

    static function isDeliveryEnabled($script) {
        return preg_match('/## delivery enabled/', $script, $regs )>0;
    }

    static function isVacationEnabled($script) {
        return preg_match('/## vacation enabled/', $script, $regs )>0;
    }

    static function createScript($scriptinfo) {
        $tests = array();
        if( $scriptinfo['vacationenabled'] ) {
            $tests[] = "## vacation enabled\r\ntrue";
        } else {
            $tests[] = "## vacation disabled\r\nnot true";
        }

        if(!empty($scriptinfo['maildomain'])) {
            $tests[] = "address :domain :contains \"From\" \"".$scriptinfo['maildomain']."\"";
        }
        if($scriptinfo['reacttospam']) {
            $tests[] = "not header :contains \"X-Spam-Flag\" \"YES\"";
        }
        $script =
            "require \"vacation\";\r\n\r\n".
            "require \"fileinto\";\r\n\r\n".
            "if allof (".join(",\r\n",$tests).") {\r\n".
            "  vacation :addresses [ \"".join('", "', $scriptinfo['vacationaddresses'] )."\" ] :days ".
            $scriptinfo['days']." text:\r\n".
            SkolabAdmin_Sieve_Script::dotstuff(trim($scriptinfo['vacationtext']))."\r\n.\r\n;\r\n}\r\n";
        if($scriptinfo['deliveryfolder']) {
            if($scriptinfo['deliveryenabled']) {
                $script .= "if allof (true, ## delivery enabled\r\n";
            } else {
                $script .= "if allof (not true, ## delivery disabled\r\n";
            }
            $script .= "header :contains [\"X-Kolab-Scheduling-Message\"] [\"FALSE\"]) {\r\nfileinto \"INBOX/".
                $scriptinfo['deliveryfolder']."\";\r\n}\r\n";
        }
        return $script;
    }
};
