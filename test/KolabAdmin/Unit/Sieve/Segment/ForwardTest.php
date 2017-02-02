<?php
/**
 * Test the sieve script forward segment.
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
require_once dirname(__FILE__) . '/../../../Autoload.php';

/**
 * Test the sieve script forward segment.
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
class KolabAdmin_Unit_Sieve_Segment_ForwardTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sieve = $this->getMock('Net_Sieve');
        $this->manager = new KolabAdmin_Sieve($this->sieve);
    }

    public function testSieveHandlerAllowsFetchingForwardSegment()
    {
        $this->assertType(
            'KolabAdmin_Sieve_Segment_Forward',
            $this->manager->fetchForwardSegment()
        );
    }

    public function testSieveHandlerIndicatesThatTheForwardSegmentIsActiveIfAnOldActiveScriptWasFound()
    {
        $this->_provideActiveScript(
            'kolab-forward.siv', $this->_getOldForwardScript()
        );
        $this->assertTrue(
            $this->manager->fetchForwardSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheForwardSegmentIsInactiveIfAnOldInactiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab-forward.siv', $this->_getOldForwardScript()
        );
        $this->assertFalse(
            $this->manager->fetchForwardSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheForwardSegmentIsActiveIfANewActiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab.siv', $this->_getForwardScript()
        );
        $this->assertTrue(
            $this->manager->fetchForwardSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveForwardSegmentIfNoActiveScriptWasFound()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array()));
        $this->assertFalse(
            $this->manager->fetchForwardSegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveForwardSegmentIfAnInactiveScriptWasFound()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getForwardScript('test@example.com', false)));
        $this->assertFalse(
            $this->manager->fetchForwardSegment()->isActive()
        );
    }

    public function testSieveHandlerAllowsActivatingForwardSegment()
    {
        $segment = $this->manager->fetchForwardSegment();
        $segment->setActive();
        $segment->setForwardAddress('somebody@example.com');
        $this->assertEquals(
            $this->_getForwardScript('somebody@example.com'),
            $segment->generate()
        );
    }

    public function testSieveHandlerAllowsDeactivatingForwardSegment()
    {
        $segment = $this->manager->fetchForwardSegment();
        $segment->setInactive();
        $segment->setForwardAddress('somebody@example.com');
        $this->assertEquals(
            $this->_getForwardScript('somebody@example.com', false),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentForwardProvidesForwardAddress()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-forward.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-forward.siv')
            ->will($this->returnValue($this->_getOldForwardScript()));
        $segment = $this->manager->fetchForwardSegment();
        $this->assertEquals('test@example.org', $segment->getForwardAddress());
    }

    public function testSieveSegmentForwardProvidesForwardAddress()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getForwardScript('dummy@example.com', false)));
        $segment = $this->manager->fetchForwardSegment();
        $this->assertEquals('dummy@example.com', $segment->getForwardAddress());
    }

    public function testSieveSegmentForwardAllowsSettingForwardAddress()
    {
        $segment = $this->manager->fetchForwardSegment();
        $segment->setActive();
        $segment->setForwardAddress('dummy@example.com');
        $this->assertEquals(
            $this->_getForwardScript('dummy@example.com'),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentForwardProvidesKeepOnServerAttributeTrueIfSetToTrue()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-forward.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-forward.siv')
            ->will($this->returnValue($this->_getOldForwardScript()));
        $segment = $this->manager->fetchForwardSegment();
        $this->assertTrue($segment->getKeepOnServer());
    }

    public function testOldSieveSegmentForwardProvidesKeepOnServerAttributeFalseIfSetToFalse()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab-forward.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab-forward.siv')
            ->will($this->returnValue($this->_getOldForwardScript(false)));
        $segment = $this->manager->fetchForwardSegment();
        $this->assertFalse($segment->getKeepOnServer());
    }

    public function testSieveSegmentForwardProvidesKeepOnServerAttributeTrueIfSetToTrue()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array('kolab.siv')));
        $this->sieve->expects($this->once())
            ->method('getScript')
            ->with('kolab.siv')
            ->will($this->returnValue($this->_getForwardScript('dummy@example.com', false, true)));
        $segment = $this->manager->fetchForwardSegment();
        $this->assertTrue($segment->getKeepOnServer());
    }

    public function testSieveSegmentForwardAllowsSettingKeepOnServerToTrue()
    {
        $segment = $this->manager->fetchForwardSegment();
        $segment->setActive();
        $segment->setForwardAddress('dummy@example.com');
        $segment->setKeepOnServer(true);
        $this->assertEquals(
            $this->_getForwardScript('dummy@example.com', true, true),
            $segment->generate()
        );
    }

    public function testSieveSegmentForwardAllowsSettingKeepOnServerToFalse()
    {
        $segment = $this->manager->fetchForwardSegment();
        $segment->setActive();
        $segment->setKeepOnServer(false);
        $segment->setForwardAddress('dummy@example.com');
        $this->assertEquals(
            $this->_getForwardScript('dummy@example.com', true, false),
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

    private function _getOldForwardScript($keep_on_server = true)
    {
        return 'require "fileinto";' . "\r\n" .
            'redirect "test@example.org";' . (($keep_on_server) ? ' keep;' : '') . "\r\n";
    }

    private function _getForwardScript($address = 'test@example.com', $active = true, $keep_on_server = true)
    {
        if (!empty($address)) {
            $address = 'redirect "' . $address . '";';
        } else {
            $address = '';
        }
        $script = 'if allof (' . (($active) ? 'true ## forward enabled' : 'false ## forward disabled') . "\r\n" .
            ') {' . "\r\n" .
            $address . (($keep_on_server) ? '' : ' stop;') . "\r\n" .
            '}' . "\r\n";
        if (!$active) {
            $script = preg_replace('/^(.)/m', '#$1', $script);
        }
        return '### SEGMENT START FORWARD ' . (($active) ? 'ENABLED' : 'DISABLED') . "\r\n" .
            $script .
            '### SEGMENT END FORWARD' . "\r\n";
    }
}