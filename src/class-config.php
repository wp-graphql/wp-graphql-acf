<?php

/**
 * Config for WPGraphQL ACF
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;

/**
 * Config class.
 */
class Config {

	protected $type_registry;

	/**
	 * @var array <string> List of field names registered to the Schema
	 */
	protected $registered_field_names;

	/**
	 * @var array <string> List of options page slugs registered to the Schema
	 */
	protected $registered_options_pages = [];

	/**
	 * Initialize WPGraphQL to ACF
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry Instance of the WPGraphQL TypeRegistry
	 */
	public function init(\WPGraphQL\Registry\TypeRegistry $type_registry) {

		/**
		 * Set the TypeRegistry
		 */
		$this->type_registry = $type_registry;

		/**
		 * Add ACF Fields to GraphQL Types
		 */
		$this->add_acf_fields_to_graphql_types();

		// This filter tells WPGraphQL to resolve revision meta for ACF fields from the revision's meta, instead
		// of the parent (published post) meta.
		add_filter( 'graphql_resolve_revision_meta_from_parent', function( $should, $object_id, $meta_key, $single ) {

			// Loop through all registered ACF fields that show in GraphQL.
			if ( is_array( $this->registered_field_names ) && ! empty( $this->registered_field_names ) ) {

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

			}

			return $should;
		}, 10, 4);
	}

	/**
	 * Determines whether a field group should be exposed to the GraphQL Schema. By default, field
	 * groups will not be exposed to GraphQL.
	 *
	 * @param array $field_group Undocumented.
	 *
	 * @return bool
	 */
	protected function should_field_group_show_in_graphql($field_group) {

		/**
		 * By default, field groups will not be exposed to GraphQL.
		 */
		$show = false;

		/**
		 * If
		 */
		if (isset($field_group['show_in_graphql']) && true === (bool) $field_group['show_in_graphql']) {
			$show = true;
		}

		/**
		 * Determine conditions where the GraphQL Schema should NOT be shown in GraphQL for
		 * root groups, not nested groups with parent.
		 */
		if (!isset($field_group['parent'])) {
			if (
				(isset($field_group['active']) && true != $field_group['active']) ||
				(empty($field_group['location']) || !is_array($field_group['location']))
			) {
				$show = false;
			}
		}

		/**
		 * Whether a field group should show in GraphQL.
		 *
		 * @var boolean $show        Whether the field group should show in the GraphQL Schema
		 * @var array   $field_group The ACF Field Group
		 * @var Config  $this        The Config for the ACF Plugin
		 */
		return apply_filters('wpgraphql_acf_should_field_group_show_in_graphql', $show, $field_group, $this);
	}

