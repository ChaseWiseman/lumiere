<?php

namespace Codeception\Template;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Template for the `codecept init` command.
 */
class Lumiere extends Wpbrowser {


	/** @var string the unit test suite name */
	const SUITE_UNIT = 'unit';

	/** @var string the unit test suite actor */
	const ACTOR_UNIT = 'Unit';

	/** @var string the integration test suite name */
	const SUITE_INTEGRATION = 'integration';

	/** @var string the integration test suite actor */
	const ACTOR_INTEGRATION = 'Integration';

	/** @var string the admin test suite name */
	const SUITE_ADMIN = 'admin';

	/** @var string the admin test suite actor */
	const ACTOR_ADMIN = 'Admin';

	/** @var string the frontend test suite name */
	const SUITE_FRONTEND = 'frontend';

	/** @var string the admin test suite actor */
	const ACTOR_FRONTEND = 'Frontend';


	/** @var string filename for the dist .env */
	protected $distEnvFilename = '.env.lumiere.dist';

	/** @var array files to add to .gitignore after setup */
	protected $toIgnore = [];


	/**
	 * Initializes the build commands.
	 *
	 * @param bool $interactive unused
	 */
	public function setup( $interactive = true ) {

		// local config env file
		$this->envFileName = '.env.lumiere';

		$this->say( "Bootstrapping Lumiere\n" );

		$this->createDirs();

		$installation_data = $this->gatherInstallationData();

		$this->createEnvFiles( $installation_data );

		$this->loadEnvFile();

		$this->createGlobalConfig();

		$this->createUnitTestSuite();

		$this->createIntegrationSuite();

		$this->createAdminSuite();

		$this->createFrontendSuite();

		$this->updateGitIgnore();

		$this->saySuccess( 'You did it!' );

		// TODO: lots more info to add here.
	}


	/**
	 * Loads environment variables.
	 *
	 * @param string $filename .env filename, if any. Defaults to the value of $envFileName
	 */
	protected function loadEnvFile( $filename = '' ) {

		if ( $filename ) {

			if ( file_exists( $this->workDir . DIRECTORY_SEPARATOR .  $filename ) ) {
				$dotEnv = \Dotenv\Dotenv::create( $this->workDir, $filename );
				$dotEnv->load();
			}

		} else {

			parent::loadEnvFile();
		}
	}


