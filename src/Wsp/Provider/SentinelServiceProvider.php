<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.10.31.
 * Time: 18:14
 */

namespace Wsp\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Wsp\SentinelBootstrapper;


class SentinelServiceProvider implements ServiceProviderInterface {
	public function register(Application $app)
	{
		$app['sentinel'] = static::instance($app);
		if (isset($app['sentinel.identities'])) {
			foreach ($app['sentinel.addons'] as $k=>$config) {
				$bootstrapper = 'Wsp\\SentinelBootstrapper';
				if ($config['bootstrapper']) {
					$bootstrapper = $config['bootstrapper'];
				}
				if (!class_exists($bootstrapper)) throw new \InvalidArgumentException('Class "'.$bootstrapper.'" not found for Sentinel identity: sentinel.identities.'.$k);
				$app['sentinel.identity.'.$k] = static::instance($app, new $bootstrapper($app, 'sentinel.identities.'.$k));
			}
		}
		$app->register(new FirewallServiceProvider());
		$app->register(new GuardianServiceProvider());
	}

	/**
	 * @param Application $app
	 * @param SentinelBootstrapper $bootstrapper
	 * @return \Cartalyst\Sentinel\Sentinel
	 */
	public static function instance(Application $app, SentinelBootstrapper $bootstrapper = null) {
		if ($bootstrapper === null) {
			$bootstrapper = new SentinelBootstrapper($app);
		}
		return $bootstrapper->createSentinel();
	}

	public function boot(Application $app)
	{
	}
}