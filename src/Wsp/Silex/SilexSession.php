<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.10.31.
 * Time: 18:45
 */

namespace Wsp\Silex;

use Cartalyst\Sentinel\Sessions\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SilexSession implements SessionInterface {
	/**
	 * The session key.
	 *
	 * @var string
	 */
	protected $key = 'cartalyst_sentinel';

	/**
	 * @var Session
	 */
	protected $session;
	/**
	 * Creates a new native session driver for Sentinel.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __construct(Session $session, $key = null)
	{
		$this->session = $session;
		if (isset($key)) {
			$this->key = $key;
		}
		$this->startSession();
	}
	/**
	 * Called upon destruction of the native session handler.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->writeSession();
	}
	/**
	 * {@inheritDoc}
	 */
	public function put($value)
	{
		$this->setSession($value);
	}
	/**
	 * {@inheritDoc}
	 */
	public function get()
	{
		return $this->getSession();
	}
	/**
	 * {@inheritDoc}
	 */
	public function forget()
	{
		$this->forgetSession();
	}
	/**
	 * Starts the session if it does not exist.
	 *
	 * @return void
	 */
	protected function startSession()
	{
		// Check that the session hasn't already been started
		if ($this->session->isStarted()) {
			$this->session->start();
		}
	}
	/**
	 * Writes the session.
	 *
	 * @return void
	 */
	protected function writeSession()
	{
		$this->session->save();
	}
	/**
	 * Gets a value from the session and returns it.
	 *
	 * @return mixed.
	 */
	protected function getSession()
	{
		if ($this->session->has($this->key)) {
			$value = $this->session->get($this->key);
			return $value;
		}
	}
	/**
	 * Interacts with the Silex Session to set a property on it.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	protected function setSession($value)
	{
		$this->session->set($this->key, $value);
	}
	/**
	 * Forgets the Sentinel session from the Silex Session.
	 *
	 * @return void
	 */
	protected function forgetSession()
	{
		if ($this->session->has($this->key)) {
			$this->session->remove($this->key);
		}
	}
}