<?php
/**
 * Test the sieve utilities provided by the webadmin.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=KolabAdmin
 */

/**
 * Require the tested classes.
 */
require_once dirname(__FILE__) . '/../Autoload.php';

/**
 * Test the sieve utilities provided by the webadmin.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=KolabAdmin
 */
class KolabAdmin_Unit_SieveTest extends PHPUnit_Framework_TestCase
{
    public function testMultiLineDotEscaping()
    {
        $this->assertEquals("abc\n..xyz", KolabAdmin_Sieve_Script::dotstuff("abc\n.xyz"));
    }

    public function testMultiLineDotUnscaping()
    {
        $this->assertEquals("abc\n.xyz", KolabAdmin_Sieve_Script::undotstuff("abc\n..xyz"));
    }

    public function testGetDeliveryFolder()
    {
        $this->assertEquals('Test', KolabAdmin_Sieve_Script::getDeliverFolder($this->_getScript()));
    }

    public function testEmptyDeliveryFolder()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getDeliverFolder(''));
    }

    public function testGetVacationAddresses()
    {
        $this->assertEquals(
            array('a@example.com', 'b@example.com'),
            KolabAdmin_Sieve_Script::getVacationAddresses($this->_getScript())
        );
    }

    public function testGetVacationAddressesWithSingleAddress()
    {
        $this->assertEquals(
            array('a@example.com'),
            KolabAdmin_Sieve_Script::getVacationAddresses(
                'vacation :addresses [a@example.com]'
            )
        );
    }

    public function testEmptyVacationAddresses()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getVacationAddresses(''));
    }

    public function testGetMailDomain()
    {
        $this->assertEquals('example.org', KolabAdmin_Sieve_Script::getMailDomain($this->_getScript()));
    }

    public function testEmptyMailDomain()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getMailDomain(''));
    }

    public function testGetReactToSpam()
    {
        $this->assertTrue(KolabAdmin_Sieve_Script::getReactToSpam($this->_getScript()));
    }

    public function testEmptyReactToSpam()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getReactToSpam(''));
    }

    public function testGetVacationDays()
    {
        $this->assertEquals(60, KolabAdmin_Sieve_Script::getVacationDays($this->_getScript()));
    }

    public function testEmptyVacationDays()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getVacationDays(''));
    }

    public function testGetVacationText()
    {
        $this->assertEquals("\r\nI'm on vacation\r\n", KolabAdmin_Sieve_Script::getVacationText($this->_getScript()));
    }

    public function testEmptyVacationText()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getVacationText(''));
    }

    public function testGetForwardAddress()
    {
        $this->assertEquals("test@example.com", KolabAdmin_Sieve_Script::getForwardAddress($this->_getScript()));
    }

    public function testEmptyForwardAddress()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getForwardAddress(''));
    }

    public function testGetKeepOnServer()
    {
        $this->assertTrue(KolabAdmin_Sieve_Script::getKeepOnServer($this->_getScript()));
    }

    public function testEmptyKeepOnServer()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::getKeepOnServer(''));
    }

    public function testIsDeliveryEnabled()
    {
        $this->assertTrue(KolabAdmin_Sieve_Script::isDeliveryEnabled($this->_getScript()));
    }

    public function testDeliveryNotEnables()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::isDeliveryEnabled(''));
    }

    public function testIsVacationEnabled()
    {
        $this->assertTrue(KolabAdmin_Sieve_Script::isVacationEnabled($this->_getScript()));
    }

    public function testVacationNotEnables()
    {
        $this->assertFalse(KolabAdmin_Sieve_Script::isVacationEnabled(''));
    }

    public function testGetScriptInfo()
    {
        $this->assertEquals(
            array(
                'maildomain'        => 'example.org',
                'vacationaddresses' => array('a@example.com', 'b@example.com'),
                'days'              => '60',
                'reacttospam'       => true,
                'vacationtext'      => "\r\nI'm on vacation\r\n",
                'vacationenabled'   => true,
                'deliveryfolder'    => 'Test',
                'deliveryenabled'   => true
            ),
            KolabAdmin_Sieve_Script::getScriptInfo($this->_getScript())
        );
    }

    public function testGetEmptyScriptInfo()
    {
        $this->assertEquals(
            array(
                'maildomain'        => false,
                'vacationaddresses' => false,
                'days'              => false,
                'reacttospam'       => false,
                'vacationtext'      => false,
                'vacationenabled'   => false,
                'deliveryfolder'    => false,
                'deliveryenabled'   => false
            ),
            KolabAdmin_Sieve_Script::getScriptInfo('')
        );
    }

    public function testCreateScript()
    {
        $this->assertEquals(
            $this->_getScript2(),
            KolabAdmin_Sieve_Script::createScript(
                array(
                    'maildomain'        => 'example.org',
                    'vacationaddresses' => array('a@example.com', 'b@example.com'),
                    'days'              => '60',
                    'reacttospam'       => true,
                    'vacationtext'      => "\r\nI'm on vacation\r\n",
                    'vacationenabled'   => true,
                    'deliveryfolder'    => 'Test',
                    'deliveryenabled'   => true
                )
            )
        );
    }

    public function testCreateScriptGetScriptInfo()
    {
        $info = array(
            'maildomain'        => 'example.org',
            'vacationaddresses' => array('a@example.com', 'b@example.com'),
            'days'              => '60',
            'reacttospam'       => true,
            'vacationtext'      => "\r\nI'm on vacation\r\n",
            'vacationenabled'   => true,
            'deliveryfolder'    => 'Test',
            'deliveryenabled'   => true
        );
        $this->assertEquals(
            $info,
            KolabAdmin_Sieve_Script::getScriptInfo(
                KolabAdmin_Sieve_Script::createScript(
                    $info
                )
            )
        );
    }

    private function _getScript()
    {
        return
            'fileinto "INBOX/Test";'
            . '## delivery enabled'
            . '## vacation enabled'
            . 'redirect "test@example.com"; keep;"'
            . 'if header :contains "X-Spam-Flag" "YES" { keep; stop; }' . "\r\n"
            . 'if not address :domain :contains "From" "example.org" { keep; stop; }' . "\r\n"
            . 'vacation :addresses [ "a@example.com", "b@example.com" ] :days 60 text:' . "\r\n"
            . 'I\'m on vacation' . "\r\n.\r\n;\r\n\r\n";
    }

    private function _getScript2()
    {
        return
            'require "vacation";' . "\r\n" .
            '' . "\r\n" .
            'require "fileinto";' . "\r\n" .
            '' . "\r\n" .
            'if allof (## vacation enabled' . "\r\n" .
            'true,' . "\r\n" .
            'address :domain :contains "From" "example.org",' . "\r\n" .
            'not header :contains "X-Spam-Flag" "YES") {' . "\r\n" .
            '  vacation :addresses [ "a@example.com", "b@example.com" ] :days 60 text:' . "\r\n" .
            'I\'m on vacation' . "\r\n" .
            '.' . "\r\n" .
            ';' . "\r\n" .
            '}' . "\r\n" .
            'if allof (true, ## delivery enabled' . "\r\n" .
            'header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"]) {' . "\r\n" .
            'fileinto "INBOX/Test";' . "\r\n" .
            '}' . "\r\n";
    }

    private function _getOldDeliveryScript()
    {
        return 'require "fileinto";' . "\r\n" .
            'if header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"] {' . "\r\n" .
            'fileinto "INBOX/Test";' . "\r\n" .
            '}' . "\r\n";
    }

    private function _getActiveDeliveryScript()
    {
        return 'if allof (true, ## delivery enabled' . "\r\n" .
            'header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"]) {' . "\r\n" .
            'fileinto "INBOX/Test";' . "\r\n" .
            '}' . "\r\n";
    }

    private function _getInactiveDeliveryScript()
    {
        return 'if allof (false, ## delivery disabled' . "\r\n" .
            'header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"]) {' . "\r\n" .
            'fileinto "INBOX/Test";' . "\r\n" .
            '}' . "\r\n";
    }
}