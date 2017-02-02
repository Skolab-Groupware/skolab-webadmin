<?php
/**
 * Test the webadmin code.
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
require_once dirname(__FILE__) . '/../../../lib/KolabAdmin/Ldap.php';

/**
 * Test the webadmin code.
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
class KolabAdmin_Unit_BaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SESSION = array();
        require '/kolab/etc/kolab/session_vars.php';
        $this->ldap = new KolabLdap();
        $this->ldap->bind(
            'cn=manager,cn=internal,' . $_SESSION['base_dn'],
            'test'
        );
        $this->cleanup = array();
    }

    public function tearDown()
    {
        foreach ($this->cleanup as $dn) {
            if (!$this->ldap->deleteObject($dn, true)) {
                throw new Exception('Deleting ' . $dn . ' failed!');
            }
        }
    }

    public function testCountmailReturnsZeroOnNonExistantMail()
    {
        $this->assertEquals(
            0,
            $this->ldap->countMail(
                $_SESSION['base_dn'],
                'certainly@does@not@exist'
            )
        );
    }

    public function testCountmailReturnsOneOnExistingMail()
    {
        $this->_add($this->_getTestUser());
        $this->assertEquals(
            1,
            $this->ldap->countMail(
                $_SESSION['base_dn'],
                'kolabadmin.test.@' . $_SESSION['fqdnhostname']
            )
        );
    }

    public function testCountmailReturnsOneOnExistingMailWithEscapedDnCharacters()
    {
        $this->_add($this->_getTestUser(',=,'));
        $this->assertEquals(
            1,
            $this->ldap->countMail(
                $_SESSION['base_dn'],
                'kolabadmin.test.,=,@' . $_SESSION['fqdnhostname']
            )
        );
    }

    public function testCountmailReturnsZeroOnExistingMailWithEscapedDnCharactersIfDnExcluded()
    {
        $this->_add($this->_getTestUser(',=,'));
        $this->assertEquals(
            0,
            $this->ldap->countMail(
                $_SESSION['base_dn'],
                'kolabadmin.test.,=,@' . $_SESSION['fqdnhostname'],
                'cn=' . $this->ldap->dn_escape('KolabAdmin TestUser,=,') . ',' . $_SESSION['base_dn']
            )
        );
    }

    public function testAddingObjectIsSuccessful()
    {
        $this->_add($this->_getTestUser());
    }

    private function _add($object)
    {
        if ($this->ldap->add($object['dn'], $object['attributes'])) {
            $this->cleanup[] = $object['dn'];
        } else {
            throw new Exception('Adding ' . $object['dn'] . ' failed!');
        }
    }

    private function _getTestUser($id = null)
    {
        $cn = 'KolabAdmin TestUser' . $id;
        return array(
            'dn' => 'cn=' . $this->ldap->dn_escape($cn) . ',' . $_SESSION['base_dn'],
            'attributes' => array(
                'objectClass'  => array(
                    'top', 'inetOrgPerson', 'kolabInetOrgPerson'
                ),
                'userPassword' => 'test',
                'sn'           => 'TestUser' . $id,
                'cn'           => $cn,
                'givenName'    => 'KolabAdmin',
                'mail'         => 'kolabadmin.test.' . $id . '@' . $_SESSION['fqdnhostname'],
                'uid'          => 'kolabadmin.test.' . $id,
            )
        );
    }
}

/** short circuit the debug function. */
function debug() {};