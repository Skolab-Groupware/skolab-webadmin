<?php
/**
 * Manages Kolab user sieve scripts.
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
 * Manages Kolab user sieve scripts.
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
class SkolabAdmin_Sieve
{

	const SCRIPT = 'kolab.siv';

	const OLD_SCRIPT_DELIVERY = 'kolab-deliver.siv';

	const OLD_SCRIPT_FORWARD  = 'kolab-forward.siv';

	const OLD_SCRIPT_VACATION = 'kolab-vacation.siv';


	//
	// The sieve connection.
	//
	private $_sieve;

	//
	// The sieve script segments.
	//
	private $_segments;

	//
	// Constructor.
	//
	// @param Net_Sieve $sieve The sieve connection.
	//
	public function __construct(Net_Sieve $sieve)
	{
		$this->_sieve = $sieve;
	}

	private function _init()
	{
		if ($this->_segments === null) {
			$scripts = $this->_sieve->listScripts();

			if (!empty($scripts)) {
				if (in_array(self::SCRIPT, $scripts)) {
					$this->_segments = $this->_splitSegments(
						$this->_sieve->getScript(self::SCRIPT)
					);
				} else {
					if (in_array(self::OLD_SCRIPT_DELIVERY, $scripts)) {
						$this->_segments['delivery'] = new SkolabAdmin_Sieve_Segment_Delivery(
							$this->_sieve->getScript(self::OLD_SCRIPT_DELIVERY)
						);
						if ($this->_sieve->getActive() == self::OLD_SCRIPT_DELIVERY) {
							$this->_segments['delivery']->setActive();
						}
					}
					if (in_array(self::OLD_SCRIPT_FORWARD, $scripts)) {
						$this->_segments['forward'] = new SkolabAdmin_Sieve_Segment_Forward(
							$this->_sieve->getScript(self::OLD_SCRIPT_FORWARD)
						);
						if ($this->_sieve->getActive() == self::OLD_SCRIPT_FORWARD) {
							$this->_segments['forward']->setActive();
						}
					}
					if (in_array(self::OLD_SCRIPT_VACATION, $scripts)) {
						$this->_segments['vacation'] = new SkolabAdmin_Sieve_Segment_Vacation(
							$this->_sieve->getScript(self::OLD_SCRIPT_VACATION)
						);
						if ($this->_sieve->getActive() == self::OLD_SCRIPT_VACATION) {
							$this->_segments['vacation']->setActive();
						}
					}
				}
			}
			if (!isset($this->_segments['delivery'])) {
				$this->_segments['delivery'] = new SkolabAdmin_Sieve_Segment_Delivery();
			}
			if (!isset($this->_segments['forward'])) {
				$this->_segments['forward'] = new SkolabAdmin_Sieve_Segment_Forward();
			}
			if (!isset($this->_segments['vacation'])) {
				$this->_segments['vacation'] = new SkolabAdmin_Sieve_Segment_Vacation();
			}
		}
	}

	private function _splitSegments($script)
	{
		$segments = array();
		preg_match_all('/### SEGMENT START [^#]*.*?### SEGMENT END [^#]*/s', $script, $matches);
		foreach ($matches[0] as $match) {
			preg_match('/### SEGMENT START ([^# ]*) ?(ENABLED)?/s', $match, $id);
			if (!empty($id[1])) {
				$type = strtolower($id[1]);
				$class = 'SkolabAdmin_Sieve_Segment_' . ucfirst($type);
				if (!isset($id[2])) {
					$match = preg_replace('/^#/m', '', $match);
				}
				$segments[$type] = new $class($match);
				if (isset($id[2]) && $id[2] == 'ENABLED') {
					$segments[$type]->setActive();
				}
			}
		}
		return $segments;
	}

	public function fetchDeliverySegment()
	{
		$this->_init();
		return $this->_segments['delivery'];
	}

	public function fetchForwardSegment()
	{
		$this->_init();
		return $this->_segments['forward'];
	}

	public function fetchVacationSegment()
	{
		$this->_init();
		return $this->_segments['vacation'];
	}

	public function store()
	{
		$result = $this->_sieve->installScript(self::SCRIPT, $this->getScript(), true);
		if ($result instanceOf PEAR_Error) {
			throw new Exception($result->getMessage());
		}
	}

	public function getScript()
	{
		$script = 'require "fileinto";' . "\r\n" . 'require "vacation";' . "\r\n\r\n\r\n";
		$order = array('vacation', 'forward', 'delivery');
		foreach ($order as $segment) {
			$script .= $this->_segments[$segment]->generate() . "\r\n\r\n";
		}
		return $script;
	}

	public function checkUnknownScript()
	{
		$known = array(
			self::SCRIPT,
			self::OLD_SCRIPT_DELIVERY,
			self::OLD_SCRIPT_FORWARD,
			self::OLD_SCRIPT_VACATION,
		);
		$active = $this->_sieve->getActive();
		if (!in_array($active, $known)) {
			return $active;
		}
		return false;
	}
}
