<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 12:57
 */

namespace Wsp\Guardian;

use Cartalyst\Sentinel\Sentinel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wsp\Guardian;
use Silex\Application;

class Director {
	/**
	 * @var [Guardian]
	 */
	protected $guardians = array();

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
	 * @param array $guardiansConfig
	 */
	public function __construct(Application $app, array $guardiansConfig) {
		$this->app = $app;
		foreach ($guardiansConfig as $options) {
			$guardian = new Guardian($app, $options);
			$this->guardians[] = $guardian;
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
		$this->filter = new Filter($this->request, $this->guardians);
		return $this;
	}

	/**
	 * @return Filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * Returns void or throws HTTP 403 exception on denial of access
	 * @return void
	 * @throws AccessDeniedHttpException
	 */
	public function apply() {
		if (!$this->filter->hasFilteredGuardians()) return;
		throw new AccessDeniedHttpException();
	}
}