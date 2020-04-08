#!/bin/bash

wp_bootstrap() {

	WP_DOMAIN=wp.test
	WP_URL="https://$WP_DOMAIN"
	WP_ADMIN_USERNAME=admin
	WP_ADMIN_PASSWORD=password
	WP_ADMIN_EMAIL="admin@$WP_DOMAIN"
	DB_HOST=mysql
	DB_NAME=acceptance_tests
	DB_USER=root
	DB_PASSWORD=root
	TABLE_PREFIX=wp_

	echo "Preparing WordPress"

	cd /wordpress

	echo "Making sure permissions are correct"

	# make sure permissions are correct (maybe can be avoided with https://stackoverflow.com/a/56990338).
	chown www-data:www-data /wordpress /wordpress/wp-content /wordpress/wp-content/plugins
	chmod 755 /wordpress /wordpress /wordpress/wp-content /wordpress/wp-content/plugins

	echo "Making sure the database server is up and running"

	while ! mysqladmin ping -h$DB_HOST --silent; do

		echo "Waiting for the database server (host: $DB_HOST)"
		sleep 1
	done

	echo 'The database server is ready'

	echo "Creating acceptance_tests database if it doesn't exist"
	mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS acceptance_tests"

	echo "Creating integration_tests database if it doesn't exist"
	mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS integration_tests"

	if [ ! -f wp-config.php ]; then

		echo "Creating wp-config.php"

		# we can't use wp core commands if the wp-config.php file is not present
		wp config create --dbhost=$DB_HOST --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASSWORD
	fi

	# Make sure WordPress is installed.
	if ! $(wp core is-installed); then

		echo "Installing WordPress"

		wp core install --url=$WP_URL --title=tests --admin_user=$WP_ADMIN_USERNAME --admin_password=$WP_ADMIN_PASSWORD --admin_email=$WP_ADMIN_EMAIL

		# overwrite existing configuration to make sure we are using the correct values
		wp core config --dbhost=$DB_HOST --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASSWORD --dbprefix=$TABLE_PREFIX --force --extra-php <<'PHP'
// allows URLs to work while accessing the WordPress service from the host using mapped ports
if ( 8443 === (int) $_SERVER['SERVER_PORT'] || 8080 === (int) $_SERVER['SERVER_PORT'] ) {
	$protocol = 8443 === (int) $_SERVER['SERVER_PORT'] ? 'https' : 'http';
	define( 'WP_HOME', "{$protocol}://{$_SERVER['HTTP_HOST']}" );
	define( 'WP_SITEURL', "{$protocol}://{$_SERVER['HTTP_HOST']}" );
}
PHP
	fi

	wp core update-db
	wp rewrite structure '/%postname%/' --hard

	wp db export fresh-install.sql


	echo "Installing and configuring WooCommerce"

	wp plugin install woocommerce --activate

	wp option update woocommerce_store_address "177 Huntington Ave Ste 1700"
	wp option update woocommerce_store_address_2 "70640"
	wp option update woocommerce_store_city "Boston"
	wp option update woocommerce_store_postcode "02115-3153"
	wp option update woocommerce_default_country "US:MA"
	wp option update woocommerce_currency "USD"

	# remove WooCommerce admin notices
	wp option update woocommerce_admin_notices [] --format=json

	wp wc tool run db_update_routine --user=admin
	wp wc tool run install_pages --user=admin

	# prevent WooCommerce redirection to Setup Wizard
	wp transient delete _wc_activation_redirect

	# run action-scheduler to make sure all necessary tables are created
	wp action-scheduler run

	wp theme install --activate storefront


	echo "Preparing plugin"

	cd /project

	# install vendor
	composer install --prefer-dist

	wp plugin activate $PLUGIN_DIR --path=/wordpress

	# allow each plugin to configure the WordPress instance
	if [ -f wp-bootstrap.sh ]; then
		source wp-bootstrap.sh
	fi


	echo "Exporting acceptance_tests database into tests/_data/dump.sql"

	cd /wordpress

	mkdir -p /project/tests/_data

	wp db export /project/tests/_data/dump.sql


	echo "Importing tests/_data/dump.sql into integration_tests database"

	wp db import --dbuser=$DB_USER --dbpass=$DB_PASSWORD --host=$DB_HOST --database=integration_tests /project/tests/_data/dump.sql


	echo "WordPress is ready"
}


if [[ "$1" == bootstrap ]]; then

	wp_bootstrap

elif [[ "$1" == start ]]; then

	wp_bootstrap

	# keep the service running...
	exec tail -f /dev/null

else

	# allow one-off command execution
	exec "$@"

fi