	/**
	 * Gather installation data via CLI prompts.
	 *
	 * @return array
	 */
	protected function gatherInstallationData() : array {

		$this->loadEnvFile( $this->envFileName );

		$installation_data = [];

		$wp_root = $this->ask(
			'Where is your test instance of WordPress installed?',
			getenv( 'WP_ROOT_FOLDER' ) ?: '/var/www/wp'
		);

		$installation_data['WP_ROOT_FOLDER'] = $this->normalizePath( $wp_root );

		$wp_url = $this->ask(
			'What is the URL of the test site?',
			getenv( 'WP_URL' ) ?: 'https://wp.test'
		);

		$installation_data['WP_URL']    = $wp_url;
		$installation_data['WP_DOMAIN'] = str_replace( [ 'https://', 'http://' ], '', $wp_url );

		$installation_data['WP_ADMIN_PATH'] = getenv( 'WP_ADMIN_PATH' ) ?: '/wp-admin';

		$installation_data['WP_ADMIN_EMAIL'] = $this->ask(
			'What is the email address for your admin user?',
			getenv( 'WP_ADMIN_EMAIL' ) ?: 'admin@wp.test'
		);

		$installation_data['WP_ADMIN_USERNAME'] = $this->ask(
			'What is the username for your admin user?',
			getenv( 'WP_ADMIN_USERNAME' ) ?: 'admin'
		);

		$installation_data['WP_ADMIN_PASSWORD'] = $this->ask(
			'What is the password for your admin user?',
			getenv( 'WP_ADMIN_PASSWORD' ) ?: 'admin'
		);

		$installation_data['ACCEPTANCE_DB_NAME'] = $this->ask(
			'What is the name of the database you\'ll use for acceptance tests?',
			getenv( 'ACCEPTANCE_DB_NAME' ) ?: 'acceptance_tests'
		);

		$installation_data['ACCEPTANCE_DB_HOST'] = $this->ask(
			'What is the host of the database you\'ll use for acceptance tests?',
			getenv( 'ACCEPTANCE_DB_HOST' ) ?: 'localhost'
		);

		$installation_data['ACCEPTANCE_DB_USER'] = $this->ask(
			'What is the username for the database you\'ll use for acceptance tests?',
			getenv( 'ACCEPTANCE_DB_USER' ) ?: 'root'
		);

		$installation_data['ACCEPTANCE_DB_PASSWORD'] = $this->ask(
			'What is the username for the database you\'ll use for acceptance tests?',
			getenv( 'ACCEPTANCE_DB_PASSWORD' ) ?: 'root'
		);

		$installation_data['ACCEPTANCE_TABLE_PREFIX'] = $this->ask(
			'What is the table prefix for the database you\'ll use for acceptance tests?',
			getenv( 'ACCEPTANCE_TABLE_PREFIX' ) ?: 'wp_'
		);

		$installation_data['INTEGRATION_DB_NAME'] = $this->ask(
			'What is the name of the database you\'ll use for integration tests?',
			getenv( 'INTEGRATION_DB_NAME' ) ?: 'integration_tests'
		);

		$installation_data['INTEGRATION_DB_HOST'] = $this->ask(
			'What is the host of the database you\'ll use for integration tests?',
			getenv( 'INTEGRATION_DB_HOST' ) ?: 'localhost'
		);

		$installation_data['INTEGRATION_DB_USER'] = $this->ask(
			'What is the username for the database you\'ll use for integration tests?',
			getenv( 'INTEGRATION_DB_USER' ) ?: 'root'
		);

		$installation_data['INTEGRATION_DB_PASSWORD'] = $this->ask(
			'What is the username for the database you\'ll use for integration tests?',
			getenv( 'INTEGRATION_DB_PASSWORD' ) ?: 'root'
		);

		$installation_data['INTEGRATION_TABLE_PREFIX'] = $this->ask(
			'What is the table prefix for the database you\'ll use for integration tests?',
			getenv( 'INTEGRATION_TABLE_PREFIX' ) ?: 'wp_'
		);

		// maybe use existing values if the env file already exists
		$this->loadEnvFile( $this->distEnvFilename );

		$plugin_name      = $this->ask( 'What is the name of the plugin?', getenv( 'PLUGIN_NAME' ) ?:  null );
		$plugin_directory = $this->ask( 'What is the plugin root directory when installed in WordPress?', getenv( 'PLUGIN_DIR' ) ?:  basename( getcwd() ) );
		$plugin_file      = $this->ask( 'What is the main filename for the plugin?', getenv( 'PLUGIN_FILENAME' ) ?:  "{$plugin_directory}.php" );

		$installation_data['plugin'] = [
			'name'      => $plugin_name,
			'directory' => $plugin_directory,
			'filename'  => $plugin_file,
		];

		// TODO: ask about other plugins?
		// TODO: ask about the framework? Install basic FW tests

		return $installation_data;
	}


	/**
	 * Creates the necessary .env files
	 *
	 * @param array $installation_data installation data, generated by the CLI prompts
	 */
	protected function createEnvFiles( array $installation_data ) {

		$filename = $this->workDir . DIRECTORY_SEPARATOR . $this->envFileName;

		$this->writeEnvFile( $filename, $installation_data );

		$this->toIgnore[] = $this->envFileName;

		if ( ! empty( $installation_data['plugin'] ) ) {

			$data = [
				'PLUGIN_NAME'     => $installation_data['plugin']['name'],
				'PLUGIN_DIR'      => $installation_data['plugin']['directory'],
				'PLUGIN_FILENAME' => $installation_data['plugin']['filename'],
			];

			$filename = $this->workDir . DIRECTORY_SEPARATOR . $this->distEnvFilename;

			$this->writeEnvFile( $filename, $data );
		}
	}


	/**
	 * Writes an .env file.
	 *
	 * @param string $filename desired filename
	 * @param array $data data to write
	 */
	public function writeEnvFile( $filename, array $data ) {

		$lines = [];

		foreach ( $data as $key => $value ) {

			if ( \is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} elseif ( null === $value ) {
				$value = 'null';
			} elseif ( \is_string( $value ) ) {
				$value = '"' . trim( $value ) . '"';
			} else {
				continue;
			}

			$lines[] = "{$key}={$value}";
		}

		$contents = implode("\n", $lines);

		$written = file_put_contents( $filename, $contents );

		if ( ! $written ) {
			throw new RuntimeException("Could not write {$filename} file!");
		}
	}


