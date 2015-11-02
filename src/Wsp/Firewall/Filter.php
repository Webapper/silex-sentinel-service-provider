<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 12:51
 */

namespace Wsp\Firewall;


use Symfony\Component\HttpFoundation\Request;
use Wsp\Firewall;

class Filter {
	/**
	 * @var [Firewall]
	 */
	protected $firewalls = array();

	public function __construct(Request $request, array $firewalls) {
		foreach ($firewalls as $firewall) {
			/** @var $firewall Firewall */
			if ($firewall->executeOn($request)) $this->firewalls[$firewall->getName()] = $firewall;
		}
	}

	/**
	 * @return bool
	 */
	public function hasFilteredFirewalls() {
		return (count($this->firewalls) > 0);
	}

	/**
	 * @return array
	 */
	public function getFilteredFirewalls() {
		return $this->firewalls;
	}

	/**
	 * @return Firewall
	 */
	public function getLastFirewall() {
		return $this->firewalls[array_keys($this->firewalls)[count($this->firewalls) - 1]];
	}

	/**
	 * @param Firewall $firewall
	 * @return bool
	 */
	public function isFiltered(Firewall $firewall) {
		return isset($this->firewalls[$firewall->getName()]);
	}
}