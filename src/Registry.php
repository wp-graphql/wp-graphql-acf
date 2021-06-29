<?php

namespace WPGraphQL\ACF;

use Exception;
use GraphQLRelay\Relay;
use WPGraphQL\ACF\Fields\AcfField;
use WPGraphQL\ACF\Types\InterfaceType\AcfFieldGroupInterface;
use WPGraphQL\ACF\Types\ObjectType\AcfFieldGroupConfig;
use WPGraphQL\ACF\Types\ObjectType\AcfGoogleMap;
use WPGraphQL\ACF\Types\ObjectType\AcfLink;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class Registry
 *
 * @package WPGraphQL\ACF
 */
class Registry {

	/**
	 * The WPGraphQL Type Registry
	 *
	 * @var TypeRegistry
	 */
	protected $type_registry;

	/**
	 * ACF Field Groups
	 *
	 * @var array
	 */
	protected $acf_field_groups;

	/**
	 * Tracks registered field names to know when to resolve
	 * data from the parent for previews.
	 *
	 * @var array
	 */
	protected $registered_field_names;

	/**
	 * Tracks which field groups have already been registered to avoid recursion
	 *
	 * @var array
	 */
	protected $registered_field_groups;

	/**
	 * Tracks which field groups have already been registered to avoid recursion and allow
	 * referencing for cloned fields
	 *
	 * @var array
	 */
	protected $registered_field_group_interfaces;

	/**
	 * Tracks which field group Interfaces have already been registered to avoid recursion and
	 * allow referencing in cloned fields
	 *
	 * @var array
	 */
	protected $registered_field_group_fields_interfaces;

	/**
	 * Initialize ACF Type Registry
	 *
	 * @param TypeRegistry $type_registry
	 *
	 * @return void
	 * @throws Exception
	 */
	public function init( TypeRegistry $type_registry ) {

		// Initialize the Type Registry
		$this->type_registry = $type_registry;

		// Get all ACF Field Groups
		$this->acf_field_groups = acf_get_field_groups();

		// Instantiate the field groups array
		$this->registered_field_groups = [];
		$this->registered_field_group_interfaces = [];
		$this->registered_field_group_fields_interfaces = [];

		// If there are no ACF Field Groups, don't proceed
		if ( empty( $this->acf_field_groups ) || ! is_array( $this->acf_field_groups ) ) {
			return;
		}

		// Filters GraphQL meta resolvers for preview support of ACF Fields
		add_filter( 'graphql_resolve_revision_meta_from_parent', [
			$this,
			'resolve_meta_from_parent'
		], 10, 4 );

		// Register types
		$this->map_acf_to_graphql();
	}

	/**
	 * Determines if a field group has already been registered
	 *
	 * @param string $key
	 *
	 * @return mixed|string|null
	 */
	public function get_registered_field_group( string $key ) {
		return $this->registered_field_groups[ $key ] ?? null;
	}

