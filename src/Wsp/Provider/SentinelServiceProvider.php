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
		$bootstrapper = new SentinelBootstrapper($app);
		$app['sentinel'] = $bootstrapper->createSentinel();
	}

	public function boot(Application $app)
	{
	}
}