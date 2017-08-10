<?php
/**
 * A sieve script that handles mail forwarding to a specific address.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  SkolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://www.kolab.org
 */

/**
 * A sieve script that handles mail forwarding to a specific address.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  SkolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://www.kolab.org
 */
class SkolabAdmin_Sieve_Segment_Forward
extends SkolabAdmin_Sieve_Segment
{
    /**
     * The segment type.
     *
     * @var string
     */
    protected $type = 'forward';

    /**
     * The forwarding address.
     *
     * @var string
     */
    private $_forward_address = '';

    /**
     * Should mails be kept on the server after forwarding?
     *
     * @var string
     */
    private $_keep_on_server = true;

    /**
     * Constructor.
     *
     * @param string $script The current script segment
     */
    public function __construct($script = '')
    {
        $this->template = 'if allof (%s' . "\r\n" .
            ') {' . "\r\n" .
            '%s%s' . "\r\n" .
            '}' . "\r\n";
        parent::__construct($script);
    }

    /**
     * Retrieve the forwarding address this script will deliver to.
     *
     * @return string The forwarding address.
     */
    public function getForwardAddress()
    {
        return $this->_forward_address;
    }

    /**
     * Set the forwarding address this script will deliver to.
     *
     * @param string $folder The forward address.
     *
     * @return NULL
     */
    public function setForwardAddress($address)
    {
        if (empty($address)) {
            throw new Exception('Please enter a valid e-mail address!');
        }
        $this->_forward_address = $address;
    }

    /**
     * Should the messages be kept on the server after forwarding?
     *
     * @return bool True if the messages should be kept.
     */
    public function getKeepOnServer()
    {
        return $this->_keep_on_server;
    }

    /**
     * Set if the messages should be kept on the server after forwarding.
     *
     * @param boolean $keep True if the messages should be kept.
     *
     * @return NULL
     */
    public function setKeepOnServer($keep)
    {
        $this->_keep_on_server = $keep;
    }

    public function getArguments()
    {
        $address = $this->getForwardAddress();
        if (!empty($address)) {
            $address = 'redirect "' . $address . '";';
        } else {
            $address = '';
        }
        return array(
            ($this->isActive()) ? 'true ## forward enabled' : 'false ## forward disabled',
            $address,
            ($this->getKeepOnServer()) ? '' : ' stop;'
        );
    }

    public function parseArguments($script)
    {
        $this->parseForwardAddress($script);
        $this->parseKeepOnServer($script);
    }

    public function parseForwardAddress($script)
    {
        if (preg_match("/redirect \"([^\"]*)\"/s", $script, $regs)) {
            $this->_forward_address = $regs[1];
        }
    }

    public function parseKeepOnServer($script)
    {
        if (preg_match('/keep;/s', $script, $regs)) {
            $this->_keep_on_server = true;
        } else if (preg_match('/stop;/s', $script, $regs)) {
            $this->_keep_on_server = false;
        } else if (preg_match('/require/s', $script, $regs)) {
            // The unused "require" statement provides the information that it
            // is an old script variant.
            $this->_keep_on_server = false;
        } else {
            $this->_keep_on_server = true;
        }
    }
}