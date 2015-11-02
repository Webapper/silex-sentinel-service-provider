<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 20:36
 */

namespace Wsp;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Wsp\Firewall\Director;
use Wsp\Guardian\GuardianException;

class Guardian {
	const ROLE_GUEST = '.guest';
	const ROLE_ROOT = '.root';

	/**
	 * @var string
	 */
	protected $pathPattern;
	/**
	 * @var array
	 */
	protected $roles = array();
	/**
	 * @var string
	 */
	protected $checkingMethod = 'has_access'; // has_access, has_any_access

	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, array $guardianConfig) {
		if (empty($guardianConfig['path'])) throw new \InvalidArgumentException('Missing argument in config: path');
		if (empty($guardianConfig['roles'])) $guardianConfig['roles'] = static::ROLE_GUEST;
		if (!empty($guardianConfig['method']) and !in_array($guardianConfig['method'], array('has_access', 'has_any_access'))) throw new \InvalidArgumentException('Invalid argument value for method: '.$guardianConfig['method'].' - valid values are "has_access", "has_any_access"');

		$this->app = $app;
		$this->pathPattern = $guardianConfig['path'];
		$this->roles = $guardianConfig['roles'];
		if (!empty($guardianConfig['method'])) $this->checkingMethod = $guardianConfig['method'];
	}

	/**
	 * @return string
	 */
	public function getPathPattern()
	{
		return $this->pathPattern;
	}

	/**
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @return string
	 */
	public function getCheckingMethod()
	{
		return $this->checkingMethod;
	}

	public function executeOn(Request $request) {
		if ($this->roles == static::ROLE_GUEST) return true;
		if (!preg_match('#'.str_replace('#', '\\#', $this->pathPattern).'#', $request->getRequestUri())) return true;

		/** @var $director Director */
		$director = $this->app['sentinel.guardian.firewall'];
		$filter = $director
			->setRequest($request)
			->build()
			->getFilter()
		;
		if (!$filter->hasFilteredFirewalls()) throw new GuardianException($this, 'Unable to guard resource at '.$request->getRequestUri().' - no firewall matched');

		// only the last firewall can be relevant about roles, identities of previous firewalls should checked by
		// another previous guardian
		$sentinel = $filter->getLastFirewall()->getSentinel();

		$passed = false;
		try {
			if ($this->checkingMethod == 'has_access') {
				$passed = (bool)$sentinel->hasAccess($this->roles);
			} else {
				$passed = (bool)$sentinel->hasAnyAccess($this->roles);
			}
		} catch (\BadMethodCallException $e) {
			// in case of user is unidentified by the requested identity:
			// nothing to do, result is false
		}

		return $passed;
	}
}