<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 1:04
 */

namespace Wsp;

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
		if (count($failedFields) > 0) throw new \InvalidArgumentException('You missed to specify a '.join(', and ', $failedFields));
		if (!empty($options['order'])) {
			$options['order'] = array_map(function($v) { return strtolower($v); }, $options['order']);
		}
		if (!empty($options['order']) and !in_array('deny', $options['order']) and !in_array('allow', $options['order'])) {
			throw new \InvalidArgumentException('Invalid order directive: '.join(', ', $options['order']));
		}

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

	public function executeOn(Request $request) {
		$passed = true;
		if ($this->order[0] == 'deny' and $this->denyPattern) {
			$passed = !(bool)preg_match('#'.str_replace('#', '\\#', $this->denyPattern).'#', $request->getRequestUri());
		} else if ($this->allowPattern) {
			$passed = (bool)preg_match('#'.str_replace('#', '\\#', $this->allowPattern).'#', $request->getRequestUri());
		}

		if ($passed) {
			if ($this->order[1] == 'deny' and $this->denyPattern) {
				$passed = !(bool)preg_match('#'.str_replace('#', '\\#', $this->denyPattern).'#', $request->getRequestUri());
			} else if ($this->allowPattern) {
				$passed = (bool)preg_match('#'.str_replace('#', '\\#', $this->allowPattern).'#', $request->getRequestUri());
			}
		}

		return $passed;
	}
}