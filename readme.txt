=== WPGraphQL for Advanced Custom Fields ===
Contributors: WPGraphQL, jasonbahl
Donate link: https://wpgraphql.com/acf
Tags: WPGraphQL, GraphQL, API, Advanced Custom Fields, ACF
Requires at least: 5.0
Tested up to: 5.1.1
Stable tag: 0.4.1
License: GPL-3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WPGraphQL for Advanced Custom Fields exposes ACF Field Groups and Fields to the WPGraphQL Schema.

== Description ==

WPGraphQL for Advanced Custom Fields exposes ACF Field Groups and Fields to the WPGraphQL Schema,
allowing for interacting with ACF field data using GraphQL Queries.

== Changelog ==

= 0.1.7 =
* Add support for User Edit and User Register form locations

= 0.1.6 =
* Add support for Single Post / Page location rule for ACF Field Groups
* Fix bug with WYSIWYG field nested within repeaters/flex fields not properly getting wpautop applied
* Fix bug where Relationship fields were throwing errors when an item in the relationship had been deleted. Now the items are not included in the response at all and no errors are thrown

= 0.1.5 =
* Fix bug where field groups would disappear when using acf-local-json

= 0.1.4 =
* Fixes bug with WYSIWYG fields nested in Flex Fields and Repeater Fields not properly resolving
* Adds support for assigning ACF Field Groups to attachments (MediaItems)

= 0.1.3 =

* Adds support for the following locations: Menus, Menu Items, Taxonomies, Comments
* Fixed a bug where the "show_in_graphql" button wasn't showing on Radio Buttons

= 0.1.2 =
* Fixes bug with Nested Fields not properly showing in the Schema. By defualt, fields are not supposed
to be exposed in the Schema if they are not set to "show_in_graphql", however there was a flaw in
logic causing nested fields of Flex Field layouts to not properly be exposed to the Schema. This
fixes that issue, so nested fields of Flex Field layouts can properly be queried and seen in the
Schema.

= 0.1.1 =
* Fixes bug with Field groups not properly being exposed to the Schema for custom post types.

= 0.1.0 =
* Initial public release.


== Upgrade Notice ==

= 0.1.1 =
ACF Field groups were not properly being added to the GraphQL Schema for Custom Post Types. This
addresses that issue, so now Field groups that are set to "show_in_graphql" and are assigned to a
Custom Post Type that's also set to "show_in_graphql" will now be present in the Schema.