	/**
	 * Undocumented function
	 *
	 * @todo: This may be a good utility to add to WPGraphQL Core? May even have something already?
	 *
	 * @param string $str      Unknown.
	 * @param array  $no_strip Unknown.
	 *
	 * @return mixed|null|string|string[]
	 */
	public static function camel_case($str, array $no_strip = []) {
		// non-alpha and non-numeric characters become spaces.
		$str = preg_replace('/[^a-z0-9' . implode('', $no_strip) . ']+/i', ' ', $str);
		$str = trim($str);
		// Lowercase the string
		$str = strtolower($str);
		// uppercase the first character of each word.
		$str = ucwords($str);
		// Replace spaces
		$str = str_replace(' ', '', $str);
		// Lowecase first letter
		$str = lcfirst($str);

		return $str;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $root Undocumented.
	 * @param [type] $acf_field Undocumented.
	 * @param boolean $format Whether ACF should apply formatting to the field. Default false.
	 *
	 * @return mixed
	 */
	protected function get_acf_field_value($root, $acf_field, $format = false) {

		$value = null;
		$id = null;

		if (is_array($root) && !(!empty($root['type']) && 'options_page' === $root['type'])) {

			if (isset($root[$acf_field['key']])) {
				$value = $root[$acf_field['key']];

				if ('wysiwyg' === $acf_field['type']) {
					$value = apply_filters('the_content', $value);
				}
			}
		} else {

			switch (true) {
				case $root instanceof Term:
					$id = 'term_' . $root->term_id;
					break;
				case $root instanceof Post:
					$id = absint($root->ID);
					break;
				case $root instanceof MenuItem:
					$id = absint($root->menuItemId);
					break;
				case $root instanceof Menu:
					$id = 'term_' . $root->menuId;
					break;
				case $root instanceof User:
					$id = 'user_' . absint($root->userId);
					break;
				case $root instanceof Comment:
					$id = 'comment_' . absint($root->comment_ID);
					break;
				case is_array($root) && !empty($root['type']) && 'options_page' === $root['type']:
					$id = $root['post_id'];
					break;
				default:
					$id = null;
					break;
			}

			/**
			 * Filters the root ID, allowing additional Models the ability to provide a way to resolve their ID
			 *
			 * @param int   $id    The ID of the object. Default null
			 * @param mixed $root  The Root object being resolved. The ID is typically a property of this object.
			 */
			$id = apply_filters('graphql_acf_get_root_id', $id, $root);

			if (empty($id)) {
				return null;
			}

			$format = false;

			if ('wysiwyg' === $acf_field['type']) {
				$format = true;
			}
			
			if ( 'select' === $acf_field['type'] ) {
				$format = true;
			}

			/**
			 * Check if cloned field and retrieve the key accordingly.
			 */
			if (!empty($acf_field['_clone'])) {
				$key = $acf_field['__key'];
			} else {
				$key = $acf_field['key'];
			}

			$field_value = get_field($key, $id, $format);

			$value = !empty($field_value) ? $field_value : null;
		}

		/**
		 * Filters the returned ACF field value
		 *
		 * @param mixed $value     The resolved ACF field value
		 * @param array $acf_field The ACF field config
		 * @param mixed $root      The Root object being resolved. The ID is typically a property of this object.
		 * @param int   $id        The ID of the object
		 */
		return apply_filters('graphql_acf_field_value', $value, $acf_field, $root, $id);
	}

	/**
	 * Get a list of supported fields that WPGraphQL for ACF supports.
	 *
	 * This is helpful for determining whether UI should be output for the field, and whether
	 * the field should be added to the Schema.
	 *
	 * Some fields, such as "Accordion" are not supported currently.
	 *
	 * @return array
	 */
	public static function get_supported_fields() {
		$supported_fields = [
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
			'flexible_content'
		];

		/**
		 * filter the supported fields
		 *
		 * @param array $supported_fields
		 */
		return apply_filters('wpgraphql_acf_supported_fields', $supported_fields);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $type_name Undocumented.
	 * @param [type] $field_name Undocumented.
	 * @param [type] $config Undocumented.
	 *
	 * @return mixed
	 */
	protected function register_graphql_field($type_name, $field_name, $config) {
		$acf_field = isset($config['acf_field']) ? $config['acf_field'] : null;
		$acf_type  = isset($acf_field['type']) ? $acf_field['type'] : null;

		if (empty($acf_type)) {
			return false;
		}



		/**
		 * filter the field config for custom field types
		 *
		 * @param array $field_config
		 */
		$field_config = apply_filters('wpgraphql_acf_register_graphql_field', [
			'type'    => null,
			'resolve' => isset($config['resolve']) && is_callable($config['resolve']) ? $config['resolve'] : function ($root, $args, $context, $info) use ($acf_field) {
				$value = $this->get_acf_field_value($root, $acf_field);

				return !empty($value) ? $value : null;
			},
		], $type_name, $field_name, $config);

		switch ($acf_type) {
			case 'button_group':
			case 'color_picker':
			case 'email':
			case 'text':
			case 'message':
			case 'oembed':
			case 'password':
			case 'wysiwyg':
			case 'url':
				// Even though Selects and Radios in ACF can _technically_ be an integer
				// we're choosing to always cast as a string because with
				// GraphQL we can't return different types
				$field_config['type'] = 'String';
				break;
			case 'textarea':
				$field_config['type'] = 'String';
				$field_config['resolve'] = function ($root) use ($acf_field) {
					$value = $this->get_acf_field_value($root, $acf_field);

					if (!empty($acf_field['new_lines'])) {
						if ('wpautop' === $acf_field['new_lines']) {
							$value = wpautop($value);
						}
						if ('br' === $acf_field['new_lines']) {
							$value = nl2br($value);
						}
					}
					return $value;
				};
				break;
			case 'select':

				/**
				 * If the select field is configured to not allow multiple values
				 * the field will return a string, but if it is configured to allow
				 * multiple values it will return a list of strings, and an empty array
				 * if no values are set.
				 *
				 * @see: https://github.com/wp-graphql/wp-graphql-acf/issues/25
				 */
				if ( empty( $acf_field['multiple'] ) ) {
					if('array' === $acf_field['return_format'] ){
						$field_config['type'] = [ 'list_of' => 'String' ];
						$field_config['resolve'] = function( $root ) use ( $acf_field) {
							$value = $this->get_acf_field_value( $root, $acf_field, true);
							
							return ! empty( $value ) && is_array( $value ) ? $value : [];
						};
					}else{
						$field_config['type'] = 'String';
					}
				} else {
					$field_config['type']    = ['list_of' => 'String'];
					$field_config['resolve'] = function ($root) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						return !empty($value) && is_array($value) ? $value : [];
					};
				}
				break;
			case 'radio':
				$field_config['type'] = 'String';
				break;
			case 'range':
				$field_config['type'] = 'Float';
				break;
			case 'number':
				$field_config['type'] = 'Float';
				break;
			case 'true_false':
				$field_config['type'] = 'Boolean';
				break;
			case 'date_picker':
			case 'time_picker':
			case 'date_time_picker':
				$field_config = [
					'type'    => 'String',
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {

						$value = $this->get_acf_field_value($root, $acf_field, true);

						if (!empty($value) && !empty($acf_field['return_format'])) {
							$value = date($acf_field['return_format'], strtotime($value));
						}
						return !empty($value) ? $value : null;
					},
				];
				break;
			case 'relationship':

				if (isset($acf_field['post_type']) && is_array($acf_field['post_type'])) {

					$field_type_name = $type_name . '_' . ucfirst(self::camel_case($acf_field['name']));

					if ($this->type_registry->get_type($field_type_name) == $field_type_name) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ($acf_field['post_type'] as $post_type) {
							if (in_array($post_type, get_post_types(['show_in_graphql' => true]), true)) {
								$type_names[$post_type] = get_post_type_object($post_type)->graphql_single_name;
							}
						}

						if (empty($type_names)) {
							$type = 'PostObjectUnion';
						} else {
							register_graphql_union_type($field_type_name, [
								'typeNames'   => $type_names,
								'resolveType' => function ($value) use ($type_names) {
									$post_type_object = get_post_type_object($value->post_type);
									return !empty($post_type_object->graphql_single_name) ? $this->type_registry->get_type($post_type_object->graphql_single_name) : null;
								}
							]);

							$type = $field_type_name;
						}
					}
				} else {
					$type = 'PostObjectUnion';
				}

				$field_config = [
					'type'    => ['list_of' => $type],
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$relationship = [];
						$value        = $this->get_acf_field_value($root, $acf_field);

						if (!empty($value) && is_array($value)) {
							foreach ($value as $post_id) {
								$post_object = get_post($post_id);
								if ($post_object instanceof \WP_Post) {
									$post_model     = new Post($post_object);
									$relationship[] = $post_model;
								}
							}
						}

						return isset($value) ? $relationship : null;
					},
				];
				break;
			case 'page_link':
			case 'post_object':

				if (isset($acf_field['post_type']) && is_array($acf_field['post_type'])) {
					$field_type_name = $type_name . '_' . ucfirst(self::camel_case($acf_field['name']));
					if ($this->type_registry->get_type($field_type_name) == $field_type_name) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ($acf_field['post_type'] as $post_type) {
							if (in_array($post_type, \get_post_types(['show_in_graphql' => true]), true)) {
								$type_names[$post_type] = get_post_type_object($post_type)->graphql_single_name;
							}
						}

						if (empty($type_names)) {
							$field_config['type'] = null;
							break;
						}

						register_graphql_union_type($field_type_name, [
							'typeNames'   => $type_names,
							'resolveType' => function ($value) use ($type_names) {
								$post_type_object = get_post_type_object($value->post_type);
								return !empty($post_type_object->graphql_single_name) ? $this->type_registry->get_type($post_type_object->graphql_single_name) : null;
							}
						]);

						$type = $field_type_name;
					}
				} else {
					$type = 'PostObjectUnion';
				}

