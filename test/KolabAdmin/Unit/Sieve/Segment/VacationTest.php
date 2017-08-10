<?php
/**
 * Test the sieve script vacation segment.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  SkolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=SkolabAdmin
 */

/**
 * Require the tested classes.
 */
require_once dirname(__FILE__) . '/../../../Autoload.php';

/**
 * Test the sieve script vacation segment.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Kolab
 * @package  SkolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=SkolabAdmin
 */
class SkolabAdmin_Unit_Sieve_Segment_VacationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sieve = $this->getMock('Net_Sieve');
        $this->manager = new SkolabAdmin_Sieve($this->sieve);
    }

    public function testSieveHandlerAllowsFetchingVacationSegment()
    {
        $this->assertType(
            'SkolabAdmin_Sieve_Segment_Vacation',
            $this->manager->fetchVacationSegment()
        );
    }

    public function testSieveHandlerIndicatesThatTheVacationSegmentIsActiveIfAnOldActiveScriptWasFound()
    {
        $this->_provideActiveScript(
            'kolab-vacation.siv', $this->_getOldVacationScript()
        );
        $this->assertTrue(
            $this->manager->fetchVacationSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheVacationSegmentIsInactiveIfAnOldInactiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab-vacation.siv', $this->_getOldVacationScript()
        );
        $this->assertFalse(
            $this->manager->fetchVacationSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheVacationSegmentIsActiveIfANewActiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab.siv', $this->_getVacationScript()
        );
        $this->assertTrue(
            $this->manager->fetchVacationSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveVacationSegmentIfNoActiveScriptWasFound()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array()));
        $this->assertFalse(
            $this->manager->fetchVacationSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveVacationSegmentIfAnInactiveScriptWasFound()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(false)));
        $this->assertFalse(
            $this->manager->fetchVacationSegment()->isActive()
        );
    }

    public function testSieveHandlerAllowsActivatingVacationSegment()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $this->assertEquals(
            $this->_getVacationScript(),
            $segment->generate()
        );
    }

    public function testSieveHandlerAllowsDeactivatingVacationSegment()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setInactive();
        $this->assertEquals(
            $this->_getVacationScript(false),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentVacationProvidesResponse()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript("REPLY\r\nLINE2\r\n")));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals("REPLY\r\nLINE2", $segment->getResponse());
    }

    public function testSieveSegmentVacationProvidesResponse()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, "REPLY\r\nLINE2\r\n")));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals("REPLY\r\nLINE2", $segment->getResponse());
    }

    public function testSieveSegmentVacationAllowsSettingResponse()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setResponse("REPLY\r\nLINE2");
        $this->assertEquals(
            $this->_getVacationScript(true, "REPLY\r\nLINE2"),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentVacationProvidesResendAfter()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript('', '', false, array(), 8)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals(8, $segment->getResendAfter());
    }

    public function testSieveSegmentVacationProvidesResendAfter()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, '', '', false, array(), 12)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals(12, $segment->getResendAfter());
    }

    public function testSieveSegmentVacationAllowsSettingResendAfter()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setResendAfter(9);
        $this->assertEquals(
            $this->_getVacationScript(true, '', '', false, array(), 9),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentVacationProvidesDomain()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript('REPLY', 'example.com')));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals('example.com', $segment->getDomain());
    }

    public function testSieveSegmentVacationProvidesDomain()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, 'REPLY', 'example.com')));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals('example.com', $segment->getDomain());
    }

    public function testSieveSegmentVacationAllowsSettingDomain()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setDomain('example.org');
        $this->assertEquals(
            $this->_getVacationScript(true, '', 'example.org'),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentVacationProvidesRecipientAddresses()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript('', '', false, array('1@example.org', '2@example.org'))));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals(array('1@example.org', '2@example.org'), $segment->getRecipientAddresses());
    }

    public function testSieveSegmentVacationProvidesRecipientAddresses()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, '', '', false, array('1@example.org', '2@example.org'))));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertEquals(array('1@example.org', '2@example.org'), $segment->getRecipientAddresses());
    }

    public function testSieveSegmentVacationAllowsSettingRecipientAddresses()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setRecipientAddresses(array('1@example.com', '2@example.com'));
        $this->assertEquals(
            $this->_getVacationScript(true, '', '', false, array('1@example.com', '2@example.com')),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentVacationProvidesReactToSpamAttributeTrueIfSetToTrue()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript('', '', true)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertTrue($segment->getReactToSpam());
    }

    public function testOldSieveSegmentVacationProvidesReactToSpamAttributeFalseIfSetToFalse()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-vacation.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-vacation.siv')
            ->will($this->returnValue($this->_getOldVacationScript('', '', false)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertFalse($segment->getReactToSpam());
    }

    public function testSieveSegmentVacationProvidesReactToSpamAttributeFalseIfSetToFalse()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, '', '', false)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertFalse($segment->getReactToSpam());
    }

    public function testSieveSegmentVacationProvidesReactToSpamAttributeTrueIfSetToTrue()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getVacationScript(true, '', '', true)));
        $segment = $this->manager->fetchVacationSegment();
        $this->assertTrue($segment->getReactToSpam());
    }

    public function testSieveSegmentVacationAllowsSettingReactToSpamToTrue()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setReactToSpam(true);
        $this->assertEquals(
            $this->_getVacationScript(true, '', '', true),
            $segment->generate()
        );
    }

    public function testSieveSegmentVacationAllowsSettingReactToSpamToFalse()
    {
        $segment = $this->manager->fetchVacationSegment();
        $segment->setActive();
        $segment->setReactToSpam(false);
        $this->assertEquals(
            $this->_getVacationScript(true, '', '', false),
            $segment->generate()
        );
    }

    private function _provideInactiveScript($name, $script)
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array($name)));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with($name)
            ->will($this->returnValue($script));
    }

    private function _provideActiveScript($name, $script)
    {
        $this->_provideInactiveScript($name, $script);
        $this->sieve->expects($this->once())
            ->method('getActive')
            ->will($this->returnValue($name));
    }

    private function _getOldVacationScript($text = '', $domain = null, $react_to_spam = false, $addresses = array(), $days = 7)
    {
        return 'require "vacation";' . "\r\n" .
            ((empty($domain)) ? '' : 'if not address :domain :contains "From" "' . $domain . '" { keep; stop; }' . "\r\n") .
            (($react_to_spam) ? '' : 'if header :contains "X-Spam-Flag" "YES" { keep; stop; }' . "\r\n") .
            'vacation :addresses [ "' . join('", "', $addresses) . '" ] :days ' . $days . ' text:' . "\r\n" .
            $text . "\r\n" .
            '.' . "\r\n" .
            ';' . "\r\n";
    }

    private function _getVacationScript($active = true, $text = '', $domain = null, $react_to_spam = false, $addresses = array(), $days = 7)
    {
        if (empty($text)) {
            $text = sprintf(
                _("I am out of office until %s.\r\n").
                _("In urgent cases, please contact Mrs. <vacation replacement>\r\n\r\n").
                _("email: <email address of vacation replacement>\r\n").
                _("phone: +49 711 1111 11\r\n").
                _("fax.:  +49 711 1111 12\r\n\r\n").
                _("Yours sincerely,\r\n").
                _("-- \r\n").
                _("<enter your name and email address here>"),
                strftime(_('%x'))
            );
        }

        if (!empty($addresses)) {
            $addresses = ':addresses [ "' . join('", "', $addresses) . '" ]';
        } else {
            $addresses = '';
        }

        $script = 'if allof (' . (($active) ? 'true, ## vacation enabled' : 'false, ## vacation disabled') . "\r\n" .
            ((empty($domain)) ? 'true,' : 'address :domain :contains "From" "' . $domain . '",') . "\r\n" .
            (($react_to_spam) ? 'true' : 'not header :contains "X-Spam-Flag" "YES"') . ') {' . "\r\n" .
            'vacation ' . $addresses . ' :days ' . $days . ' text:' . "\r\n" .
            $text . "\r\n" .
            '.' . "\r\n" .
            ';' . "\r\n" .
            '}' . "\r\n";

        if (!$active) {
            $script = preg_replace('/^(.)/m', '#$1', $script);
        }

        return '### SEGMENT START VACATION ' . (($active) ? 'ENABLED' : 'DISABLED') . "\r\n" .
            $script .
            '### SEGMENT END VACATION' . "\r\n";
    }
}