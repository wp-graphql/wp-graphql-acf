#!/usr/bin/env bash

source .env

print_usage_instruction() {
	echo "Ensure that .env file exist in project root directory exists."
	echo "And run the following 'composer install-wp-tests' in the project root directory"
	exit 1
}

if [[ -z "$TEST_DB_NAME" ]]; then
	echo "TEST_DB_NAME not found"
	print_usage_instruction
else
	DB_NAME=$TEST_DB_NAME
fi
if [[ -z "$TEST_DB_USER" ]]; then 
	echo "TEST_DB_USER not found"
	print_usage_instruction
else
	DB_USER=$TEST_DB_USER
fi
if [[ -z "$TEST_DB_PASSWORD" ]]; then 
	DB_PASS=""
else
	DB_PASS=$TEST_DB_PASSWORD
fi
if [[ -z "$TEST_DB_HOST" ]]; then 
	DB_HOST=localhost
else
	DB_HOST=$TEST_DB_HOST
fi
if [ -z "$SKIP_DB_CREATE" ]; then 
	SKIP_DB_CREATE=false
fi

PLUGIN_DIR=$(pwd)
WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wp-graphql-acf/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wp-graphql-acf/wordpress/}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_wp() {

	if [ -d $WP_CORE_DIR ]; then
		return;
	fi

	mkdir -p $WP_CORE_DIR

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		mkdir -p /tmp/wordpress-nightly
		download https://wordpress.org/nightly-builds/wordpress-latest.zip  /tmp/wordpress-nightly/wordpress-nightly.zip
		unzip -q /tmp/wordpress-nightly/wordpress-nightly.zip -d /tmp/wordpress-nightly/
		mv /tmp/wordpress-nightly/wordpress/* $WP_CORE_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
		tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
	fi

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WP_TESTS_DIR ]; then
		# set up testing suite
		mkdir -p $WP_TESTS_DIR
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
		# remove all forward slashes in the end
		WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|$DB_HOST|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi

}

install_db() {

	if [ ${SKIP_DB_CREATE} = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

configure_wordpress() {

    cd $WP_CORE_DIR
    wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --skip-check --force=true
    wp core install --url=wpgraphql.test --title="WPGraphQL ACF Tests" --admin_user=admin --admin_password=password --admin_email=admin@wpgraphql.test
    wp rewrite structure '/%year%/%monthnum%/%postname%/'
}

install_wpgraphql() {
	if [ ! -d $WP_CORE_DIR/wp-content/plugins/wp-graphql ]; then
		echo "Cloning WPGraphQL"
		git clone https://github.com/wp-graphql/wp-graphql.git $WP_CORE_DIR/wp-content/plugins/wp-graphql
	fi
	echo "Activating WPGraphQL"
	wp plugin activate wp-graphql
}

install_acf_pro() {
	if [ ! -d $WP_CORE_DIR/wp-content/plugins/advanced-custom-fields-pro ]; then
		echo "Cloning ACF PRO"
		git clone https://github.com/wp-premium/advanced-custom-fields-pro.git $WP_CORE_DIR/wp-content/plugins/advanced-custom-fields-pro
	fi
	echo "Cloning ACF PRO"
	wp plugin activate advanced-custom-fields-pro
}

activate_plugins() {

    # Add this repo as a plugin to the repo
	if [ ! -d $WP_CORE_DIR/wp-content/plugins/wp-graphql-acf ]; then
    	ln -s $PLUGIN_DIR $WP_CORE_DIR/wp-content/plugins/wp-graphql-acf
	fi

    cd $WP_CORE_DIR

    # Flush the permalinks
    wp rewrite flush

    # Export the db for codeception to use
    wp db export $PLUGIN_DIR/tests/_data/dump.sql
}

install_wp
install_test_suite
install_db
configure_wordpress
install_wpgraphql
install_acf_pro
activate_plugins
