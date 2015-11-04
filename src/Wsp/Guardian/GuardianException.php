<?php
/**
 * Created by PhpStorm.
 * User: assarte
 * Date: 2015.11.02.
 * Time: 8:28
 */

namespace Wsp\Guardian;

use Wsp\Guardian;

class GuardianException extends \Exception{
	/**
	 * @var Guardian
	 */
	protected $guardian;

	public function __construct(Guardian $guardian, $message = '', $code = 0, \Exception $previous = null) {
		$this->guardian = $guardian;
		parent::__construct(sprintf($message, $guardian->getPattern()), $code, $previous);
	}

	public function getGuardian() {
		return $this->guardian;
	}
}