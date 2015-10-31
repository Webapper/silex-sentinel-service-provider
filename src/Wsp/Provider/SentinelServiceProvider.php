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
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Wsp\SentinelBootstrapper;


class SentinelServiceProvider implements ServiceProviderInterface {
	public function register(Application $app)
	{
		$app['sentinel'] = Sentinel::instance(new SentinelBootstrapper($app));
	}

	public function boot(Application $app)
	{
	}
}