				// If the field is allowed to be a multi select
				if (0 !== $acf_field['multiple']) {
					$type = ['list_of' => $type];
				}

				$field_config = [
					'type'    => $type,
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						$return = [];
						if (!empty($value)) {
							if (is_array($value)) {
								foreach ($value as $id) {
									$post = get_post($id);
									if (!empty($post)) {
										$return[] = new Post($post);
									}
								}
							} else {
								$post = get_post(absint($value));
								if (!empty($post)) {
									$return[] = new Post($post);
								}
							}
						}

						// If the field is allowed to be a multi select
						if (0 !== $acf_field['multiple']) {
							$return = !empty($return) ? $return : null;
						} else {
							$return = !empty($return[0]) ? $return[0] : null;
						}

						/**
						 * This hooks allows for filtering of the post object source. In case an non-core defined
						 * post-type is being targeted.
						 *
						 * @param mixed|null  $source  GraphQL Type source.
						 * @param mixed|null  $value   Root ACF field value.
						 * @param AppContext  $context AppContext instance.
						 * @param ResolveInfo $info    ResolveInfo instance.
						 */
						return apply_filters(
							'graphql_acf_post_object_source',
							$return,
							$value,
							$context,
							$info
						);
					},
				];
				break;
			case 'link':

				$field_type_name = 'ACF_Link';
				if ($this->type_registry->get_type($field_type_name) == $field_type_name) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __('ACF Link field', 'wp-graphql-acf'),
						'fields'      => [
							'url'    => [
								'type'        => 'String',
								'description' => __('The url of the link', 'wp-graphql-acf'),
							],
							'title'  => [
								'type'        => 'String',
								'description' => __('The title of the link', 'wp-graphql-acf'),
							],
							'target' => [
								'type'        => 'String',
								'description' => __('The target of the link (_blank, etc)', 'wp-graphql-acf'),
							],
						],
					]
				);
				$field_config['type'] = $field_type_name;
				break;
			case 'image':
			case 'file':
				$field_config = [
					'type'    => 'MediaItem',
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						return DataSource::resolve_post_object((int) $value, $context);
					},
				];
				break;
			case 'checkbox':
				$field_config = [
					'type'    => ['list_of' => 'String'],
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						return is_array($value) ? $value : null;
					},
				];
				break;
			case 'gallery':
				$field_config = [
					'type'    => ['list_of' => 'MediaItem'],
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$value   = $this->get_acf_field_value($root, $acf_field);
						$gallery = [];
						if (!empty($value) && is_array($value)) {
							foreach ($value as $image) {
								$post_object = get_post((int) $image);
								if ($post_object instanceof \WP_Post) {
									$post_model = new Post($post_object);
									$gallery[]  = $post_model;
								}
							}
						}

						return isset($value) ? $gallery : null;
					},
				];
				break;
			case 'user':

				$type = 'User';

				if (isset($acf_field['multiple']) &&  1 === $acf_field['multiple']) {
					$type = ['list_of' => $type];
				}

				$field_config = [
					'type'    => $type,
					'resolve' => function ($root, $args, $context, $info) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						$return = [];
						if (!empty($value)) {
							if (is_array($value)) {
								foreach ($value as $id) {
									$user = get_user_by('id', $id);
									if (!empty($user)) {
										$user = new User($user);
										if ('private' !== $user->get_visibility()) {
											$return[] = $user;
										}
									}
								}
							} else {
								$user = get_user_by('id', absint($value));
								if (!empty($user)) {
									$user = new User($user);
									if ('private' !== $user->get_visibility()) {
										$return[] = $user;
									}
								}
							}
						}

						// If the field is allowed to be a multi select
						if (0 !== $acf_field['multiple']) {
							$return = !empty($return) ? $return : null;
						} else {
							$return = !empty($return[0]) ? $return[0] : null;
						}

						return $return;
					},
				];
				break;
			case 'taxonomy':

				$type = 'TermObjectUnion';

				if (isset($acf_field['taxonomy'])) {
					$tax_object = get_taxonomy($acf_field['taxonomy']);
					if (isset($tax_object->graphql_single_name)) {
						$type = $tax_object->graphql_single_name;
					}
				}

				$is_multiple = isset($acf_field['field_type']) && in_array($acf_field['field_type'], array('checkbox', 'multi_select'));

				$field_config = [
					'type'    => $is_multiple ? ['list_of' => $type] : $type,
					'resolve' => function ($root, $args, $context, $info) use ($acf_field, $is_multiple) {
						$value = $this->get_acf_field_value($root, $acf_field);
						/**
						 * If this is multiple, the value will most likely always be an array.
						 * If it isn't, we want to return a single term id.
						 */
						if (!empty($value) && is_array($value)) {
							foreach ($value as $term) {
								$terms[] = DataSource::resolve_term_object((int) $term, $context);
							}
							return $terms;
						} else {
							return DataSource::resolve_term_object((int) $value, $context);
						}
					},
				];
				break;

				// Accordions are not represented in the GraphQL Schema.
			case 'accordion':
				$field_config = null;
				break;
			case 'group':
				$field_type_name = $type_name . '_' . ucfirst(self::camel_case($acf_field['name']));
				if ($this->type_registry->get_type($field_type_name)) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __('Field Group', 'wp-graphql-acf'),
						'fields'      => [
							'fieldGroupName' => [
								'type'    => 'String',
								'resolve' => function ($source) use ($acf_field) {
									return !empty($acf_field['name']) ? $acf_field['name'] : null;
								},
							],
						],
					]
				);


				$this->add_field_group_fields($acf_field, $field_type_name);

				$field_config['type'] = $field_type_name;
				break;

			case 'google_map':
				$field_type_name = 'ACF_GoogleMap';
				if ($this->type_registry->get_type($field_type_name) == $field_type_name) {
					$field_config['type'] = $field_type_name;
					break;
				}

				$fields = [
					'streetAddress' => [
						'type'        => 'String',
						'description' => __('The street address associated with the map', 'wp-graphql-acf'),
						'resolve'     => function ($root) {
							return isset($root['address']) ? $root['address'] : null;
						},
					],
					'latitude'      => [
						'type'        => 'Float',
						'description' => __('The latitude associated with the map', 'wp-graphql-acf'),
						'resolve'     => function ($root) {
							return isset($root['lat']) ? $root['lat'] : null;
						},
					],
					'longitude'     => [
						'type'        => 'Float',
						'description' => __('The longitude associated with the map', 'wp-graphql-acf'),
						'resolve'     => function ($root) {
							return isset($root['lng']) ? $root['lng'] : null;
						},
					],
				];

				// ACF 5.8.6 added more data to Google Maps field value
				// https://www.advancedcustomfields.com/changelog/
				if (\acf_version_compare(acf_get_db_version(), '>=', '5.8.6')) {
					$fields += [
						'streetName' => [
							'type'        => 'String',
							'description' => __('The street name associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['street_name']) ? $root['street_name'] : null;
							},
						],
						'streetNumber' => [
							'type'        => 'String',
							'description' => __('The street number associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['street_number']) ? $root['street_number'] : null;
							},
						],
						'city' => [
							'type'        => 'String',
							'description' => __('The city associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['city']) ? $root['city'] : null;
							},
						],
						'state' => [
							'type'        => 'String',
							'description' => __('The state associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['state']) ? $root['state'] : null;
							},
						],
						'stateShort' => [
							'type'        => 'String',
							'description' => __('The state abbreviation associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['state_short']) ? $root['state_short'] : null;
							},
						],
						'postCode' => [
							'type'        => 'String',
							'description' => __('The post code associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['post_code']) ? $root['post_code'] : null;
							},
						],
						'country' => [
							'type'        => 'String',
							'description' => __('The country associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['country']) ? $root['country'] : null;
							},
						],
						'countryShort' => [
							'type'        => 'String',
							'description' => __('The country abbreviation associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['country_short']) ? $root['country_short'] : null;
							},
						],
						'placeId' => [
							'type'        => 'String',
							'description' => __('The country associated with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['place_id']) ? $root['place_id'] : null;
							},
						],
						'zoom' => [
							'type'        => 'String',
							'description' => __('The zoom defined with the map', 'wp-graphql-acf'),
							'resolve'     => function ($root) {
								return isset($root['zoom']) ? $root['zoom'] : null;
							},
						],
					];
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __('Google Map field', 'wp-graphql-acf'),
						'fields'      => $fields,
					]
				);
				$field_config['type'] = $field_type_name;
				break;
			case 'repeater':
				$field_type_name = $type_name . '_' . self::camel_case($acf_field['name']);

				if ($this->type_registry->get_type($field_type_name)) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __('Field Group', 'wp-graphql-acf'),
						'fields'      => [
							'fieldGroupName' => [
								'type'    => 'String',
								'resolve' => function ($source) use ($acf_field) {
									return !empty($acf_field['name']) ? $acf_field['name'] : null;
								},
							],
						],
						'resolve'     => function ($source) use ($acf_field) {
							$repeater = $this->get_acf_field_value($source, $acf_field);

							return !empty($repeater) ? $repeater : [];
						},
					]
				);

				$this->add_field_group_fields($acf_field, $field_type_name);

				$field_config['type'] = ['list_of' => $field_type_name];
				break;

				/**
				 * Flexible content fields should return a Union of the Layouts that can be configured.
				 *
				 *
				 * Example Query of a flex field with the name "flex_field" and 2 groups
				 *
				 * {
				 *   post {
				 *      flexField {
				 *         ...on GroupOne {
				 *           textField
				 *           textAreaField
				 *         }
				 *         ...on GroupTwo {
				 *           imageField {
				 *             id
				 *             title
				 *           }
				 *         }
				 *      }
				 *   }
				 * }
				 *
				 */
			case 'flexible_content':

				$field_config    = null;
				$field_type_name = $type_name . '_' . ucfirst(self::camel_case($acf_field['name']));
				if ($this->type_registry->get_type($field_type_name)) {
					$field_config['type'] = $field_type_name;
					break;
				}

				if (!empty($acf_field['layouts']) && is_array($acf_field['layouts'])) {

					$union_types = [];
					foreach ($acf_field['layouts'] as $layout) {

						$flex_field_layout_name = !empty($layout['name']) ? ucfirst(self::camel_case($layout['name'])) : null;
						$flex_field_layout_name = !empty($flex_field_layout_name) ? $field_type_name . '_' . $flex_field_layout_name : null;

						/**
						 * If there are no layouts defined for the Flex Field
						 */
						if (empty($flex_field_layout_name)) {
							continue;
						}

						$layout_type            = $this->type_registry->get_type($flex_field_layout_name);

						if ($layout_type) {
							$union_types[$layout['name']] = $layout_type;
						} else {


							register_graphql_object_type($flex_field_layout_name, [
								'description' => __('Group within the flex field', 'wp-graphql-acf'),
								'fields'      => [
									'fieldGroupName' => [
										'type'    => 'String',
										'resolve' => function ($source) use ($flex_field_layout_name) {
											return !empty($flex_field_layout_name) ? $flex_field_layout_name : null;
										},
									],
								],
							]);

							$union_types[$layout['name']] = $flex_field_layout_name;


							$layout['parent']          = $acf_field;
							$layout['show_in_graphql'] = isset($acf_field['show_in_graphql']) ? (bool) $acf_field['show_in_graphql'] : true;
							$this->add_field_group_fields($layout, $flex_field_layout_name, true);
						}
					}

					register_graphql_union_type($field_type_name, [
						'typeNames'       => $union_types,
						'resolveType' => function ($value) use ($union_types) {
							return isset($union_types[$value['acf_fc_layout']]) ? $this->type_registry->get_type($union_types[$value['acf_fc_layout']]) : null;
						}
					]);

					$field_config['type']    = ['list_of' => $field_type_name];
					$field_config['resolve'] = function ($root, $args, $context, $info) use ($acf_field) {
						$value = $this->get_acf_field_value($root, $acf_field);

						return !empty($value) ? $value : [];
					};
				}
				break;
			default:
				break;
		}

		if (empty($field_config) || empty($field_config['type'])) {
			return null;
		}

		$config = array_merge($config, $field_config);

		$this->registered_field_names[] = $acf_field['name'];
		return $this->type_registry->register_field($type_name, $field_name, $config);
	}

	/**
	 * Given a field group array, this adds the fields to the specified Type in the Schema
	 *
	 * @param array  $field_group The group to add to the Schema.
	 * @param string $type_name   The Type name in the GraphQL Schema to add fields to.
	 * @param bool   $layout      Whether or not these fields are part of a Flex Content layout.
	 */
	protected function add_field_group_fields($field_group, $type_name, $layout = false) {

		/**
		 * If the field group has the show_in_graphql setting configured, respect it's setting
		 * otherwise default to true (for nested fields)
		 */
		$field_group['show_in_graphql'] = isset($field_group['show_in_graphql']) ? (bool) $field_group['show_in_graphql'] : true;

		/**
		 * Determine if the field group should be exposed
		 * to graphql
		 */
		if (!$this->should_field_group_show_in_graphql($field_group)) {
			return;
		}

		/**
		 * Get the fields in the group.
		 */
		$acf_fields = !empty($field_group['sub_fields']) || $layout ? $field_group['sub_fields'] : acf_get_fields($field_group);

		/**
		 * If there are no fields, bail
		 */
		if (empty($acf_fields) || !is_array($acf_fields)) {
			return;
		}

		/**
		 * Stores field keys to prevent duplicate field registration for cloned fields
		 */
		$processed_keys = [];

		/**
		 * Loop over the fields and register them to the Schema
		 */
		foreach ($acf_fields as $acf_field) {
			if (in_array($acf_field['key'], $processed_keys, true)) {
				continue;
			} else {
				$processed_keys[] = $acf_field['key'];
			}

			/**
			 * Setup data for register_graphql_field
			 */
			$explicit_name   = !empty($acf_field['graphql_field_name']) ? $acf_field['graphql_field_name'] : null;
			$name            = empty($explicit_name) && !empty($acf_field['name']) ? self::camel_case($acf_field['name']) : $explicit_name;
			$show_in_graphql = isset($acf_field['show_in_graphql']) ? (bool) $acf_field['show_in_graphql'] : true;
			$description     = isset($acf_field['instructions']) ? $acf_field['instructions'] : __('ACF Field added to the Schema by WPGraphQL ACF');

			/**
			 * If the field is missing a name or a type,
			 * we can't add it to the Schema.
			 */
			if (
				empty($name) ||
				true != $show_in_graphql
			) {

				/**
				 * Uncomment line below to determine what fields are not going to be output
				 * in the Schema.
				 */
				continue;
			}

			$config = [
				'name'            => $name,
				'description'     => $description,
				'acf_field'       => $acf_field,
				'acf_field_group' => $field_group,
			];

			$this->register_graphql_field($type_name, $name, $config);
		}
	}

	/**
	 * Returns all available GraphQL Types
	 */
	public static function get_all_graphql_types() {
		$graphql_types = array();

		/**
		 * Add post types exposed to GraphQL to GraphQL types
		 */
		$graphql_post_types = get_post_types(['show_in_graphql' => true]);

		if (!empty($graphql_post_types) && is_array($graphql_post_types)) {

			/**
			 * Prepare type key prefix and label surfix
			 */
			$key_prefix = 'post_type__';
			$label_surfix = ' (' . __('Post Type', 'wp-graphql-acf') . ')';

			/**
			 * Loop over the post types exposed to GraphQL
			 */
			foreach ($graphql_post_types as $post_type) {

				/**
				 * Get the post_type_object
				 */
				$post_type_object = get_post_type_object($post_type);

				$type_label = $post_type_object->labels->singular_name . $label_surfix;
				$type_key = $key_prefix . $post_type;

				$graphql_types[$type_key] = $type_label;
			}
		}

		/**
		 * Add taxonomies to GraphQL types
		 */
		$graphql_taxonomies = \WPGraphQL::get_allowed_taxonomies();

		if (!empty($graphql_taxonomies) && is_array($graphql_taxonomies)) {

			/**
			 * Prepare type key prefix and label surfix
			 */
			$key_prefix = 'taxonomy__';
			$label_surfix = ' (' . __('Taxonomy', 'wp-graphql-acf') . ')';

			/**
			 * Loop over the taxonomies exposed to GraphQL
			 */
			foreach ($graphql_taxonomies as $taxonomy) {
				/**
				 * Get the Taxonomy object
				 */
				$tax_object = get_taxonomy($taxonomy);
				$type_label = $tax_object->labels->singular_name . $label_surfix;
				$type_key = $key_prefix . $taxonomy;

				$graphql_types[$type_key] = $type_label;
			}
		}

		/**
		 * Add comment to GraphQL types
		 */
		$graphql_types['comment'] = __('Comment', 'wp-graphql-acf');

		/**
		 * Add menu to GraphQL types
		 */
		$graphql_types['menu'] = __('Menu', 'wp-graphql-acf');

		/**
		 * Add menu items to GraphQL types
		 */
		$graphql_types['menu_item'] = __('Menu Item', 'wp-graphql-acf');

		/**
		 * Add media items to GraphQL types
		 */
		$graphql_types['media_item'] = __('Media Item', 'wp-graphql-acf');

		/**
		 * Add users to GraphQL types
		 */
		$graphql_types['user'] = __('User', 'wp-graphql-acf');

		/**
		 * Add options pages to GraphQL types
		 */
		global $acf_options_page;
		if (isset($acf_options_page)) {
			/**
			 * Get a list of post types that have been registered to show in graphql
			 */
			$graphql_options_pages = acf_get_options_pages();

			/**
			 * If there are no post types exposed to GraphQL, bail
			 */
			if (!empty($graphql_options_pages) && is_array($graphql_options_pages)) {

				/**
				 * Prepare type key prefix and label surfix
				 */
				$key_prefix = 'acf_options_page__';
				$label_surfix = ' (' . __('ACF Options Page', 'wp-graphql-acf') . ')';

				/**
				 * Loop over the post types exposed to GraphQL
				 */
				foreach ($graphql_options_pages as $options_page_key => $options_page) {
					if (empty($options_page['show_in_graphql'])) {
						continue;
					}

					/**
					 * Get options page properties.
					 */
					$page_title = $options_page['page_title'];
					$page_slug  = $options_page['menu_slug'];

					$type_label = $page_title . $label_surfix;
					$type_key = $key_prefix . $page_slug;

					$graphql_types[$type_key] = $type_label;
				}
			}
		}

		return $graphql_types;
	}

	/**
	 * Adds acf field groups to GraphQL types.
	 */
	protected function add_acf_fields_to_graphql_types() {
		/**
		 * Get all the field groups
		 */
		$field_groups = acf_get_field_groups();

		/**
		 * If there are no acf field groups, bail
		 */
		if (empty($field_groups) || !is_array($field_groups)) {
			return;
		}

		/**
		 * Loop over all the field groups
		 */
		foreach ($field_groups as $field_group) {

			/**
			 * If there are no graphql types on for this field groups, move on to the next one.
			 */
			if (empty($field_group['graphql_types_on']) || !is_array($field_group['graphql_types_on'])) {
				continue;
			}

			/**
			 * Prepare default info
			 */
			$field_name = isset($field_group['graphql_field_name']) ? $field_group['graphql_field_name'] : Config::camel_case($field_group['title']);
			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$config              = [
				'name'            => $field_name,
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function ($root) use ($field_group) {
					return isset($root) ? $root : null;
				}
			];
			$default_description = $field_group['description'] ? $field_group['description'] . ' | ' : '';

			/**
			 * Loop over the GraphQL types for this field group on
			 */
			foreach ($field_group['graphql_types_on'] as $graphql_type) {

				/**
				 * Set type_name and description by graphql_type
				 */
				$type_pieces = explode('__', $graphql_type);
				switch ($type_pieces[0]) {
					case 'post_type':
						/**
						 * Get the Post Type name
						 */
						$post_type = substr($graphql_type, strlen('post_type__'));

						/**
						 * Get the post_type_object
						 */
						$post_type_object = get_post_type_object($post_type);

						if (empty($post_type_object) || !isset($post_type_object->graphql_single_name)) {
							continue 2;
						}

						$type_name = $post_type_object->graphql_single_name;
						$config['description'] = $field_group['description'];
						break;

					case 'taxonomy':
						/**
						 * Get the Taxonomy name
						 */
						$taxonomy = substr($graphql_type, strlen('taxonomy__'));

						/**
						 * Get the Taxonomy object
						 */
						$tax_object = get_taxonomy($taxonomy);

						if (empty($tax_object) || !isset($tax_object->graphql_single_name)) {
							continue 2;
						}

						$type_name = $tax_object->graphql_single_name;
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%1$s" was assigned to the "%2$s" taxonomy', 'wp-graphql-acf'), $field_group['title'], $tax_object->name);
						break;

					case 'comment':
						$type_name = 'Comment';
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Comments', 'wp-graphql-acf'), $field_group['title']);
						break;

					case 'menu':
						$type_name = 'Menu';
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Menus', 'wp-graphql-acf'), $field_group['title']);
						break;

					case 'menu_item':
						$type_name = 'MenuItem';
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Menu Items', 'wp-graphql-acf'), $field_group['title']);
						break;

					case 'media_item':
						$type_name = 'MediaItem';
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to attachments', 'wp-graphql-acf'), $field_group['title']);
						break;

					case 'user':
						$type_name = 'User';
						$config['description'] = $default_description . sprintf(__('Added to the GraphQL Schema because the ACF Field Group "%1$s" was assigned to Users edit or register form', 'wp-graphql-acf'), $field_group['title']);
						break;

					case 'acf_options_page':
						/**
						 * Get options page slug
						 */
						$page_slug = substr($graphql_type, strlen('acf_options_page__'));

						/**
						 * Get options page object
						 */
						$options_page = acf_get_options_page($page_slug);

						if (empty($options_page['show_in_graphql'])) {
							continue 2;
						}

						/**
						 * Get options page properties.
						 */
						$page_title = $options_page['page_title'];

						/**
						 * Create field and type names. Use explicit graphql_field_name
						 * if available and fallback to generating from title if not available.
						 */
						if (!empty($options_page['graphql_field_name'])) {
							$page_field_name = $options_page['graphql_field_name'];
							$type_name = ucfirst($options_page['graphql_field_name']);
						} else {
							$page_field_name = Config::camel_case($page_title);
							$type_name = ucfirst(Config::camel_case($page_title));
						}

						$config['description'] = $field_group['description'];

						/**
						 * Register options pages as graphql object type if not registered before
						 */
						if (!in_array($page_slug, $this->registered_options_pages)) {
							/**
							 * Register options page type to schema.
							 */
							register_graphql_object_type(
								$type_name,
								[
									'description' => sprintf(__('%s options', 'wp-graphql-acf'), $page_title),
									'fields'      => [
										'pageTitle' => [
											'type'    => 'String',
											'resolve' => function ($source) use ($page_title) {
												return !empty($page_title) ? $page_title : null;
											},
										],
										'pageSlug' => [
											'type'    => 'String',
											'resolve' => function ($source) use ($page_slug) {
												return !empty($page_slug) ? $page_slug : null;
											},
										],
									],
								]
							);

							/**
							 * Register options page type to the "RootQuery"
							 */
							$options_page['type'] = 'options_page';
							register_graphql_field(
								'RootQuery',
								$page_field_name,
								[
									'type'        => $type_name,
									'description' => sprintf(__('%s options', 'wp-graphql-acf'), $options_page['page_title']),
									'resolve'     => function () use ($options_page) {
										return !empty($options_page) ? $options_page : null;
									}
								]
							);

							/**
							 * Mark current page as restered by adding to registered page list
							 */
							$this->registered_options_pages[] = $page_slug;
						}

						break;

					default:
						continue 2;
				}

				$this->register_graphql_field($type_name, $field_name, $config);
			}
		}
	}

	/**
	 * Update field groups with graphql_types_on field
	 * Used when upgrading version from under 0.4.1 to above
	 *
	 * @since 0.4.1
	 */
	public static function auto_update_field_groups() {

		// Get all field groups
		$all_field_groups = acf_get_field_groups();

		// Init GraphQL field groups
		$graphql_field_groups = array();

		/**
		 * Function to push graphql_type_key field group
		 *
		 * @param array $field_groups Field group to push the key
		 * @param string $graphql_type_key GraphQL type key to push
		 */
		$func_push_type_key = function ($field_groups, $graphql_type_key) use (&$graphql_field_groups) {

			/**
			 * If there are no field groups for this post type, move on to the next one.
			 */
			if (empty($field_groups) || !is_array($field_groups)) {
				return;
			}

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ($field_groups as $field_group) {
				$field_group_id = $field_group['ID'];

				/**
				 * Add field group to if field group exists in graphql_field_groups array
				 */
				if (!array_key_exists($field_group_id, $graphql_field_groups)) {
					$graphql_field_groups[$field_group_id] = $field_group;
				}

				/**
				 * Init graphql_types_on field
				 */
				if (!isset($graphql_field_groups[$field_group_id]['graphql_types_on'])) {
					$graphql_field_groups[$field_group_id]['graphql_types_on'] = array();
				}

				/**
				 * Push the type key to the array
				 */
				if (!in_array($graphql_type_key, $graphql_field_groups[$field_group_id]['graphql_types_on'])) {
					$graphql_field_groups[$field_group_id]['graphql_types_on'][] = $graphql_type_key;
				}
			}
		};

		/**
		 * Handle Post Types
		 */
		// Get a list of post types that have been registered to show in graphql
		$graphql_post_types = get_post_types(['show_in_graphql' => true]);

		// If there are no post types exposed to GraphQL, bail
		if (!empty($graphql_post_types) && is_array($graphql_post_types)) {

			// Loop over the post types exposed to GraphQL
			foreach ($graphql_post_types as $post_type) {
				$field_groups = acf_get_field_groups(array('post_type' => $post_type));

				$func_push_type_key($field_groups, 'post_type__' . $post_type);
			}
		}

		/**
		 * Handle Taxonomies
		 */
		// Get a list of taxonomies that have been registered to show in graphql
		$graphql_taxonomies = \WPGraphQL::get_allowed_taxonomies();

		if (!empty($graphql_taxonomies) && is_array($graphql_taxonomies)) {

			// Loop over the taxonomies exposed to GraphQL
			foreach ($graphql_taxonomies as $taxonomy) {
				$field_groups = acf_get_field_groups(array('taxonomy' => $taxonomy));
				$func_push_type_key($field_groups, 'taxonomy__' . $taxonomy);
			}
		}

		/**
		 * Handle Comment, Menus, Menu Items, Media Items and Individual Posts
		 */
		// Init field group variables for comment, menu_field, menu_item and media_item
		$comment_field_groups = array();
		$menu_field_groups = array();
		$menu_item_field_groups = array();
		$media_item_field_groups = array();

		// Init field group variable for individual posts
		$allowed_post_types = get_post_types([
			'show_ui'         => true,
			'show_in_graphql' => true
		]);

		// Remove the `attachment` post_type, as it's treated special and we don't
		// want to add field groups in the same way we do for other post types
		unset($allowed_post_types['attachment']);

		$individual_post_groups = array_fill_keys($allowed_post_types, array());

		foreach ($all_field_groups as $field_group) {
			if (!empty($field_group['location']) && is_array($field_group['location'])) {
				foreach ($field_group['location'] as $locations) {
					if (!empty($locations) && is_array($locations)) {
						foreach ($locations as $location) {
							if ('==' === $location['operator']) {

								switch ($location['param']) {
									case 'comment':
										$comment_field_groups[] = $field_group;
										break;

									case 'nav_menu':
										$menu_field_groups[] = $field_group;
										break;

									case 'nav_menu_item':
										$menu_item_field_groups[] = $field_group;
										break;

									case 'attachment':
										$media_item_field_groups[] = $field_group;
										break;
									
									default:
										if (in_array($location['param'], $allowed_post_types, true)) {
											$individual_post_groups[$location['param']][] = $field_group;
										}
										break;
								}
							}
						}
					}
				}
			}
		}

		// Push comment, menu_field, menu_item and media_item keys
		$func_push_type_key($comment_field_groups, 'comment');
		$func_push_type_key($menu_field_groups, 'menu');
		$func_push_type_key($menu_item_field_groups, 'menu_item');
		$func_push_type_key($media_item_field_groups, 'media_item');

		// Push individual post keys
		foreach ($individual_post_groups as $post_type => $field_groups) {
			$func_push_type_key($field_groups, 'post_type__' . $post_type);
		}

		/**
		 * Handle Users
		 */
		// Get the field groups associated with the User edit form
		$user_edit_field_groups = acf_get_field_groups([
			'user_form' => 'edit',
		]);

		// Get the field groups associated with the User register form
		$user_register_field_groups = acf_get_field_groups([
			'user_form' => 'register',
		]);

		// Get a unique list of groups that match the register and edit user location rules
		$user_field_groups = array_merge($user_edit_field_groups, $user_register_field_groups);
		$user_field_groups = array_intersect_key($user_field_groups, array_unique(array_map('serialize', $field_groups)));
		$func_push_type_key($user_field_groups, 'user');

		/**
		 * Handle ACF Option Pages key
		 */
		global $acf_options_page;
		if (isset($acf_options_page)) {

			// Get a list of post types that have been registered to show in graphql
			$graphql_options_pages = acf_get_options_pages();

			// If there are no post types exposed to GraphQL, bail
			if (!empty($graphql_options_pages) && is_array($graphql_options_pages)) {
				
				// Loop over the post types exposed to GraphQL
				foreach ($graphql_options_pages as $options_page_key => $options_page) {
					if (empty($options_page['show_in_graphql'])) {
						continue;
					}

					// Get the field groups associated with the options page
					$field_groups = acf_get_field_groups(
						[
							'options_page' => $options_page['menu_slug'],
						]
					);

					$func_push_type_key($field_groups, 'acf_options_page__' . $options_page['menu_slug']);
				}
			}
		}

		/**
		 * Update field group data
		 */
		foreach ($graphql_field_groups as $field_group) {
			acf_update_field_group($field_group);
		}
	}
}
