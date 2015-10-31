<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.10.31.
 * Time: 18:59
 */

namespace Wsp;

use Cartalyst\Sentinel\Native\SentinelBootstrapper as NativeBootstrapper;
use Silex\Application;
use Wsp\Silex\SilexCookie;
use Wsp\Silex\SilexSession;

class SentinelBootstrapper extends NativeBootstrapper {
	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param  arry  $config
	 * @return void
	 */
	public function __construct(Application $app, $config = 'sentinel.config')
	{
		$this->app = $app;
		$this->config = $app[$config];
	}

	/**
	 * Creates a session.
	 *
	 * @return \Wsp\Silex\SilexSession
	 */
	protected function createSession()
	{
		return new SilexSession($this->app['session'], $this->config['session']);
	}

	/**
	 * Creates a cookie.
	 *
	 * @return \Wsp\Silex\SilexCookie
	 */
	protected function createCookie()
	{
		return new SilexCookie($this->app['dispatcher'], $this->config['cookie']);
	}

}