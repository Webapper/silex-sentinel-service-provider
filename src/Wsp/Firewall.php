<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 1:04
 */

namespace Wsp;

use Cartalyst\Sentinel\Sentinel;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Firewall {
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $allowPattern;
	/**
	 * @var string
	 */
	protected $denyPattern;
	/**
	 * @var string
	 */
	protected $patternsType = 'path'; // path, controller, route
	/**
	 * @var array
	 */
	protected $order = array('deny', 'allow');
	/**
	 * @var string
	 */
	protected $parentFirewall;
	/**
	 * @var string
	 */
	protected $denyFallbackRoute;
	/**
	 * @var string
	 */
	protected $authRoute;
	/**
	 * @var string
	 */
	protected $authSuccessRoute;
	/**
	 * @var string
	 */
	protected $authFailedRoute;
	/**
	 * @var string
	 */
	protected $identity; // standard, basic, social.*
	/**
	 * @var bool
	 */
	protected $passed = false;
	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, $options = array()) {
		$failedFields = array();
		if (empty($options['name'])) $failedFields[] = 'name';
		if (empty($options['pattern']) and empty($options['allow_pattern']) and empty($options['deny_pattern'])) $failedFields[] = 'pattern or allow_pattern, and deny_pattern';
		if (!empty($options['patterns_type']) and !in_array(strtolower($options['patterns_type']), array('path', 'controller', 'route'))) $failedFields[] = 'invalid patterns_type value "'.$options['patterns_type'].'" - valids are: "path", "controller", "route"';
		if (count($failedFields) > 0) throw new \InvalidArgumentException('You\'ve missed/failed specifying configuration of '.join(', and ', $failedFields));
		if (!empty($options['order'])) {
			$options['order'] = array_map(function($v) { return strtolower($v); }, $options['order']);
		}
		if (!empty($options['order']) and !in_array('deny', $options['order']) and !in_array('allow', $options['order'])) {
			throw new \InvalidArgumentException('Invalid order directive: '.join(', ', $options['order']));
		}

		$this->app = $app;

		$this->name = $options['name'];
		$this->allowPattern = $options['pattern']?: $options['allow_pattern']?: false;
		$this->denyPattern = $options['deny_pattern']?: false;
		$this->order = $options['order']?: array('deny', 'allow');
		$this->parentFirewall = $options['parent']?: false;
		$this->denyFallbackRoute = $options['deny_fallback_route']?: null;
		$this->authRoute = $options['auth_route']?: null;
		$this->authSuccessRoute = $options['auth_success_route']?: null;
		$this->authFailedRoute = $options['auth_failed_route']?: null;
		$this->identity = $options['identity']?: null;

		$config = $this->getConfig();
		$this->patternsType = $options['patterns_type']?: $config['patterns_type']?: strtolower($this->patternsType);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getAllowPattern() {
		return $this->allowPattern;
	}

	/**
	 * @return string
	 */
	public function getDenyPattern() {
		return $this->denyPattern;
	}

	/**
	 * @return string
	 */
	public function getPatternsType() {
		return $this->patternsType;
	}

	/**
	 * @return array
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * @return string
	 */
	public function getParentFirewall() {
		return $this->parentFirewall;
	}

	/**
	 * @return string
	 */
	public function getDenyFallbackRoute() {
		return $this->denyFallbackRoute;
	}

	/**
	 * @return string
	 */
	public function getAuthRoute() {
		return $this->authRoute;
	}

	/**
	 * @return string
	 */
	public function getAuthSuccessRoute() {
		return $this->authSuccessRoute;
	}

	/**
	 * @return string
	 */
	public function getAuthFailedRoute() {
		return $this->authFailedRoute;
	}

	/**
	 * @return string
	 */
	public function getIdentity() {
		return $this->identity;
	}

	/**
	 * @return bool
	 */
	public function isPassed() {
		return $this->passed;
	}

	/**
	 * @return array
	 */
	public function getConfig() {
		$identityKey = 'sentinel.config';
		if ($this->getIdentity() !== null) {
			$identityKey = 'sentinel.identities.'.$this->getIdentity();
			if (!isset($this->app[$identityKey])) throw new \RuntimeException('Unspecified identity manager "sentinel.identities.'.$this->getIdentity().'" for firewall: '.$this->getName());
		}
		$sentinel = $this->app[$identityKey];
		return $sentinel;
	}

	/**
	 * @return Sentinel
	 */
	public function getSentinel() {
		$identityKey = 'sentinel';
		if ($this->getIdentity() !== null) {
			$identityKey = 'sentinel.identity.'.$this->getIdentity();
			if (!isset($this->app[$identityKey])) throw new \RuntimeException('Identity manager "sentinel.identities.'.$this->getIdentity().'" looks unspecified for firewall: '.$this->getName());
		}
		$sentinel = $this->app[$identityKey];
		return $sentinel;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	public function executeOn(Request $request) {
		$passed = true;
		$target = '';
		switch ($this->patternsType) {
			case 'path': $target = $request->getRequestUri(); break;
			case 'controller': $target = $request->get('_controller'); break;
			case 'route': $target = $request->get('_route'); break;
		}

		if ($this->order[0] == 'deny' and $this->denyPattern) {
			$passed = !(bool)preg_match('#'.str_replace('#', '\\#', $this->denyPattern).'#', $target);
		} else if ($this->allowPattern) {
			$passed = (bool)preg_match('#'.str_replace('#', '\\#', $this->allowPattern).'#', $target);
		}

		if ($passed) {
			if ($this->order[1] == 'deny' and $this->denyPattern) {
				$passed = !(bool)preg_match('#'.str_replace('#', '\\#', $this->denyPattern).'#', $target);
			} else if ($this->allowPattern) {
				$passed = (bool)preg_match('#'.str_replace('#', '\\#', $this->allowPattern).'#', $target);
			}
		}

		return $passed;
	}
}