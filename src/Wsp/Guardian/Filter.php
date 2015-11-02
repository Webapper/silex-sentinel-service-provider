<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 12:51
 */

namespace Wsp\Guardian;


use Symfony\Component\HttpFoundation\Request;
use Wsp\Guardian;

class Filter {
	/**
	 * @var [Firewall]
	 */
	protected $guardians = array();

	public function __construct(Request $request, array $guardians) {
		foreach ($guardians as $guardian) {
			/** @var $guardian Guardian */
			if (!$guardian->executeOn($request)) $this->guardians[] = $guardian;
		}
	}

	/**
	 * @return bool
	 */
	public function hasFilteredGuardians() {
		return (count($this->guardians) > 0);
	}

	/**
	 * @return array
	 */
	public function getFilteredGuardians() {
		return $this->guardians;
	}

	/**
	 * @return Guardian
	 */
	public function getLastFirewall() {
		return $this->guardians[count($this->guardians) - 1];
	}

	/**
	 * @param Guardian $guardian
	 * @return bool
	 */
	public function isFiltered(Guardian $guardian) {
		foreach ($this->guardians as $check) {
			/** @var $check Guardian */
			if ($check->getPathPattern() == $guardian->getPathPattern()) return true;
		}
		return false;
	}
}