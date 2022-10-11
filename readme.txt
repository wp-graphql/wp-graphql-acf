=== WPGraphQL for Advanced Custom Fields ===
Contributors: WPGraphQL, jasonbahl
Donate link: https://wpgraphql.com/acf
Tags: WPGraphQL, GraphQL, API, Advanced Custom Fields, ACF
Requires at least: 5.0
Tested up to: 5.1.1
Stable tag: 0.6.1
License: GPL-3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WPGraphQL for Advanced Custom Fields exposes ACF Field Groups and Fields to the WPGraphQL Schema.

== Description ==

WPGraphQL for Advanced Custom Fields exposes ACF Field Groups and Fields to the WPGraphQL Schema,
allowing for interacting with ACF field data using GraphQL Queries.

== Changelog ==

SEE: https://github.com/wp-graphql/wp-graphql-acf/releases

== Upgrade Notice ==

= 0.1.1 =
ACF Field groups were not properly being added to the GraphQL Schema for Custom Post Types. This
addresses that issue, so now Field groups that are set to "show_in_graphql" and are assigned to a
Custom Post Type that's also set to "show_in_graphql" will now be present in the Schema.
