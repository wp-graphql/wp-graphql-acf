############################################################################
# Container for running Codeception tests on a WPGraphQL Docker instance. #
############################################################################

# Using the 'DESIRED_' prefix to avoid confusion with environment variables of the same name.
FROM wpgraphql-acf-app:latest

LABEL author=jasonbahl
LABEL author_uri=https://github.com/jasonbahl

SHELL [ "/bin/bash", "-c" ]

# Redeclare ARGs and set as environmental variables for reuse.
ARG WP_VERSION
ARG PHP_VERSION
ARG USE_XDEBUG
ENV USING_XDEBUG=${USE_XDEBUG}

# Install php extensions
RUN docker-php-ext-install pdo_mysql

# Install PCOV and XDebug
# This is needed for Codeception / PHPUnit to track code coverage
RUN if [[ "$USING_XDEBUG" ]]; then \
    apt-get install zip unzip -y && \
    pecl install pcov && \
    docker-php-ext-enable pcov && \
    rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "pcov.enabled=1" >> /usr/local/etc/php/php.ini ;\
    yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini; \
fi

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Add composer global binaries to PATH
ENV PATH "$PATH:~/.composer/vendor/bin"



# Configure php
RUN echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/app-entrypoint.sh

# Set up entrypoint
WORKDIR    /var/www/html/wp-content/plugins/wp-graphql-acf
COPY       docker/testing.entrypoint.sh /usr/local/bin/testing-entrypoint.sh
RUN        chmod 755 /usr/local/bin/testing-entrypoint.sh
ENTRYPOINT ["testing-entrypoint.sh"]
