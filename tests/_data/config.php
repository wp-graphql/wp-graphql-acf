<?php
/**
 * Disable autoloading while running tests, as the test
 * suite already bootstraps the autoloader and creates
 * fatal errors when the autoloader is loaded twice
 */
define( 'CODECEPTION_REMOTE_COVERAGE', true );
define( 'GRAPHQL_DEBUG', true );