	/**
	 * Creates the global codeception config files.
	 */
	public function createGlobalConfig() {

		// create codeception.dist.yml
		$config = [
			'extends' => 'vendor/skyverge/lumiere/configs/codeception.yml',
			'params' => [
				trim( $this->distEnvFilename ),
			],
		];

		$contents = Yaml::dump( $config, 4 );

		$this->createFile( $this->workDir . DIRECTORY_SEPARATOR . 'codeception.dist.yml', $contents );

		// create codeception.yml
		$config = [
			'extensions' => [
				'enabled' => [
					\tad\WPBrowser\Extension\Symlinker::class,
				],
				'config' => [
					\tad\WPBrowser\Extension\Symlinker::class => [
						'mode' => 'plugin',
						'destination' => '%WP_ROOT_FOLDER%/wp-content/plugins/',
					],
				],
			],
			'params' => [
				trim( $this->envFileName ),
			],
		];

		$contents = Yaml::dump( $config, 4 );

		$this->createFile( $this->workDir . DIRECTORY_SEPARATOR . 'codeception.yml', $contents );

		$this->toIgnore[] = 'codeception.yml';
	}


	/**
	 * Generates the unit test suite.
	 *
	 * @param string $actor actor name
	 */
	protected function createUnitTestSuite( string $actor = self::ACTOR_UNIT ) {

		$this->createLumiereSuite( self::SUITE_UNIT, $actor );
	}


	/**
	 * Generates the integration test suite.
	 *
	 * @param string $actor actor name
	 */
	protected function createIntegrationSuite( string $actor = self::ACTOR_INTEGRATION ) {

		$this->createLumiereSuite( self::SUITE_INTEGRATION, $actor );
	}


	/**
	 * Generates the admin acceptance test suite.
	 *
	 * @param string $actor actor name
	 */
	protected function createAdminSuite( string $actor = self::ACTOR_ADMIN ) {

		$this->createLumiereSuite( self::SUITE_ADMIN, $actor );
	}


	/**
	 * Generates the frontend acceptance test suite.
	 *
	 * @param string $actor actor name
	 */
	protected function createFrontendSuite( string $actor = self::ACTOR_FRONTEND ) {

		$this->createLumiereSuite( self::SUITE_FRONTEND, $actor );
	}


	/**
	 * Creates a lumiere-style test suite.
	 *
	 * @param string $name the suite name, such as "integration"
	 * @param string $actor the actor name, such as "Integration"
	 */
	protected function createLumiereSuite( $name, $actor ) {

		$lumiere_config_path = '../vendor/skyverge/lumiere/configs/' . $name . '.suite.yml';

		$local_config = <<<EOF
actor: {$actor}{$this->actorSuffix}
extends: "{$lumiere_config_path}"  # Do not remove this line

# Local {$actor} suite configuration.

# Add custom properties here
EOF;

		// create the suite and local config file
		$this->createSuite( $name, $actor, $local_config );

		// ignore the local config file
		$this->toIgnore[] = "tests/{$name}.suite.yml";

		$helper_name = '\\Helper\\' . $actor;

		$dist_config = <<<EOF
# {$actor} suite configuration.

actor: {$actor}{$this->actorSuffix}
modules:
  enabled:
    - {$helper_name}
EOF;

		// create the dist config file
		$this->createFile( 'tests' . DIRECTORY_SEPARATOR . "$name.suite.dist.yml", $dist_config );
	}


	/**
	 * Updates the project's .gitignore file with our added directories and config files.
	 */
	protected function updateGitIgnore() {

		$filename = $this->workDir . DIRECTORY_SEPARATOR . self::GIT_IGNORE;

		// check the existing gitignore for these entries
		if ( file_exists( $filename ) ) {

			$contents = file_get_contents( $filename );

			foreach ( $this->toIgnore as $key => $file_to_ignore ) {

				if ( false !== strpos( $contents, $file_to_ignore ) ) {
					unset( $this->toIgnore[ $key ] );
				}
			}
		}

		// if all entries are duplicates, bail
		if ( empty( $this->toIgnore ) ) {
			return;
		}

		$toIgnore = implode( "\n", $this->toIgnore );

		$content = <<<EOF

# Lumiere
{$toIgnore}
EOF;

		$filename = $this->workDir . DIRECTORY_SEPARATOR . self::GIT_IGNORE;

		if ( file_exists( $filename ) ) {

			$file = fopen( $filename, 'a' );

			fwrite( $file, $content );

			fclose( $file );

		} else {

			$this->createFile( $filename, $content );
		}
	}


}
