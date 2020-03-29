FROM php:7.4-cli-buster

# Install required system packages
RUN apt-get update && \
    apt-get -y install \
            git \
            zlib1g-dev \
            libssl-dev \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
			libzip-dev \
            default-mysql-client \
            sudo less \
            zip unzip \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install php extensions
RUN docker-php-ext-install \
    bcmath \
    gd \
    zip

RUN docker-php-ext-install -j$(nproc) iconv \
        && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
        && docker-php-ext-install -j$(nproc) gd

# Add mysql driver required for wp-browser
RUN docker-php-ext-install mysqli pdo_mysql

# Configure php
RUN echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin
RUN composer global require --optimize-autoloader \
        "hirak/prestissimo"

# Add WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin

# Allows WP CLI to run with the right permissions.
RUN echo "#\!/bin/bash\nsudo -E -u www-data /usr/local/bin/wp-cli.phar \"\$@\"" > /usr/local/bin/wp
RUN chmod +x /usr/local/bin/wp

ADD docker-entrypoint.sh /usr/local/bin/

RUN ["chmod", "+x", "/usr/local/bin/docker-entrypoint.sh"]

WORKDIR /project

ENTRYPOINT [ "docker-entrypoint.sh" ]

CMD ["start"]