	/**
	 *
	 *
	 * @param string string $key The key the field group is registered under
	 * @param string $type_name $type_name The GraphQL Type name
	 *
	 * @return string
	 */
	public function add_registered_field_group( string $key, string $type_name ) {
		$this->registered_field_groups[ $key ] = $type_name;
		return $type_name;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_registered_field_group_interface( string $key ) {
		return $this->registered_field_group_interfaces[ $key ] ?? null;
	}

	/**
	 * @param $key
	 * @param $interface_name
	 * @return string
	 */
	public function add_registered_field_group_interface( string $key, string $interface_name ) {
		$this->registered_field_group_interfaces[ $key ] = $interface_name;
		return $interface_name;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_registered_field_group_fields_interface( string $key ) {
		return $this->registered_field_group_fields_interfaces[ $key ] ?? null;
	}

	/**
	 * @param $key
	 * @param $interface_name
	 * @return string
	 */
	public function add_registered_field_group_fields_interface( string $key, string $interface_name ) {
		$this->registered_field_group_fields_interfaces[ $key ] = $interface_name;
		return $interface_name;
	}

	/**
	 * Given a from Type, to Type and from field name, a connection name is returned
	 *
	 * @param string $from_type The Type name the connection is coming from
	 * @param string $to_type The Type name the connection is going to
	 * @param string $from_field_name The name of the field the connection resolves from
	 *
	 * @return string
	 */
	public function get_connection_name( string $from_type, string $to_type, string $from_field_name ) {
		// Create connection name using $from_type + To + $to_type + Connection.
		$connection_name = ucfirst( $from_type ) . 'To' . ucfirst( $to_type ) . 'Connection';

		// If connection type already exists with that connection name. Set connection name using
		// $from_field_name + To + $to_type + Connection.
		if ( ! empty( $this->type_registry->get_type( $connection_name ) ) ) {
			$connection_name = ucfirst( $from_type ) . 'To' . ucfirst( $from_field_name ) . 'Connection';
		}

		return $connection_name;
	}

	/**
	 * @return TypeRegistry
	 */
	public function get_type_registry() {
		return $this->type_registry;
	}

	/**
	 * Determines whether meta should resolve from the requested object or the parent. This
	 * aids with previews.
	 *
	 * @param bool   $should    Whether the meta should resolve from the parent or not.
	 * @param mixed  $object_id The ID of the object the field belongs to
	 * @param string $meta_key  The name of the field
	 * @param bool   $single    Whether it's a singular field or a group
	 *
	 * @return bool
	 */
	public function resolve_meta_from_parent( bool $should, $object_id, string $meta_key, bool $single ) {

		if ( empty( $this->registered_field_names ) || ! is_array( $this->registered_field_names ) ) {
			return $should;
		}

		$matches = null;

		// Iterate over all field names
		foreach ( $this->registered_field_names as $field_name ) {

			// If the field name is an exact match with the $meta_key, the ACF field should
			// resolve from the revision meta, so we can return false here, so that meta can
			// resolve from the revision instead of the parent
			if ( $field_name === $meta_key ) {
				return false;
			}

			// For flex fields/repeaters, the meta keys are structured a bit funky.
			// This checks to see if the $meta_key starts with the same string as one of the
			// acf fields (a flex/repeater field) and then checks if it's preceeded by an underscore and a number.
			if ( $field_name === substr( $meta_key, 0, strlen( $field_name ) ) ) {
				// match any string that starts with the field name, followed by an underscore, followed by a number, followed by another string
				// ex my_flex_field_0_text_field or some_repeater_field_12_25MostPopularDogToys
				$pattern = '/' . $field_name . '_\d+_\w+/m';
				preg_match( $pattern, $meta_key, $matches );
			}

			// If the meta key matches the pattern, treat it as a sub-field of an ACF Field Group
			if ( null !== $matches ) {
				return false;
			}

		}

		return $should;

	}

	/**
	 * Register Types to the WPGraphQL Schema
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function map_acf_to_graphql() {

		// Register initial pre-defined types
		$this->register_initial_types();
		$this->register_options_pages();

		// Map User created Field Groups to the Schema
		$this->map_acf_field_groups_to_types();

	}

	/**
	 * Register ACF Options pages to the GraphQL Schema.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function register_options_pages() {

		$options_pages = acf_get_options_pages();
		if ( empty( $options_pages ) || ! is_array( $options_pages ) ) {
			return;
		}

		foreach ( $options_pages as $options_page ) {

			if ( ! isset( $options_page['show_in_graphql'] ) || false === (bool) $options_page['show_in_graphql'] ) {
				continue;
			}

			$page_title = $options_page['page_title'];
			$page_slug  = $options_page['menu_slug'];
			$type_name = isset( $options_page['graphql_field_name'] ) ? Utils::format_type_name( $options_page['graphql_field_name'] ) : Utils::format_type_name( $options_page['menu_slug'] );

			if ( null !== $this->type_registry->get_type( $type_name ) ) {
				return;
			}

			register_graphql_object_type( $type_name, [
				'description' => sprintf( __( '%s options. Registered as an ACF Options page.', 'wp-graphql-acf' ), $page_title ),
				'fields'      => [
					'pageTitle' => [
						'type'    => 'String',
						'resolve' => function( $source ) use ( $page_title ) {
							return ! empty( $page_title ) ? $page_title : null;
						},
					],
					'pageSlug'  => [
						'type'    => 'String',
						'resolve' => function( $source ) use ( $page_slug ) {
							return ! empty( $page_slug ) ? $page_slug : null;
						},
					],
				],
			] );

			$field_name = Utils::format_field_name( $type_name );

			register_graphql_field(
				'RootQuery',
				$field_name,
				[
					'type' => $type_name,
					'description' => sprintf( __( '%s options.', 'wp-graphql-acf' ), $page_title ),
					'resolve' => function() use ( $options_page ) {
						return ! empty( $options_page ) ? $options_page : null;
					}
				]
			);


		}

	}

	/**
	 * Register initial types to the Schema
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function register_initial_types() {

		// Interfaces
		AcfFieldGroupInterface::register_type( $this );

		// Object Types
		AcfLink::register_type();
		AcfGoogleMap::register_type();
		AcfFieldGroupConfig::register_type();

		/**
		 * Registers a RootQuery entry for fetching
		 * an individual FieldGroup by ID
		 */
		$this->type_registry->register_field( 'RootQuery', 'acfFieldGroup', [
			'description' => __( 'ACF Field Group', 'wp-graphql-acf' ),
			'type'        => 'AcfFieldGroup',
			'args'        => [
				'id' => [
					'type' => [ 'non_null' => 'ID' ],
				],
			],
			'resolve'     => function( $root, $args, $context, $info ) {

				$id_parts    = Relay::fromGlobalId( $args['id'] );
				$field_group = isset( $id_parts['id'] ) ? acf_get_field_group( $id_parts['id'] ) : null;

				if ( empty( $field_group ) ) {
					return null;
				}

				return [
					'fieldGroupName'    => isset( $field_group['title'] ) ? $field_group['title'] : null,
					'_fieldGroupConfig' => $field_group,
				];

			}
		] );

	}

	/**
	 * Map ACF field groups to the Schema
	 *
	 * @return void
	 * @throws Exception
	 */
	public function map_acf_field_groups_to_types() {

		if ( empty( $this->acf_field_groups ) || ! is_array( $this->acf_field_groups ) ) {
			return;
		}

		foreach ( $this->acf_field_groups as $field_group ) {
			$this->add_acf_field_group_to_graphql( $field_group );
		}
	}

	/**
	 * Adds an ACF Field Group to the GraphQL Schema by determining the GraphQL Types the
	 * field group should show on.
	 *
	 * @param array $field_group The ACF Field Group config to add to the Schema
	 * @param array $graphql_types The GraphQL Types the field group should show on
	 * @param array<string> $interfaces Interfaces to apply to the Types
	 *
	 * @return mixed|string|void
	 *
	 * @throws Exception
	 */
	public function add_acf_field_group_to_graphql( array $field_group, array $graphql_types = [], $interfaces = [] ) {

		// If the field group has already been registered, return the registered Type
		if ( isset( $field_group['key'] ) && $this->get_registered_field_group( $field_group['key'] ) ) {
			return $this->get_registered_field_group( $field_group['key'] );
		}

		if ( ! $this->should_field_group_show_in_graphql( $field_group ) ) {
			return;
		}

		$field_group_name = '';

		if ( isset( $field_group['graphql_field_name'] ) ) {
			$field_group_name = $field_group['graphql_field_name'];
		} else if ( isset( $field_group['title'] ) ) {
			$field_group_name = $field_group['title'];
		} else if ( isset( $field_group['label'] ) )  {
			$field_group_name = $field_group['label'];
		} else if ( isset( $field_group['name'] ) ) {
			$field_group_name = $field_group['name'];
		}

		if ( empty( $field_group_name ) ) {
			graphql_debug( __( 'No name could be determined for the field group', 'wp-graphql-acf'), [ 'fieldGroup' => $field_group ] );
			return;
		}

		$type_name      = $this->get_field_group_type_name( $field_group );
		$interface_name = 'With_' . $type_name;
		$fields_interface_name = 'With_' . $type_name . '_Fields';

		$this->type_registry->register_interface_type( $interface_name, [
			'description' => sprintf( __( 'Fields of the %s ACF Field Group', 'wp-graphql-acf' ), $field_group_name ),
			'fields' => [
				lcfirst( $type_name ) => [
					'type' => $type_name,
					'description' => sprintf( __( 'Types that support the %s field group', 'wp-graphql' ), $field_group_name ),
					'resolve'     => function( $root ) use ( $field_group ) {
						return ! empty( $root ) ? $root : $field_group;
					}
				],
			],
		] );

		$fields = [
			'fieldGroupName' => [
				'type' => 'String',
				'resolve' => function() use ( $type_name ) {
					return lcfirst( $type_name );
				}
			]
		];

		$mapped_fields = $this->map_acf_fields_to_field_group( $field_group );
		$mapped_fields = ! empty( $mapped_fields ) ? array_merge( $fields, $mapped_fields ) : $fields;

		$this->type_registry->register_interface_type( $fields_interface_name, [
			'description' => sprintf( __( 'Field Groups with fields of the %s ACF Field Group', 'wp-graphql-acf' ), $field_group_name ),
			'fields' => $mapped_fields
		]);

		$layout_interfaces = [ 'AcfFieldGroup', $fields_interface_name ];

		if ( ! empty( $interfaces ) ) {
			$interfaces = array_merge( $layout_interfaces, $interfaces );
		} else {
			$interfaces = $layout_interfaces;
		}

		$this->type_registry->register_object_type( $type_name, [
			'interfaces' => $interfaces,
			'fields' => $mapped_fields
		]);

		$is_flex_layout = false;

		if ( isset( $field_group['isFlexLayout'] ) && true === $field_group['isFlexLayout'] ) {
			$is_flex_layout = true;
		}

		// For flex layouts we want to leave the GraphQL Types empty
		// As they don't need to be added as fields in the Schema independently
		if ( empty( $graphql_types ) && ! $is_flex_layout ) {
			$graphql_types = $this->get_graphql_types_for_field_group( $field_group );
		}

		if ( ! empty( $graphql_types ) ) {
			register_graphql_interfaces_to_types( $interface_name, $graphql_types );
		}


		// Add the interfaces to a Registry, so they can be identified by field group key
		$this->add_registered_field_group_interface( $field_group['key'], $interface_name );
		$this->add_registered_field_group_fields_interface( $field_group['key'], $fields_interface_name );

		// Add the field group to the list of registered field groups
		return $this->add_registered_field_group( $field_group['key'], $type_name );

	}

	/**
	 * Get the GraphQL Types a Field Group should be registered to show on
	 *
	 * @param array $field_group The ACF Field Group config to determine the Types for
	 *
	 * @return array
	 *
	 * @return array
	 */
	public function get_graphql_types_for_field_group( array $field_group ) {

		$graphql_types = $field_group['graphql_types'] ?? [];

		$field_group_name = $field_group['graphql_field_name'] ?? $field_group['title'];
		$field_group_name = Utils::format_field_name( $field_group_name );

		$manually_set_graphql_types = isset( $field_group['map_graphql_types_from_location_rules'] ) ? (bool) $field_group['map_graphql_types_from_location_rules'] : false;

		if ( false === $manually_set_graphql_types || empty( $graphql_types ) ) {
			if ( ! isset( $field_group['graphql_types'] ) || empty( $field_group['graphql_types'] ) ) {
				$location_rules = $this->get_location_rules();
				if ( isset( $location_rules[ $field_group_name ] ) ) {
					$graphql_types = $location_rules[ $field_group_name ];
				}
			}
		}

		return ! empty( $graphql_types ) && is_array( $graphql_types ) ? array_unique( array_filter( $graphql_types ) ) : [];

	}

	/**
	 * Gets the location rules
	 *
	 * @return array
	 */
	protected function get_location_rules() {

		$field_groups = $this->acf_field_groups;
		$rules        = [];

		// Each field group that doesn't have GraphQL Types explicitly set should get the location
		// rules interpreted.
		foreach ( $field_groups as $field_group ) {
			if ( ! isset( $field_group['graphql_types'] ) || ! is_array( $field_group['graphql_types'] ) ) {
				$rules[] = $field_group;
			}
		}

		if ( empty( $rules ) ) {
			return [];
		}

		// If there are field groups with no graphql_types field set, inherit the rules from
		// ACF Location Rules
		$rules = new LocationRules();
		$rules->determine_location_rules();

		return $rules->get_rules();
	}

	/**
	 * Determines whether a field group should be exposed to the GraphQL Schema. By default, field
	 * groups will not be exposed to GraphQL.
	 *
	 * @param array $field_group Undocumented.
	 *
	 * @return bool
	 */
	protected function should_field_group_show_in_graphql( array $field_group ) {

		/**
		 * By default, field groups will not be exposed to GraphQL.
		 */
		$show = false;

		/**
		 * If the field group is set to show_in_graphql, show it
		 */
		if ( isset( $field_group['show_in_graphql'] ) && true === (bool) $field_group['show_in_graphql'] ) {
			$show = true;
		}

		if ( isset( $field_group['parent'] ) && ! empty( $field_group['parent'] ) ) {
			$show = true;
		}

		/**
		 * Whether a field group should show in GraphQL.
		 *
		 * @var boolean $show        Whether the field group should show in the GraphQL Schema
		 * @var array   $field_group The ACF Field Group
		 * @var Config  $this        The Config for the ACF Plugin
		 */
		return apply_filters( 'wpgraphql_acf_should_field_group_show_in_graphql', $show, $field_group, $this );

	}

	/**
	 * Given a field group config array, returns the Type name to be used in the graph
	 *
	 * @param array $field_group The ACF Field Group config array
	 *
	 * @return string
	 */
	public function get_field_group_type_name( array $field_group ) {
		$type_name = $field_group['graphql_field_name'] ?? $field_group['title'];
		$type_name = ucfirst( $type_name );

		return $type_name;
	}

	/**
	 * Get a list of supported field types that WPGraphQL for ACF supports.
	 *
	 * This is helpful for determining whether UI should be output for the field, and whether
	 * the field should be added to the Schema.
	 *
	 * Some fields, such as "Accordion" are not supported currently.
	 *
	 * @return array
	 */
	public function get_supported_field_types() {

		$supported_field_types = [
			'text',
			'textarea',
			'number',
			'range',
			'email',
			'url',
			'password',
			'image',
			'file',
			'wysiwyg',
			'oembed',
			'gallery',
			'select',
			'checkbox',
			'radio',
			'button_group',
			'true_false',
			'link',
			'post_object',
			'page_link',
			'relationship',
			'taxonomy',
			'user',
			'google_map',
			'date_picker',
			'date_time_picker',
			'time_picker',
			'color_picker',
			'group',
			'repeater',
			'flexible_content',
			'clone'
		];

		/**
		 * filter the supported fields to allow 3rd party extensions to hook in and
		 * add support for their fields.
		 *
		 * @param array $supported_fields
		 */
		return apply_filters( 'wpgraphql_acf_supported_fields', $supported_field_types );

	}

	/**
	 * Map Fields to the Field Groups in the Schema
	 *
	 * @param array $field_group
	 *
	 * @return array
	 */
	public function map_acf_fields_to_field_group( array $field_group ) {

		// Get the ACF Fields for the specified field group
		$fields = isset( $field_group['sub_fields'] ) && is_array( $field_group['sub_fields'] ) ? $field_group['sub_fields'] :  acf_get_fields( $field_group );

		// If there are no for the field group, do nothing.
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return [];
		}

		$cloned_groups = [];

		foreach ( $fields as $field ) {

			if ( ! isset( $field['_clone'] ) ) {
				continue;
			}

			$field_config = acf_get_field( $field['_clone'] );

			if ( ! isset( $field_config['clone'] ) || ! is_array( $field_config['clone' ] ) ) {
				continue;
			}

			foreach ( $field_config['clone'] as $cloned ) {
				if ( 0 !== strpos( $cloned, 'group_' ) ) {
					continue;
				}

				if ( isset( $groups[ $cloned ] ) ) {
					continue;
				}

				$cloned_field_group = acf_get_field_group( $cloned );
				$cloned_groups[ $cloned ] = 'With_' . $this->get_field_group_type_name( $cloned_field_group ) . '_Fields';

			}

		}

		if ( ! empty( $cloned_groups ) ) {

			graphql_debug( [
				'register_graphql_interfaces_to_types',
				array_values( $cloned_groups ),
				$this->get_field_group_type_name( $field_group )
			]);

			register_graphql_interfaces_to_types( array_values( $cloned_groups ), [ $this->get_field_group_type_name( $field_group ) ]);
			// graphql_debug( [ $this->get_field_group_type_name( $field_group ), $cloned_groups ] );
		}

		$mapped_fields = [];

		// Store a list of field keys that have been registered
		// to help avoid registering the same field twice on one
		// field group. This occasionally happens with clone fields.
		$registered_field_keys = [];

		foreach ( $fields as $field ) {

			// If a field is empty or not an array, it's not valid
			if ( empty( $field ) || ! is_array( $field ) ) {
				continue;
			}

			// If a field doesn't have a name or key, it's not valid
			if ( ! isset( $field['name'], $field['key'] ) ) {
				continue;
			}

			// If a field is specifically set to not show in GraphQL, don't proceed
			if ( isset( $field['show_in_graphql'] ) && false === $field['show_in_graphql'] ) {
				continue;
			}

			// Prevent duplicate cloned fields from being registered to the same field group
			if ( in_array( $field['key'], $registered_field_keys, true ) ) {
				continue;
			}

			// If the ACF Field Type is not a supported field type, don't add it to the Schema.
			// For example, Accordion and Tab types, and various extension types
			// are not supported natively, but can be filtered in!
			if ( ! in_array( $field['type'], $this->get_supported_field_types(), true ) ) {
				continue;
			}

			$mapped_field = $this->map_graphql_field( $field, $field_group );

			if ( $mapped_field instanceof AcfField && null !== $mapped_field->get_graphql_field_config() ) {
				$mapped_fields[ $mapped_field->get_field_name() ] = $mapped_field->get_graphql_field_config();
			}

		}

		return $mapped_fields;

	}

	/**
	 * @param array $field The ACF Field config
	 * @param array $field_group The ACF Field Group Config
	 *
	 * @return AcfField
	 */
	public function map_graphql_field( array $field, array $field_group ) {

		$field_type = $field['type'] ?? null;

		$class_name = Utils::format_type_name( $field_type );
		$class_name = '\\WPGraphQL\\ACF\Fields\\' . $class_name;

		/**
		 * This allows 3rd party extensions to hook and and provide
		 * a path to their class for registering a field to the Schema
		 */
		$class_name = apply_filters( 'graphql_acf_field_class', $class_name, $field, $field_group, $this );

		if ( class_exists( $class_name ) ) {
			$field = new $class_name( $field, $field_group, $this );
			if ( $field instanceof AcfField ) {
				return $field;
			}

			return new AcfField( $field, $field_group, $this );
		}

		return new AcfField( $field, $field_group, $this );

	}

}
