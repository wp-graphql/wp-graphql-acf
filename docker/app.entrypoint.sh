#!/bin/bash

if [ "$USING_XDEBUG" == "1"  ]; then
    echo "Enabling XDebug 3"
    mv /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/
fi

# Run WordPress docker entrypoint.
. docker-entrypoint.sh 'apache2'

set +u

# Ensure mysql is loaded
dockerize -wait tcp://${DB_HOST}:${DB_HOST_PORT:-3306} -timeout 1m

# Config WordPress
if [ ! -f "${WP_ROOT_FOLDER}/wp-config.php" ]; then
    wp config create \
        --path="${WP_ROOT_FOLDER}" \
        --dbname="${DB_NAME}" \
        --dbuser="${DB_USER}" \
        --dbpass="${DB_PASSWORD}" \
        --dbhost="${DB_HOST}" \
        --dbprefix="${WP_TABLE_PREFIX}" \
        --skip-check \
        --quiet \
        --allow-root
fi

# Install WP if not yet installed
if ! $( wp core is-installed --allow-root ); then
	wp core install \
		--path="${WP_ROOT_FOLDER}" \
		--url="${WP_URL}" \
		--title='Test' \
		--admin_user="${ADMIN_USERNAME}" \
		--admin_password="${ADMIN_PASSWORD}" \
		--admin_email="${ADMIN_EMAIL}" \
		--allow-root
fi

# Install and activate WPGraphQL

echo "wpgraphql version... ${WPGRAPHQL_VERSION}"
echo "${PLUGINS_DIR}"

if [ ! -f "${PLUGINS_DIR}/wp-graphql/wp-graphql.php" ]; then
    # WPGRAPHQL_VERSION in format like v1.2.3
    echo ${WPGRAPHQL_VERSION}
    if [[ -z ${WPGRAPHQL_VERSION} ]]; then
        echo "installing latest WPGraphQL from WordPress.org"
        wp plugin install wp-graphql --activate --allow-root
    else
    	echo "Installing WPGraphQL from Github"
        git clone https://github.com/wp-graphql/wp-graphql.git "${PLUGINS_DIR}/wp-graphql"
        cd "${PLUGINS_DIR}/wp-graphql"
        echo "checking out WPGraphQL tag/${WPGRAPHQL_VERSION}"
        git checkout tags/${WPGRAPHQL_VERSION} -b master
        composer install --no-dev
        cd ${WP_ROOT_FOLDER}
        echo "activating WPGraphQL"
        wp plugin activate wp-graphql --allow-root
        wp plugin list --allow-root
    fi
else
    wp plugin activate wp-graphql --allow-root
fi

# Install and activate ACF Pro
if [ ! -f "${PLUGINS_DIR}/advanced-custom-fields-pro/acf.php" ]; then
    wp plugin install \
        https://github.com/wp-premium/advanced-custom-fields-pro/archive/master.zip \
        --activate --allow-root
else
    wp plugin activate advanced-custom-fields-pro --allow-root
fi

# Install and activate WPGatsby
wp plugin activate wp-graphql-acf --allow-root

# Set pretty permalinks.
wp rewrite structure '/%year%/%monthnum%/%postname%/' --allow-root

wp db export "${PROJECT_DIR}/tests/_data/dump.sql" --allow-root

exec "$@"
