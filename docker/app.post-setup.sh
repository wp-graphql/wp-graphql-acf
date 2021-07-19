#!/bin/bash

# Activate wp-graphql
wp plugin install https://github.com/wp-premium/advanced-custom-fields-pro/archive/refs/heads/master.zip --activate --allow-root
wp plugin install wp-graphql --allow-root --activate
wp plugin activate wp-graphql-acf --allow-root

# Set pretty permalinks.
wp rewrite structure '/%year%/%monthnum%/%postname%/' --allow-root

wp db export "${DATA_DUMP_DIR}/dump.sql" --allow-root

# If maintenance mode is active, de-activate it
if $( wp maintenance-mode is-active --allow-root ); then
  echo "Deactivating maintenance mode"
  wp maintenance-mode deactivate --allow-root
fi
