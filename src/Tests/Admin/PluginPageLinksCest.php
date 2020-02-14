<?php

namespace SkyVerge\Lumiere\Tests\Admin;

use SkyVerge\Lumiere\Tests\AcceptanceBase;
use Codeception\Exception\ModuleException;

/**
 * The best set of tests for the plugin action links of any plugin.
 *
 * get_settings_links() can be extended to return the expected set of links.
 */
abstract class PluginPageLinksCest extends AcceptanceBase {


	/**
	 * Test the plugin action links.
	 *
	 * @throws ModuleException
	 */
	public function tryPluginActionLinks() {

		$this->tester->wantTo( 'See the plugin settings link(s)' );

		$this->tester->loginAsAdmin();

		$this->tester->amOnPluginsPage();

		$plugin         = $this->get_plugin();
		$settings_links = $this->get_settings_links();

		if ( ! empty( $settings_links ) ) {

			foreach ( $settings_links as $text => $url ) {
				$this->tester->seeLink( $text, $url );
			}
		}

		$this->tester->seeLink( 'Docs', $plugin->get_documentation_url() );

		$this->tester->seeLink( 'Support', $plugin->get_support_url() );
	}


	/**
	 * Get the expected plugin settings links.
	 *
	 * @return array
	 */
	protected function get_settings_links() {

		$links = [];

		if ( $this->get_plugin()->get_settings_url() ) {
			$links[ 'Configure' ] = $this->get_plugin()->get_settings_url();
		}

		return $links;
	}


}
