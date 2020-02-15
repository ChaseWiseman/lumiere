<?php
namespace Codeception\Module;

use Codeception\TestInterface;

/**
 * The WooCommerce DB module.
 *
 * Extends WPDb to add WooCommerce-specific methods for easier shop data creation.
 */
class WooCommerceDB extends WPDb {


	/**
	 * Runs before each test.
	 *
	 * Performs any base WooCommerce configuration to avoid the need to maintain them in a SQL dump.
	 *
	 * @param TestInterface $test
	 */
	public function _before( TestInterface $test ) {

		parent::_before( $test );

		// ensure the base pages are set
		\WC_Install::create_pages();
	}


	/**
	 * Creates a simple product in the database.
	 *
	 * @param array $props product properties
	 * @return \WC_Product_Simple
	 */
	public function haveSimpleProductInDatabase( array $props = [] ) : \WC_Product_Simple {

		$props = wp_parse_args( $props, [
			'name'          => 'Simple Product',
			'regular_price' => 1.00,
			'virtual'       => false,
		] );

		$product = new \WC_Product_Simple();

		$product->set_props( $props );

		$product->save();

		return $product;
	}


}
