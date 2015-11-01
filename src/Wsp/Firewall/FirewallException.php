<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 17:46
 */

namespace Wsp\Firewall;

use Wsp\Exception;
use Wsp\Firewall;

class FirewallException extends Exception {
	/**
	 * @var Firewall
	 */
	protected $firewall;

	public function __construct(Firewall $firewall, $message = '', $code = 0, \Exception $previous = null) {
		$this->firewall = $firewall;
		parent::__construct(sprintf($message, $firewall->getName()), $code, $previous);
	}

	public function getFirewall() {
		return $this->firewall;
	}
}