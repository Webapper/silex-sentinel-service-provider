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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Wsp\Guardian\Director;

class GuardianServiceProvider implements ServiceProviderInterface {
	/**
	 * @var array
	 */
	protected $firewalls = array();
	/**
	 * @var Application
	 */
	protected $app;

	public function register(Application $app)
	{
		$this->app = $app;
		$app['sentinel.guardian'] = new Director($app, $app['sentinel.guardians']);
	}

	public function boot(Application $app)
	{
		$app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'));
	}

	public function onKernelRequest(GetResponseEvent $event) {
		/** @var $director Director */
		$director = $this->app['sentinel.guardian'];
		$director
			->setRequest($event->getRequest())
			->build()
			->apply()
		;
	}
}