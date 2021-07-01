# WPGraphQL for Advanced Custom Fields

WPGraphQL for Advanced Custom Fields automatically exposes your ACF fields to the WPGraphQL Schema.

- [Install and Activate](#install-and-activate)
  - [Installing from Github](#install-from-github)
  - [Installing with Composer](#install-with-composer)
- [Dependencies](#dependencies)
- [Adding Fields to the WPGraphQL Schema](#adding-fields-to-wpgraphql)
- [Supported Fields](#supported-fields)
  - [Accordion](./docs/fields/accordion.md)
  - [Button Group](./docs/fields/button-group.md)
  - [Checkbox](./docs/fields/checkbox.md)
  - [Clone](./docs/fields/clone.md)
  - [Color Picker](./docs/fields/color-picker.md)
  - [Date Picker](./docs/fields/date-picker.md)
  - [Date/Time Picker](./docs/fields/date-time-picker.md)
  - [Email](./docs/fields/email.md)
  - [File](./docs/fields/file.md)
  - [Flexible Content](./docs/fields/flexible-content.md)
  - [Gallery](./docs/fields/gallery.md)
  - [Google Map](./docs/fields/google-map.md)
  - [Group](./docs/fields/group.md)
  - [Image](./docs/fields/image.md)
  - [Link](./docs/fields/link.md)
  - [Message](./docs/fields/message.md)
  - [Number](./docs/fields/number.md)
  - [Oembed](./docs/fields/oembed.md)
  - [Page Link](./docs/fields/page-link.md)
  - [Password](./docs/fields/password.md)
  - [Post Object](./docs/fields/post-object.md)
  - [Radio](./docs/fields/radio.md)
  - [Range](./docs/fields/range.md)
  - [Relationship](./docs/fields/relationship.md)
  - [Repeater](./docs/fields/repeater.md)
  - [Select](./docs/fields/select.md)
  - [Tab](./docs/fields/tab.md)
  - [Taxonomy](./docs/fields/taxonomy.md)
  - [Text](./docs/fields/text.md)
  - [Text Area](./docs/fields/text-area.md)
  - [Time Picker](./docs/fields/time-picker.md)
  - [True/False](./docs/fields/true-false.md)
  - [Url](./docs/fields/url.md)
  - [User](./docs/fields/user.md)
  - [WYSIWYG](./docs/fields/wysiwyg.md)
- [Options Pages](#options-pages)
- [Location Rules](#location-rules)

## Install and Activate <a name="install-and-activate" />

WPGraphQL for Advanced Custom Fields is not currently available on the WordPress.org repository, so you must download it from Github, or Composer.

### Installing From Github <a name="install-from-github" />

To install the plugin from Github, you can [download the latest release zip file](https://github.com/wp-graphql/wp-graphql-acf/archive/master.zip), upload the Zip file to your WordPress install, and activate the plugin.

[Click here](https://wordpress.org/support/article/managing-plugins/) to learn more about installing WordPress plugins from a Zip file.

### Installing from Composer <a name="install-with-composer" />

`composer require wp-graphql/wp-graphql-acf`

## Dependencies <a name="dependencies" />

In order to use WPGraphQL for Advanced Custom Fields, you must have [WPGraphQL](https://github.com/wp-graphql/wp-graphql) and [Advanced Custom Fields](https://advancedcustomfields.com) (free or pro) installed and activated.

## Adding Fields to the WPGraphQL Schema <a name="adding-fields-to-wpgraphql" />

**TL;DR:** [Here's a video](https://www.youtube.com/watch?v=rIg4MHc8elg) showing an overview of usage.

Advanced Custom Fields, or ACF for short, enables users to add Field Groups, either using a [Graphical User Interface](https://www.advancedcustomfields.com/resources/creating-a-field-group/), [PHP code](https://www.advancedcustomfields.com/resources/register-fields-via-php/), or [local JSON](https://www.advancedcustomfields.com/resources/local-json/) to various screens in the WordPress dashboard, such as (but not limited to) the Edit Post, Edit User and Edit Term screens.

Whatever method you use to register ACF fields to your WordPress site should work with WPGraphQL for Advanced Custom Fields. For the sake of simplicity, the documentation below will _primarily_ use the Graphic User Interface for examples.

### Add ACF Fields to the WPGraphQL Schema

The first step in using Advanced Custom Fields with WPGraphQL is to [create an ACF Field Group](https://www.advancedcustomfields.com/resources/creating-a-field-group/).

By default, field groups are _not_ exposed to WPGraphQL. You must opt-in to expose your ACF Field Groups and fields to the WPGraphQL Schema as some information managed by your ACF fields may not be intended for exposure in a queryable API like WPGraphQL.

#### Show in GraphQL Setting

To have your ACF Field Group show in the WPGraphQL Schema, you need to configure the Field Group to "Show in GraphQL".

##### Using the ACF GUI

When using the ACF Graphic User Interface for creating fields, WPGraphQL for Advanced Custom Fields adds a **Show in GraphQL** field to Field Groups.

Setting the value of this field to "Yes" will show the field group in the WPGraphQL Schema, if a [GraphQL Field Name](#graphql-field-name) is also set

![Show in GraphQL Setting for ACF Field Groups](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/field-group-show-in-graphql.png?raw=true)

##### Registering Fields in PHP

When registering ACF Fields in PHP, you need to add `show_in_graphql` and `graphql_field_name` when defining your field group.  See below as an example.

```
function my_acf_add_local_field_groups() {

	acf_add_local_field_group(array(
		'key' => 'group_1',
        'title' => 'My Group',
        'show_in_graphql' => true,
        'graphql_field_name' => 'myGroup',
		'fields' => array (
			array (
				'key' => 'field_1',
				'label' => 'Sub Title',
				'name' => 'sub_title',
				'type' => 'text',
			)
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				),
			),
		),
	));

}

add_action('acf/init', 'my_acf_add_local_field_groups');
```

Each individual field will inherit its GraphQL name from the supplied `name` tag. In this example, `sub_title` will become `subTitle` when requested through GraphQL. If you want more granular control, you can pass `graphql_field_name` to each individual field as well.

## Supported Fields

In order to document interacting with the fields in GraphQL, an example field group has been created with one field of each type.

To replicate the same field group documented here you can download the [example field group](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/field-group-examples-export.json) and [import it](https://support.advancedcustomfields.com/forums/topic/importing-exporting-acf-settings/) into your environment.

For the sake of documentation, this example field group has the [location rule](#location-rules) set to "Post Type is equal to Post", which will allow for the fields to be entered when creating and editing Posts in the WordPress dashboard, and will expose the fields to the `Post` type in the WPGraphQL Schema.

![Location rule set to Post Type is equal to Post](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/location-rule-post-type-post.png?raw=true)

## Options Pages

**Reference**: https://www.advancedcustomfields.com/add-ons/options-page/

To add an option page and expose it to the graphql schema, simply add 'show_in_graphql' => true when you register an option page.

**Example Usage**

```php
function register_acf_options_pages() {

    // check function exists
    if ( ! function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    // register options page
    $my_options_page = acf_add_options_page(
        array(
            'page_title'      => __( 'My Options Page' ),
            'menu_title'      => __( 'My Options Page' ),
            'menu_slug'       => 'my-options-page',
            'capability'      => 'edit_posts',
            'show_in_graphql' => true,
        )
    );
}

add_action( 'acf/init', 'register_acf_options_pages' )
Example Query
query GetMyOptionsPage {
    myOptionsPage {
        someCustomField
    }
}
```

Alternatively, it's you can check the Fields API Reference to learn about exposing your custom fields to the Schema.

## Location Rules <a name="location-rules" />

Advanced Custom Fields field groups are added to the WordPress dashboard by being assigned "Location Rules".

WPGraphQL for Advanced Custom Fields uses the Location Rules to determine where in the GraphQL Schema the field groups/fields should be added to the Schema.

For example, if a Field Group were assigned to "Post Type is equal to Post", then the field group would show in the WPGraphQL Schema on the `Post` type, allowing you to query for ACF fields of the Post, anywhere you can interact with the `Post` type in the Schema.

### Supported Locations

@todo: Document supported location rules and how they map from ACF to the WPGraphQL Schema

### Why aren't all location rules supported?

You might notice that some location rules don't add fields to the Schema. This is because some location rules are based on context that doesn't exist when the GraphQL Schema is generated.

For example, if you have a location rule to show a field group only on a specific page, how would that be exposed the the Schema? There's no Type in the Schema for just one specific page.

If you're not seeing a field group in the Schema, look at the location rules, and think about _how_ the field group would be added to a Schema that isn't aware of context like which admin page you're on, what category a Post is assigned to, etc.

If you have ideas on how these specific contextual rules should be handled in WPGraphQL, submit an issue so we can consider how to best support it!
