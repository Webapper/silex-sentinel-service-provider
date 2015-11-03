<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 12:57
 */

namespace Wsp\Firewall;

use Cartalyst\Sentinel\Sentinel;
use Symfony\Component\HttpFoundation\Request;
use Wsp\Firewall;
use Silex\Application;

class Director {
	/**
	 * @var [Firewall]
	 */
	protected $firewalls = array();

	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Filter
	 */
	protected $filter;

	/**
	 * @param Application $app
	 * @param array $firewallsConfig
	 */
	public function __construct(Application $app, array $firewallsConfig) {
		$this->app = $app;
		foreach ($firewallsConfig as $name=>$options) {
			if (!is_numeric($name)) {
				$options['name'] = $name;
			}
			$firewall = new Firewall($app, $options);
			$this->firewalls[$firewall->getName()] = $firewall;
		}
	}

	/**
	 * @param Request $request
	 * @return $this
	 */
	public function setRequest(Request $request) {
		$this->request = $request;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function build() {
		$this->filter = new Filter($this->request, $this->firewalls);
		return $this;
	}

	/**
	 * @return Filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
	 */
	public function apply() {
		if (!$this->filter->hasFilteredFirewalls()) return;

		/** @var $refused Firewall */
		$refused = null;

		/* returns true if passed, false otherwise */
		$apply = function($apply, Firewall $firewall) {
			if (!$this->filter->isFiltered($firewall)) return true;

			$applicable = true;
			if ($firewall->getParentFirewall()) {
				if (!isset($this->firewalls[$firewall->getParentFirewall()])) throw new \RuntimeException('Parent firewall not found: '.$firewall->getParentFirewall());
				// chaining up on parent firewalls to find out whether if this firewall could be applicable or not
				$applicable = $apply($apply, $this->firewalls[$firewall->getParentFirewall()]);
			}
			if ($applicable === true) {
				$sentinel = $firewall->getSentinel();
				if ($sentinel->check()) {
					// maybe, nothing to do here...
				} else {
					$applicable = $firewall;
				}
			}

			return $applicable;
		};

		foreach ($this->filter->getFilteredFirewalls() as $firewall) {
			/** @var $firewall Firewall */
			if (true !== $refused = $apply($apply, $firewall)) {
				$route = $refused->getAuthRoute();
				if (!$route) {
					$sentinelConfig = $refused->getConfig();
					$route = $sentinelConfig['auth_route'];
				}
				if (!$route) throw new FirewallException($refused, 'Could not determine authentication route for firewall: %s');

				return $this->app->redirect($route{0} == '/'? $route : $this->app['url_generator']->generate($route));
			}
		}
	}
}