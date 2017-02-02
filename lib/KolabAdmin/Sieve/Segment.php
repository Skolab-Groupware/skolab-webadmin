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
abstract class KolabAdmin_Sieve_Segment
{
    /**
     * The segment type
     *
     * @var string
     */
    protected $type;

    /**
     * The script template
     *
     * @var string
     */
    protected $template;

    /**
     * Is this particular segment active?
     *
     * @var bool
     */
    private $_active = false;

    /**
     * Constructor.
     *
     * @param string $script The current script segment
     */
    public function __construct($script = '')
    {
        if (!empty($script)) {
            $this->parseArguments($script);
        }
    }

    /**
     * Is this particular segment active?
     *
     * @return bool True if the segment is active.
     */
    public function isActive()
    {
        return $this->_active;
    }

    /**
     * Set the segment (in)active.
     *
     * @param boolean $active Should the segment be active or inactive?
     *
     * @return NULL
     */
    public function setActive($active = true)
    {
        $this->_active = $active;
    }

    /**
     * Set the segment inactive.
     *
     * @return NULL
     */
    public function setInactive()
    {
        $this->_active = false;
    }

    public function generate()
    {
        $script = $this->_generateScript();
        if (!$this->_active) {
            $script = preg_replace('/^(.)/m', '#$1', $script);
        }
        return '### SEGMENT START ' . strtoupper($this->type) .' ' .
            (($this->_active) ? 'ENABLED' : 'DISABLED') . "\r\n" .
            $script .
            '### SEGMENT END ' . strtoupper($this->type) . "\r\n";
    }

    private function _generateScript()
    {
        return vsprintf($this->template, $this->getArguments());
    }

    protected function dotstuff($str)
    {
        return str_replace("\n.", "\n..", $str);
    }

    protected function undotstuff($str)
    {
        return str_replace("\n..", "\n.", $str);
    }

    abstract public function getArguments();

    abstract public function parseArguments($script);
}