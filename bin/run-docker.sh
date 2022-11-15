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
WP_VERSION=${WP_VERSION-5.9}
PHP_VERSION=${PHP_VERSION-8.0}
DOCKER_REGISTRY=${DOCKER_REGISTRY-ghcr.io/wp-graphql/}

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
                    docker build $BUILD_NO_CACHE -f docker/Dockerfile \
                        -t wpgraphql-acf-app:${TAG}-wp${WP_VERSION}-php${PHP_VERSION} \
                        --build-arg WP_VERSION=${WP_VERSION} \
                        --build-arg PHP_VERSION=${PHP_VERSION} \
                        --build-arg DOCKER_REGISTRY=${DOCKER_REGISTRY} \
                        .
                    ;;
                t )
                    echo "Build app"
                    docker build $BUILD_NO_CACHE -f docker/Dockerfile \
                        -t wpgraphql-acf-app:${TAG}-wp${WP_VERSION}-php${PHP_VERSION} \
                        --build-arg WP_VERSION=${WP_VERSION} \
                        --build-arg PHP_VERSION=${PHP_VERSION} \
                        --build-arg DOCKER_REGISTRY=${DOCKER_REGISTRY} \
                        .
                    echo "Build testing"
                    docker build $BUILD_NO_CACHE -f docker/Dockerfile.testing \
                        -t wpgraphql-acf-testing:${TAG}-wp${WP_VERSION}-php${PHP_VERSION} \
                        --build-arg WP_VERSION=${WP_VERSION} \
                        --build-arg PHP_VERSION=${PHP_VERSION} \
                        --build-arg DOCKER_REGISTRY=${DOCKER_REGISTRY} \
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
                    WP_VERSION=${WP_VERSION} PHP_VERSION=${PHP_VERSION} docker compose up app
                    ;;
                t )
                    docker-compose run --rm \
                        -e COVERAGE=${COVERAGE-} \
                        -e USING_XDEBUG=${USING_XDEBUG-} \
                        -e DEBUG=${DEBUG-} \
                        -e WP_VERSION=${WP_VERSION} \
                        -e PHP_VERSION=${PHP_VERSION} \
                        -e DOCKER_REGISTRY=${DOCKER_REGISTRY} \
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
