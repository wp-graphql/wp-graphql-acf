#!/usr/bin/env bash

set -eu

##
# Use this script through Composer scripts in the package.json.
# To quickly build and run the docker-compose scripts for an app or automated testing
# run the command below after run `composer install --no-dev` with the respectively
# flag for what you need.
##
print_usage_instructions() {
	echo "Usage: $0 [build|run] [-c|-a|-t]";
    echo "    Build or run app or testing images."
    echo "       -c  Specify as first option with [build] command to build images without cache."
	echo "       -a  Spin up a WordPress installation.";
	echo "       -t  Run the automated tests.";
	exit 1
}

if [ -z "$1" ]; then
	print_usage_instructions
fi

TAG=${TAG-latest}
WP_VERSION=${WP_VERSION-5.6}
PHP_VERSION=${PHP_VERSION-7.4}

BUILD_NO_CACHE=${BUILD_NO_CACHE-}

if [[ ! -f ".env" ]]; then
  echo "No .env file was detected. .env.dist has been copied to .env"
  echo "Open the .env file and enter values to match your local environment"
  cp .env.dist .env
fi

subcommand=$1; shift
case "$subcommand" in
    "build" )
        while getopts ":cat" opt; do
            case ${opt} in
                c )
                    echo "Build without cache"
                    BUILD_NO_CACHE=--no-cache
                    ;;
                a )
                    echo "Build app"
                    docker build $BUILD_NO_CACHE -f docker/app.Dockerfile \
                        -t wpgraphql-acf-app:latest \
                        --build-arg WP_VERSION=${WP_VERSION-5.4} \
                        --build-arg PHP_VERSION=${PHP_VERSION-7.4} \
                        .
                    ;;
                t )
                    echo "Build app"
                    docker build $BUILD_NO_CACHE -f docker/app.Dockerfile \
                        -t wpgraphql-acf-app:latest \
                        --build-arg WP_VERSION=${WP_VERSION-5.4} \
                        --build-arg PHP_VERSION=${PHP_VERSION-7.4} \
                        .
                    echo "Build testing"
                    docker build $BUILD_NO_CACHE -f docker/testing.Dockerfile \
                        -t wpgraphql-acf-testing:latest \
                        .
                    ;;
                \? ) print_usage_instructions;;
                * ) print_usage_instructions;;
            esac
        done
        shift $((OPTIND -1))
        ;;
    "run" )
        while getopts ":at" opt; do
            case ${opt} in
                a )
                    docker-compose up --scale testing=0
                    ;;
                t )
                    docker-compose run --rm \
                        -e COVERAGE=${COVERAGE-} \
                        -e USING_XDEBUG=${USING_XDEBUG-} \
                        -e DEBUG=${DEBUG-} \
                        -e WPGRAPHQL_VERSION=${WPGRAPHQL_VERSION-} \
                        testing --scale app=0
                    ;;
                \? ) print_usage_instructions;;
                * ) print_usage_instructions;;
            esac
        done
        shift $((OPTIND -1))
        ;;

    \? ) print_usage_instructions;;
    * ) print_usage_instructions;;
esac
