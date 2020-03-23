## Getting started
Lumiere is designed to get a functioning test suite up and running with minimal plugin-level configuration. The majority of the options defined in Codeception's `.yml` configuration files is the same for all plugins, so this consolidates them into a shared configuration in `/configs`. Improvements can be inherited by all plugins with a composer update.

### Prerequisites
- A fresh and isolated WordPress installation. **IMPORTANT:** <u>Do not use this installation for any other purpose, including performing manual tests. It should be reserved to acceptance tests</u>. Any changes might cause a problem with the test suite; any tests run by the suite might be destructive of the changes you made as well. To run manual tests you should dedicate a different installation.
- [Selenium](https://www.seleniumhq.org/download/) for acceptance tests

### New installation
After installing via composer:
1. `$ vendor/bin/lumiere up`
1. Answer a series of configuration questions about your local WordPress installation(s).
1. Commit all of the resulting generated files. Local files will already be ignored when appropriate.
1. `cd` or SSH into your local WordPress installation:
1. If WooCommerce is not already installed, `$ wp plugin install woocommerce --activate`
1. Copy a build of your plugin to the WordPress install
1. Activate the plugin: `$ wp plugin activate {your-plugin-slug}`
1. Make any further database changes that the plugin requires for _every_ acceptance test, e.g. enabling pretty permalinks
1. Dump the database: `$ wp db export path/to/your/plugin/repo/tests/_data/dump.sql`
1. Commit the SQL dump file
1. Add some tests!

For now, tests can be run using standard Codeception commands:
- `vendor/bin/codecept run admin`
- `vendor/bin/codecept run frontend`
- `vendor/bin/codecept run integration`
- `vendor/bin/codecept run unit`

### Suites
On installation, this library configures 4 common suites for running different types of tests:
- Unit tests
- Integration tests
- Admin acceptance tests
- Frontend acceptance tests

As with the Codeception library that this library is built on, any number or combination of test suites can be created and configured. The above four are likely to be the most common, but aren't required.

### Configuration
A number of configuration files are generated automatically, and all can be overridden as needed.

### Global configuration
- `codeception.dist.yml`
    - Holds any configuration values that are specific to the plugin and should also be default for everyone running tests
    - Inherits and overrides the base `configs/codeception.yml` configuration in this package
    - Should be committed
- `codeception.yml`
    - Holds any configuration values that are specific to your local environment
    - Inherits and overrides `codeception.dist.yml`
    - Should not be committed
    
### Suite configuration
Each generated test suite has its own set of configuration files.

- `{suite}.suite.dist.yml`
    - Holds any configuration values that are specific to the plugin and should also be default for everyone running the test suite
    - Inherits and overrides the base `configs/{suite}.suite.yml` configuration in this package
    - Should be committed
- `{suite}.suite.yml`
    - Holds any configuration values that are specific to your local environment
    - Inherits and overrides `{suite}.suite.dist.yml`
    - Should not be committed
    
### Environment configuration
- `.env.lumiere`
    - Holds all variables for your local WordPress installation
    - Should not be committed
- `.env.lumiere.dist`
    - Holds all variables that are specific to the plugin and should also be available for everyone running the test suite
    - Should be committed
    
## Modules

### WooCommerceDB
A wrapper for WPDb, this provides common methods that are often used in acceptance tests to generate and interact with WooCommerce database data. This can be used for things like creating products and orders.

### WooCommerceBrowser
A wrapper for WPWebDriver, this adds common methods for various WooCommerce-related acceptance test actions like going directly to the card or product pages.

