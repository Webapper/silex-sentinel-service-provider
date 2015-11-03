<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.10.31.
 * Time: 20:07
 */

namespace Wsp\Silex;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SilexCookie implements CookieInterface {
	/**
	 * The cookie options.
	 *
	 * @var array
	 */
	protected $options = array(
		'name'		=> 'cartalyst_sentinel',
		'lifetime'	=> 0,
		'domain'	=> '',
		'path'		=> '/',
		'secure'	=> false,
		'http_only'	=> false,
	);

	/**
	 * Data cache
	 *
	 * @var array
	 */
	protected $dataOptions = array();

	/**
	 * Application dispatcher
	 * @var EventDispatcher
	 */
	protected $dispatcher;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * Indicates whether the value changed or clean
	 *
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * Create a new cookie driver.
	 *
	 * @param EventDispatcher $dispatcher
	 * @param  string|array $options
	 */
	public function __construct(EventDispatcher $dispatcher, $options = array())
	{
		$this->dispatcher = $dispatcher;
		$this->request = Request::createFromGlobals(); // for the earliest reasons

		if (is_array($options)) {
			$this->options = array_merge($this->options, $options);
		} else {
			$this->options['name'] = $options;
		}

		$this->dataOptions = array_merge($this->options, array('value'=>$this->request->cookies->get($this->options['name']))); // cloning the array

		$this->dispatcher->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'));
		$this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
	}
	/**
	 * {@inheritDoc}
	 */
	public function put($value)
	{
		$this->setCookie($value, $this->minutesToLifetime(2628000));
	}
	/**
	 * {@inheritDoc}
	 */
	public function get()
	{
		return $this->getCookie();
	}
	/**
	 * {@inheritDoc}
	 */
	public function forget()
	{
		$this->put(null, -2628000);
	}
	/**
	 * Takes a minutes parameter (relative to now)
	 * and converts it to a lifetime (unix timestamp).
	 *
	 * @param  int  $minutes
	 * @return int
	 */
	protected function minutesToLifetime($minutes)
	{
		return time() + ($minutes * 60);
	}
	/**
	 * Returns a PHP cookie.
	 *
	 * @return mixed
	 */
	protected function getCookie()
	{
		return $this->dataOptions['value'];
	}
	/**
	 * Sets a PHP cookie.
	 *
	 * @param  mixed  $value
	 * @param  int  $lifetime
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  bool  $secure
	 * @param  bool  $httpOnly
	 * @return void
	 */
	protected function setCookie($value, $lifetime, $path = null, $domain = null, $secure = null, $httpOnly = null)
	{
		$this->dirty = true;
		$this->dataOptions['value'] = $value;
		$this->dataOptions['lifetime'] = $lifetime;
		$this->dataOptions['path'] = $path?: $this->dataOptions['path'];
		$this->dataOptions['domain'] = $domain?: $this->dataOptions['domain'];
		$this->dataOptions['secure'] = $secure?: $this->dataOptions['secure'];
		$this->dataOptions['http_only'] = $httpOnly?: $this->dataOptions['http_only'];
	}

	public function onKernelRequest(GetResponseEvent $event) {
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		$this->request = $event->getRequest();
		if ($this->dirty) return; // do not override changed value by a previous state of cookie

		$cookies = $event->getRequest()->cookies;

		if ($cookies->has($this->options['name'])) {
			$this->dataOptions['value'] = $cookies->get($this->options['name']);
		}
	}

	public function onKernelResponse(FilterResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		$headers = $event->getResponse()->headers;

		$headers->setCookie(new Cookie(
			$this->options['name'],
			$this->dataOptions['value'],
			$this->dataOptions['lifetime'],
			$this->dataOptions['path'],
			$this->dataOptions['domain'],
			$this->dataOptions['secure'],
			$this->dataOptions['http_only']
		));
	}
}