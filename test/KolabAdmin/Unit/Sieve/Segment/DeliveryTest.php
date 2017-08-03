<?php
/**
 * Test the sieve script delivery segment.
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
 * Test the sieve script delivery segment.
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
class KolabAdmin_Unit_Sieve_Segment_DeliveryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sieve = $this->getMock('Net_Sieve');
        $this->manager = new KolabAdmin_Sieve($this->sieve);
    }

    public function testSieveHandlerAllowsFetchingDeliverySegment()
    {
        $this->assertType(
            'KolabAdmin_Sieve_Segment_Delivery',
            $this->manager->fetchDeliverySegment()
        );
    }

    public function testSieveHandlerIndicatesThatTheDeliverySegmentIsActiveIfAnOldActiveScriptWasFound()
    {
        $this->_provideActiveScript(
            'kolab-deliver.siv', $this->_getOldDeliveryScript()
        );
        $this->assertTrue(
            $this->manager->fetchDeliverySegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheDeliverySegmentIsInactiveIfAnOldInactiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab-deliver.siv', $this->_getOldDeliveryScript()
        );
        $this->assertFalse(
            $this->manager->fetchDeliverySegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesThatTheDeliverySegmentIsActiveIfANewActiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab.siv', $this->_getDeliveryScript()
        );
        $this->assertTrue(
            $this->manager->fetchDeliverySegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveDeliverySegmentIfNoActiveScriptWasFound()
    {
        $this->sieve->expects($this->once())
            ->method('listScripts')
            ->will($this->returnValue(array()));
        $this->assertFalse(
            $this->manager->fetchDeliverySegment()->isActive()
        );
    }

    public function testSieveHandlerIndicatesAnInactiveDeliverySegmentIfAnInactiveScriptWasFound()
    {
        $this->_provideInactiveScript(
            'kolab.siv', $this->_getDeliveryScript('Test', false)
        );
        $this->assertFalse(
            $this->manager->fetchDeliverySegment()->isActive()
        );
    }

    public function testSieveHandlerAllowsActivatingDeliverySegment()
    {
        $segment = $this->manager->fetchDeliverySegment();
        $segment->setActive();
        $this->assertEquals(
            $this->_getDeliveryScript('Inbox'),
            $segment->generate()
        );
    }

    public function testSieveHandlerAllowsDeactivatingDeliverySegment()
    {
        $segment = $this->manager->fetchDeliverySegment();
        $segment->setInactive();
        $this->assertEquals(
            $this->_getDeliveryScript('Inbox', false),
            $segment->generate()
        );
    }

    public function testOldSieveSegmentDeliveryProvidesDeliveryFolder()
    {
        $this->_provideActiveScript(
            'kolab-deliver.siv', $this->_getOldDeliveryScript()
        );
        $segment = $this->manager->fetchDeliverySegment();
        $this->assertEquals('Test', $segment->getDeliveryFolder());
    }

    public function testSieveSegmentDeliveryProvidesDeliveryFolder()
    {
        $this->_provideInactiveScript(
            'kolab.siv', $this->_getDeliveryScript('Test', false)
        );
        $segment = $this->manager->fetchDeliverySegment();
        $this->assertEquals('Test', $segment->getDeliveryFolder());
    }

    public function testSieveSegmentDeliveryAllowsSettingDeliveryFolder()
    {
        $segment = $this->manager->fetchDeliverySegment();
        $segment->setActive();
        $segment->setDeliveryFolder('Dummy');
        $this->assertEquals(
            $this->_getDeliveryScript('Dummy'),
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

    private function _getOldDeliveryScript()
    {
        return 'require "fileinto";' . "\r\n" .
            'if header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"] {' . "\r\n" .
            'fileinto "INBOX/Test";' . "\r\n" .
            '}' . "\r\n";
    }

    private function _getDeliveryScript($box = 'Test', $active = true)
    {
        $script = 'if allof (' . (($active) ? 'true, ## delivery enabled' : 'false, ## delivery disabled') . "\r\n" .
            'header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"]) {' . "\r\n" .
            'fileinto "INBOX/' . $box . '";' . "\r\n" .
            '}' . "\r\n";
        if (!$active) {
            $script = preg_replace('/^(.)/m', '#$1', $script);
        }
        return '### SEGMENT START DELIVERY ' . (($active) ? 'ENABLED' : 'DISABLED') . "\r\n" .
            $script .
            '### SEGMENT END DELIVERY' . "\r\n";
    }
}