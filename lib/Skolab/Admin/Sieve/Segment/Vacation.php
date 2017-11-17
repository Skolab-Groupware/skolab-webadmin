<?php
/**
 * A sieve script that responds automatically during vacations.
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
 * A sieve script that responds automatically during vacations.
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
class SkolabAdmin_Sieve_Segment_Vacation
extends SkolabAdmin_Sieve_Segment
{
	//
	// The segment type.
	//
	// @var string
	//
	protected $type = 'vacation';

	//
	// The text of the automatic response.
	//
	// @var string
	//
	private $_response;

	//
	// Resend response after this amount of days elapsed.
	//
	// @var string
	//
	private $_resend_after = 7;

	//
	// Recipient addresses for which the response should be sent.
	//
	// @var array
	//
	private $_recipient_addresses = array();

	//
	// Should a response be sent in case the incoming message has been tagged as
	// spam?
	//
	// @var boolean
	//
	private $_react_to_spam = false;

	//
	// The sender address must be part of this domain for the automatic
	// responses to be sent.
	//
	// @var string
	//
	private $_domain = '';

	//
	// Constructor.
	//
	// @param string $script The current script segment
	//
	public function __construct($script = '')
	{
		$this->_response = '';

		$this->template = 'if allof (%s' . "\r\n" .
		    '%s,' . "\r\n" .
		    '%s) {' . "\r\n" .
		    'vacation %s :days %s text:' . "\r\n" .
		    '%s' . "\r\n" .
		    '.' . "\r\n" .
		    ';' . "\r\n" .
		    '}' . "\r\n";

		$this->_response = sprintf(
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

		parent::__construct($script);
	}

	//
	// Fetch the text of the automatic response.
	//
	// @return string The response.
	//
	public function getResponse()
	{
		return $this->undotstuff($this->_response);
	}

	//
	// Set the text of the automatic response.
	//
	// @param string $response The response.
	//
	// @return NULL
	//
	public function setResponse($response)
	{
		$this->_response = $this->dotstuff($response);
	}

	//
	// Resend the automatic response after how many days?
	//
	// @return int The number of days.
	//
	public function getResendAfter()
	{
		//@todo: Should be placed into a validate method.
		if ($this->_resend_after < 1) {
			$this->_resend_after = 7;
		}
		return $this->_resend_after;
	}

	//
	// Set after how many days the automatic response should get sent again.
	//
	// @param int $days Resend after this many days.
	//
	// @return NULL
	//
	public function setResendAfter($days)
	{
		//@todo: Should be placed into a validate method.
		if ($this->_resend_after < 1) {
			throw new Exception(_('Days must be at least one'));
		}
		$this->_resend_after = $days;
	}

	//
	// Send the responses to which recipient addresses?
	//
	// @return array The recipient addresses.
	//
	public function getRecipientAddresses()
	{
		return $this->_recipient_addresses;
	}

	//
	// Set the recipient addresses for which the automatic reply will be sent.
	//
	// @param array $addresses The recipient addresses.
	//
	// @return NULL
	//
	public function setRecipientAddresses(array $addresses)
	{
		$this->_recipient_addresses = $addresses;
	}

	//
	// Should the vacation notice also be sent in reply to messages
	// flagged as spam?
	//
	// @return boolean True in case the reply should also be sent to
	// potential spam messages.
	//
	public function getReactToSpam()
	{
		return $this->_react_to_spam;
	}

	//
	// Set whether the replies during vacation should also get sent to
	// potential spam messages.
	//
	// @param boolean $react_to_spam Should the replies also be sent
	// for potential spam messages?
	//
	// @return NULL
	//
	public function setReactToSpam($react_to_spam)
	{
		$this->_react_to_spam = $react_to_spam;
	}

	//
	// Should we only react to messages recieved from a specific domain?
	//
	// @return string The domain for which the vacation response will be sent.
	//
	public function getDomain()
	{
		return $this->_domain;
	}

	//
	// Set the domain for which vacation replies will be sent.
	//
	// @param string $domain The domain for which the vacation response will be sent.
	//
	// @return NULL
	//
	public function setDomain($domain)
	{
		$this->_domain = $domain;
	}

	public function getArguments()
	{
		$domain = $this->getDomain();
		if (!empty($domain)) {
			$domain = 'address :domain :contains "From" "' . $domain . '"';
		} else {
			$domain = 'true';
		}
		if (!$this->getReactToSpam()) {
			$react_to_spam = 'not header :contains "X-Spam-Flag" "YES"';
		} else {
			$react_to_spam = 'true';
		}
		$addresses = $this->getRecipientAddresses();
		if (!empty($addresses)) {
			$addresses = ':addresses [ "' . join('", "', $this->getRecipientAddresses()) . '" ]';
		} else {
			$addresses = '';
		}
		return array(
		             ($this->isActive()) ? 'true, ## vacation enabled' : 'false, ## vacation disabled',
		             $domain,
		             $react_to_spam,
		             $addresses,
		             $this->getResendAfter(),
		             $this->getResponse()
		);
	}

	public function parseArguments($script)
	{
		$this->parseResponse($script);
		$this->parseResendAfter($script);
		$this->parseDomain($script);
		$this->parseRecipientAddresses($script);
		$this->parseReactToSpam($script);
	}

	public function parseResponse($script)
	{
		if (preg_match("/text:(.*\r\n)\\.\r\n/s", $script, $regs)) {
			$this->_response = trim(str_replace( '\n', "\r\n", $regs[1]));
		}
	}

	public function parseResendAfter($script)
	{
		if (preg_match("/:days ([0-9]+)/s", $script, $regs)) {
			$this->_resend_after = $regs[1];
		}
	}

	public function parseDomain($script)
	{
		if (preg_match('/address :domain :contains "From" "(.*)"/i', $script, $regs)) {
			$this->_domain = $regs[1];
		}
	}

	public function parseRecipientAddresses($script)
	{
		if (preg_match("/:addresses \\[([^\\]]*)\\]/s", $script, $regs)) {
			$tmp = preg_split('/,/', $regs[1]);
			$this->_recipient_addresses = array();
			foreach ($tmp as $a) {
				if (preg_match('/^ *"(.*)" *$/', $a, $regs)) {
					$this->_recipient_addresses[] = $regs[1];
				} else {
					$this->_recipient_addresses[] = $a;
				}
			}
		}
	}

	public function parseReactToSpam($script)
	{
		if (preg_match('/header :contains "X-Spam-Flag" "YES"/i', $script)) {
			$this->_react_to_spam = false;
		} else {
			$this->_react_to_spam = true;
		}
	}
}
