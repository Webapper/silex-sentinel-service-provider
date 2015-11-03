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
	 * @param Application $app
	 * @param string $configKey
	 */
	public function __construct(Application $app, $configKey = 'sentinel.config')
	{
		$reflection = new \ReflectionClass('Cartalyst\\Sentinel\\Sentinel');
		$configPath = dirname(realpath($reflection->getFileName())).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
		$config = $app[$configKey];
		if (file_exists($configPath)) {
			$config = require($configPath);
			$config = array_merge($config, $app[$configKey]);
		}

		$this->app = $app;
		$this->config = $config;
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