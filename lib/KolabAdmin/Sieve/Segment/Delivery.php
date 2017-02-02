<?php
/**
 * A sieve script that handles mail delivery to a specific folder.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://www.kolab.org
 */

/**
 * A sieve script that handles mail delivery to a specific folder.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://www.kolab.org
 */
class KolabAdmin_Sieve_Segment_Delivery
extends KolabAdmin_Sieve_Segment
{
    /**
     * The segment type
     *
     * @var string
     */
    protected $type = 'delivery';

    /**
     * The folder to deliver mails to.
     *
     * @var string
     */
    private $_delivery_folder = 'Inbox';

    /**
     * Constructor.
     *
     * @param string $script The current script segment
     */
    public function __construct($script = '')
    {
        $this->template = 'if allof (%s' . "\r\n" .
            'header :contains ["X-Kolab-Scheduling-Message"] ["FALSE"]) {' . "\r\n" .
            'fileinto "INBOX/%s";' . "\r\n" .
            '}' . "\r\n";
        parent::__construct($script);
    }

    /**
     * Retrieve the delivery folder this script will deliver to.
     *
     * @return string The delivery folder.
     */
    public function getDeliveryFolder()
    {
        return $this->_delivery_folder;
    }

    /**
     * Set the delivery folder this script will deliver to.
     *
     * @param string $folder The delivery folder.
     *
     * @return NULL
     */
    public function setDeliveryFolder($folder)
    {
        $this->_delivery_folder = $folder;
    }

    public function getArguments()
    {
        return array(
            ($this->isActive()) ? 'true, ## delivery enabled' : 'false, ## delivery disabled',
            // UTF7-conversion handles a specific cyrus bug. This does not work
            // when using dovecot for example. The sieve RFC requires UTF8.
            String::convertCharset($this->getDeliveryFolder(), 'utf-8', 'utf7-imap')
        );
    }

    public function parseArguments($script)
    {
        $this->parseDeliveryFolder($script);
    }

    public function parseDeliveryFolder($script)
    {
        if (preg_match("/fileinto \"INBOX\/([^\"]*)\";/", $script, $regs)) {
            $this->setDeliveryFolder(String::convertCharset($regs[1], 'utf7-imap', 'utf-8'));
        }
    }
}