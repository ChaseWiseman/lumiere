<?php

namespace Codeception\Module;

/**
 * The WooCommerce Browser module.
 *
 * Extends WPWebDriver to add WooCommerce-specific methods for easier shop navigation.
 */
class WooCommerceBrowser extends WPWebDriver {


	/**
	 * Directs the test user to the cart page.
	 */
	public function amOnCartPage() {

		$this->amOnUrl( wc_get_cart_url() );
	}


}
