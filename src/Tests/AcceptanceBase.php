<?php

namespace SkyVerge\Lumiere\Tests;

use Codeception\Actor;
use Codeception\Module\WPWebDriver;

/**
 * The base acceptance test class.
 */
abstract class AcceptanceBase {


	/** @var WPWebDriver|Actor our tester */
	protected $tester;


	/**
	 * Runs before each test.
	 *
	 * @param WPWebDriver|Actor $I tester instance
	 */
	public function _before( $I ) {

		$this->tester = $I;
	}


	/**
	 * Gets the plugin instance.
	 *
	 * @return object
	 */
	abstract protected function get_plugin();


}
