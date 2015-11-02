<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.02.
 * Time: 12:31
 */

namespace Wsp\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Wsp\Firewall\Director;

class TwigServiceProvider implements ServiceProviderInterface {
	/**
	 * @var Request
	 */
	protected $request;

	public function register(Application $app) {
		$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
			/** @var $twig \Twig_Environment */
			/** @var $director Director */
			$director = $app['sentinel.guardian.firewall'];
			$sentinel = $director
				->setRequest($this->request)
				->build()
				->getFilter()
				->getLastFirewall()
				->getSentinel()
			;
			$twig->addGlobal('Sentinel', $sentinel);
			$twig->addGlobal('DefaultSentinel', $app['sentinel']);
			return $twig;
		}));
	}

	public function boot(Application $app)
	{
		$app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'));
	}

	public function onKernelRequest(GetResponseEvent $event) {
		$this->request = $event->getRequest();
	}

}