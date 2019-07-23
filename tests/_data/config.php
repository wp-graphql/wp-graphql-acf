<?php
/**
 * Disable autoloading while running tests, as the test
 * suite already bootstraps the autoloader and creates
 * fatal errors when the autoloader is loaded twice
 */
define( 'WPGRAPHQL_JWT_AUTHENTICATION_AUTOLOAD', false );
define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', 'codeception_tests' );
define( 'CODECEPTION_REMOTE_COVERAGE', true );