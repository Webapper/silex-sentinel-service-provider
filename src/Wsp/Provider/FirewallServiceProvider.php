<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.01.
 * Time: 1:02
 */

namespace Wsp\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FirewallServiceProvider implements ServiceProviderInterface {
	public function register(Application $app)
	{
		$app['sentinel'] = Sentinel::instance(new SentinelBootstrapper($app));
	}

	public function boot(Application $app)
	{
	}
}