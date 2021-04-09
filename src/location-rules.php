<?php

namespace WPGraphQL\ACF;

use WPGraphQL\Utils\Utils;

/**
 * Class LocationRules
 *
 * Determine the WPGraphQL Schema Location rules based on the ACF Location rules.
 *
 * ACF Field Groups are now explicitly set to show on specific GraphQL Types in the Schema.
 *
 * Before, GraphQL Schema locations were inferred from the ACF Admin Location rules, but this
 * was often quite buggy as many ACF Location rules assume admin/editorial context that is not
 * present when the GraphQL Schema is being built.
 *
 * This Class is a polyfill for ACF Field Groups that were registered without a `graphql_types`
 * field, and need to fall back to the old Location Rules.
 *
 * Any Field Group that has `graphql_types` set, will use the explicit `graphql_types` configuration.
 *
 * @package WPGraphQL\ACF
 */
class LocationRules {

	/**
	 * The field groups that have location rules mapped.
	 *
	 * @var array
	 */
	public $mapped_field_groups = [];

	/**
	 * @var array
	 */
	public $unset_types = [];

	/**
	 * The field groups to map to location rules
	 *
	 * @var array|mixed
	 */
	public $acf_field_groups = [];

	/**
	 * LocationRules constructor.
	 *
	 * @param array $acf_field_groups
	 */
	public function __construct( $acf_field_groups = [] ) {
		$this->acf_field_groups = isset( $acf_field_groups ) && ! empty( $acf_field_groups ) ? $acf_field_groups : acf_get_field_groups();
	}

	/**
	 * Given a field name, formats it for GraphQL
	 *
	 * @param string $field_name The field name to format
	 *
	 * @return string
	 */
	public function format_field_name( string $field_name ) {

		$replaced = preg_replace( '[^a-zA-Z0-9 -]', '_', $field_name );

		// If any values were replaced, use the replaced string as the new field name
		if ( ! empty( $replaced ) ) {
			$field_name = $replaced;
		}

		$field_name = lcfirst( $field_name );
		$field_name = lcfirst( str_replace( '-', ' ', ucwords( $field_name, '_' ) ) );
		$field_name = lcfirst( str_replace( ' ', '', ucwords( $field_name, ' ' ) ) );

		return $field_name;
	}

	/**
	 * Given a type name, formats it for GraphQL
	 *
	 * @param string $type_name The type name to format
	 *
	 * @return string
	 */
	public function format_type_name( string $type_name ) {
		return ucfirst( $this->format_field_name( $type_name ) );
	}

	/**
	 * Given the name of a GraphqL Field Group and the name of a GraphQL Type, this sets the
	 * field group to show in that Type
	 *
	 * @param string $field_group_name  The name of the ACF Field Group
	 * @param string $graphql_type_name The name of the GraphQL Type
	 */
	public function set_graphql_type( string $field_group_name, string $graphql_type_name ) {
		$this->mapped_field_groups[ Utils::format_field_name( $field_group_name ) ][] = $this->format_type_name( $graphql_type_name );
	}

	/**
	 * Given the name of a GraphqL Field Group and the name of a GraphQL Type, this unsets the
	 * GraphQL Type for the field group
	 *
	 * @param string $field_group_name  The name of the ACF Field Group
	 * @param string $graphql_type_name The name of the GraphQL Type
	 */
	public function unset_graphql_type( string $field_group_name, string $graphql_type_name ) {
		$this->unset_types[ $this->format_field_name( $field_group_name ) ][] = $this->format_type_name( $graphql_type_name );
	}

	/**
	 * Get the rules
	 *
	 * @return array
	 */
	public function get_rules() {

		$mapped_field_groups = isset( $this->mapped_field_groups ) && ! empty( $this->mapped_field_groups ) ? $this->mapped_field_groups : [];

		if ( empty( $mapped_field_groups ) )  {
			return [];
		}


		if ( empty( $this->unset_types ) ) {
			return $mapped_field_groups;
		}

		/**
		 * Remove any Types that were flagged to unset
		 */
		foreach ( $this->unset_types as $field_group => $types ) {
			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					if ( isset( $this->mapped_field_groups[ $field_group ] ) ) {
						if( ( $key = array_search( $type, $mapped_field_groups[ $field_group ] ) ) !== false ) {
							unset( $mapped_field_groups[ $field_group ][ $key ] );
						}
					}
				}
			}
		}

		return $mapped_field_groups;

	}

	/**
	 * Determine GraphQL Schema location rules based on ACF Location rules for field groups
	 * that are configured with no `graphql_types` field.
	 *
	 * @return void
	 */
	public function determine_location_rules() {

		if ( ! empty( $this->acf_field_groups ) ) {
			foreach ( $this->acf_field_groups as $field_group ) {

				$field_group_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];

				if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {

					foreach ( $field_group['location'] as $location_rule_group ) {
						if ( ! empty( $location_rule_group ) ) {

							$and_params = wp_list_pluck( $location_rule_group, 'param' );
							$and_params = ! empty( $and_params ) ? array_values( $and_params ) : [];

							foreach ( $location_rule_group as $group => $rule ) {

								$operator = isset( $rule['operator'] ) ? $rule['operator'] : '==';
								$param = isset( $rule['param'] ) ? $rule['param'] : null;
								$value = isset( $rule['value'] ) ? $rule['value'] : null;

								if ( empty( $param ) || empty( $value ) ) {
									continue;
								}

								// Depending on the param of the rule, there's different logic to
								// map to the Schema
								switch( $param ) {
									case 'post_type':

										$key = array_search( 'post_type', $and_params );
										unset( $and_params[ $key ] );

										$has_conflict = false;
										$allowed_and_params = [
											'post_status',
											'post_format',
											'post_category',
											'post_taxonomy',
											'post',
										];

										if ( ! empty( $and_params ) ) {
											foreach ( $and_params as $key => $allowed ) {
												if ( false === array_search( $allowed, $allowed_and_params ) ) {
													$has_conflict = true;
												}
											}
										}

										if ( true === $has_conflict ) {
											break;
										}

										$this->determine_post_type_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'post_template':
									case 'page_template':
										$this->determine_post_template_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'post_status':
										$this->determine_post_status_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'post_format':
									case 'post_category':
									case 'post_taxonomy':
										$this->determine_post_taxonomy_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'post':
										$this->determine_post_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'page_type':
										$this->determine_page_type_rules( $field_group_name, $param, $operator, $value );
										break;
									case 'page_parent':
									case 'page':
										// If page or page_parent is set, regardless of operator and value,
										// we can add the field group to the Page type
										$this->set_graphql_type( $field_group_name, 'Page' );
										break;
									case 'current_user':
									case 'current_user_role':
										// @todo:
										// Right now, if you set current_user or current_user_role as the only rule,
										// ACF adds the field group to every possible location in the Admin.
										// This seems a bit heavy handed. 🤔
										// We need to think through this a bit more, and how this rule
										// Can be composed with other rules, etc.
										break;
									case 'user_form':
									case 'user_role':
										// If user_role or user_form params are set, we need to expose the field group
										// to the User type
										$this->set_graphql_type( $field_group_name, 'User' );
										break;
									case 'taxonomy':
										$this->determine_taxonomy_rules( $field_group_name, $param, $operator, $value );
										break;
									default:
										// If a built-in location rule could not be matched,
										// Custom rules (from extensions, etc) can hook in here and apply their
										// rules to the WPGraphQL Schema
										do_action( 'graphql_acf_match_location_rule', $field_group_name, $param, $operator, $value, $this );
										break;

								}

							}
						}
					}
				}
			}
		}

//		wp_send_json( [ 'goo', 'unset' => $this->unset_types, 'set' => $this->mapped_field_groups ] );

//		$this->post_object_location_rules();
//		$this->term_object_location_rules();
//		$this->comment_location_rules();
//		$this->menu_location_rules();
//		$this->menu_item_location_rules();
//		$this->media_location_rules();
//		$this->individual_post_location_rules();
//		$this->user_location_rules();
//		$this->option_page_location_rules();

	}

	/**
	 * Returns an array of Post Templates
	 *
	 * @return array
	 */
	public function get_graphql_post_template_types() {

		$registered_page_templates = wp_get_theme()->get_post_templates();

		$page_templates['default'] = 'DefaultTemplate';

		if ( ! empty( $registered_page_templates ) && is_array( $registered_page_templates ) ) {

			foreach ( $registered_page_templates as $post_type_templates ) {
				// Post templates are returned as an array of arrays. PHPStan believes they're returned as
				// an array of strings and believes this will always evaluate to false.
				// We should ignore the phpstan check here.
				// @phpstan-ignore-next-line
				if ( ! empty( $post_type_templates ) && is_array( $post_type_templates ) ) {
					foreach ( $post_type_templates as $file => $name ) {

						$name          = ucwords( $name );
						$replaced_name = preg_replace( '/[^\w]/', '', $name );

						if ( ! empty( $replaced_name ) ) {
							$name = $replaced_name;
						}

						if ( preg_match( '/^\d/', $name ) || false === strpos( strtolower( $name ), 'template' ) ) {
							$name = 'Template_' . $name;
						}

						$page_templates[ $file ] = $name;
					}
				}
			}
		}

		return $page_templates;
	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_type_rules( string $field_group_name, string $param, string $operator, string $value ) {
		$allowed_post_types = get_post_types( [ 'show_in_graphql' => true ] );

		if ( empty( $allowed_post_types ) ) {
			return;
		}

		if ( '==' === $operator ) {

			// If all post types
			if ( 'all' === $value ) {

				// loop over and set all post types
				foreach ( $allowed_post_types as $allowed_post_type ) {

					$post_type_object = get_post_type_object( $allowed_post_type );
					$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}
			} else {
				if ( in_array( $value, $allowed_post_types, true ) ) {
					$post_type_object = get_post_type_object( $value );
					$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}
			}


		}

		if ( '!=' === $operator ) {

			if ( 'all' !== $value ) {
				// loop over and set all post types
				foreach ( $allowed_post_types as $allowed_post_type ) {
					$post_type_object = get_post_type_object( $allowed_post_type );
					$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}
			}

			$post_type_object = get_post_type_object( $value );
			$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
			if ( ! empty( $graphql_name ) ) {
				$this->unset_graphql_type( $field_group_name, $graphql_name );
			}
		}
	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_template_rules( string $field_group_name, string $param, string $operator, string $value ) {

		$templates = $this->get_graphql_post_template_types();

		if ( ! is_array( $templates ) || empty( $templates ) ) {
			return;
		}

		if ( '==' === $operator ) {

			// If the template is available in GraphQL, set it
			if ( isset( $templates[ $value ] ) ) {
				$this->set_graphql_type( $field_group_name, $templates[ $value ] );
			}
		}

		if ( '!=' === $operator ) {

			foreach ( $templates as $name => $template_type ) {
				$this->set_graphql_type( $field_group_name, $template_type );
			}

			// If the Template is available in GraphQL, unset it
			if ( isset( $templates[ $value ] ) ) {
				$this->unset_graphql_type( $field_group_name, $templates[ $value ] );
			}
		}

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_status_rules( string $field_group_name, string $param, string $operator, string $value ) {
		// @todo: Should post status affect the GraphQL Schema at all?
		// If a field group is set to show on "post_status == publish" as the only rule, what post type does that apply to? All? 🤔
		// If a field group is set to show on "post_status != draft" does that mean the field group should be available on all post types in the Schema by default?
		// This seems like a very difficult rule to translate to the Schema.
		// Like, lets say I add a field group called: "Editor Notes" that I want to show for any status that is not "publish". In theory, if that's my only rule, that seems like it should apply to all post types across the board, and show in the Admin in any state of the post, other than publish. 🤔

		// ACF Admin behavior seems to add it to the Admin on all post types, so WPGraphQL
		// should respect this rule and also add it to all post types. The resolver should
		// then determine whether to resolve the data or not, based on this rule.

		// If Post Status is used to qualify a field group location,
		// It will be added to the Schema for any Post Type that is set to show in GraphQL
		$allowed_post_types = get_post_types([ 'show_in_graphql' => true ]);
		foreach ( $allowed_post_types as $post_type ) {

			$post_type_object = get_post_type_object( $post_type );
			$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
			if ( ! empty( $graphql_name ) ) {
				$this->set_graphql_type( $field_group_name, $graphql_name );
			}
		}

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_format_rules( string $field_group_name, string $param, string $operator, string $value ) {

		$post_format_taxonomy = get_taxonomy( 'post_format' );
		$post_format_post_types = $post_format_taxonomy->object_type;

		if ( ! is_array( $post_format_post_types ) || empty( $post_format_post_types ) ) {
			return;
		}

		// If Post Format is used to qualify a field group location,
		// It will be added to the Schema for any Post Type that supports post formats
		// And shows in GraphQL
		$allowed_post_types = get_post_types(['show_in_graphql' => true ]);
		foreach ( $allowed_post_types as $post_type ) {
			if ( in_array( $post_type, $post_format_post_types, true ) ) {
				$post_type_object = get_post_type_object( $value );
				$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
				if ( ! empty( $graphql_name ) ) {
					$this->set_graphql_type( $field_group_name, $graphql_name );
				}
			}
		}

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_taxonomy_rules( string $field_group_name, string $param, string $operator, string $value ) {

		// If Post Taxonomy is used to qualify a field group location,
		// It will be added to the Schema for the Post post type
		$this->set_graphql_type( $field_group_name, 'Post' );

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_post_rules( string $field_group_name, string $param, string $operator, string $value ) {

		// If a Single post is used to qualify a field group location,
		// It will be added to the Schema for the GraphQL Type for the post_type of the Post
		// it is assigned to

		if ( '==' === $operator ) {

			if ( absint( $value ) ) {
				$post = get_post( absint( $value ) );
				if ( $post instanceof \WP_Post ) {
					$post_type_object = get_post_type_object( $post->post_type );
					if ( isset( $post_type_object->show_in_graphql ) && true === $post_type_object->show_in_graphql ) {
						if ( isset( $post_type_object->graphql_single_name ) ) {
							$this->set_graphql_type( $field_group_name, $post_type_object->graphql_single_name );
						}
					}
				}
			}

		}

		// If a single post is used as not equal,
		// the field group should be added to ALL post types in the Schema
		if ( '!=' === $operator ) {

			$allowed_post_types = get_post_types( [ 'show_in_graphql' => true ] );

			if ( empty( $allowed_post_types ) ) {
				return;
			}

			// loop over and set all post types
			foreach ( $allowed_post_types as $allowed_post_type ) {

				$post_type_object = get_post_type_object( $allowed_post_type );
				$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
				if ( ! empty( $graphql_name ) ) {
					$this->set_graphql_type( $field_group_name, $graphql_name );
				}
			}
		}

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_page_type_rules( string $field_group_name, string $param, string $operator, string $value ) {

		// If front_page or posts_page is set to equal_to or not_equal_to
		// then the field group should be shown on the Post type
		if ( in_array( $value, [ 'front_page', 'posts_page' ], true ) ) {
			$this->set_graphql_type( $field_group_name, 'Page' );
		}

		// If top_level, parent, or child is set as equal_to or not_equal_to
		// then the field group should be shown on all hierarchical post types
		if ( in_array( $value, [ 'top_level', 'parent', 'child' ], true ) ) {

			$hierarchical_post_types = get_post_types( [ 'show_in_graphql' => true, 'hierarchical' => true ] );

			if ( empty( $hierarchical_post_types ) ) {
				return;
			}

			// loop over and set all post types
			foreach ( $hierarchical_post_types as $allowed_post_type ) {

				$post_type_object = get_post_type_object( $allowed_post_type );
				$graphql_name = isset( $post_type_object->graphql_single_name ) ? $post_type_object->graphql_single_name : null;
				if ( ! empty( $graphql_name ) ) {
					$this->set_graphql_type( $field_group_name, $graphql_name );
				}
			}
		}

	}

	/**
	 * Determines how the ACF Rules should apply to the WPGraphQL Schema
	 *
	 * @param string $field_group_name The name of the ACF Field Group the rule applies to
	 * @param string $param The parameter of the rule
	 * @param string $operator The operator of the rule
	 * @param string $value The value of the rule
	 */
	public function determine_taxonomy_rules( string $field_group_name, string $param, string $operator, string $value ) {

		$allowed_taxonomies = get_taxonomies( [ 'show_in_graphql' => true ] );

		if ( empty( $allowed_taxonomies ) ) {
			return;
		}

		if ( '==' === $operator ) {

			// If all post types
			if ( 'all' === $value ) {

				// loop over and set all post types
				foreach ( $allowed_taxonomies as $allowed_taxonomy ) {

					$tax_object = get_taxonomy( $allowed_taxonomy );
					$graphql_name = isset( $tax_object->graphql_single_name ) ? $tax_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}

			} else {
				if ( in_array( $value, $allowed_taxonomies, true ) ) {
					$tax_object = get_taxonomy( $value );
					$graphql_name = isset( $tax_object->graphql_single_name ) ? $tax_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}
			}


		}

		if ( '!=' === $operator ) {

			if ( 'all' !== $value ) {

				// loop over and set all post types
				foreach ( $allowed_taxonomies as $allowed_taxonomy ) {

					$tax_object = get_taxonomy( $allowed_taxonomy );
					$graphql_name = isset( $tax_object->graphql_single_name ) ? $tax_object->graphql_single_name : null;
					if ( ! empty( $graphql_name ) ) {
						$this->set_graphql_type( $field_group_name, $graphql_name );
					}
				}

				$tax_object = get_taxonomy( $value );
				$graphql_name = isset( $tax_object->graphql_single_name ) ? $tax_object->graphql_single_name : null;
				if ( ! empty( $graphql_name ) ) {
					$this->unset_graphql_type( $field_group_name, $graphql_name );
				}

			}
		}

	}

	public function determine_individual_post_rules() {

	}

	public function determine_page_rules() {

	}

	public function determine_user_rules() {

	}

	public function determine_attachment_rules() {

	}

	public function determine_comment_rules() {

	}

	public function determine_menu_rules() {

	}

	public function determine_menu_item_rules() {

	}

	public function determine_block_rules() {
		// @todo: ACF Blocks are not formally supported by WPGraphQL / WPGraphQL for ACF. More to come in the future!
	}

	public function determine_options_rules() {

	}

	/**
	 * Determine the Schema location for post object location rules
	 *
	 * @return void
	 */
	public function post_object_location_rules() {

		/**
		 * Get a list of post types that have been registered to show in graphql
		 */
		$graphql_post_types = get_post_types( [ 'show_in_graphql' => true, 'show_ui' => true ] );

		/**
		 * If there are no post types exposed to GraphQL, bail
		 */
		if ( empty( $graphql_post_types ) || ! is_array( $graphql_post_types ) ) {
			return;
		}

		/**
		 * Loop over the post types exposed to GraphQL
		 */
		foreach ( $graphql_post_types as $post_type ) {

			/**
			 * Get the field groups associated with the post type
			 */
			$field_groups = acf_filter_field_groups( $this->acf_field_groups, [ 'post_type' => $post_type ] );

			/**
			 * If there are no field groups for this post type, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Get the post_type_object
			 */
			$post_type_object = get_post_type_object( $post_type );

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ( $field_groups as $field_group ) {
				$field_group_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];
				$this->set_graphql_type( $field_group_name, $post_type_object->graphql_single_name );
			}

		}

	}

	/**
	 * Determine the Schema location for term object location rules
	 *
	 * @return void
	 */
	public function term_object_location_rules() {

		/**
		 * Get a list of taxonomies that have been registered to show in graphql
		 */
		$graphql_taxonomies = get_taxonomies([ 'show_in_graphql' => true, 'show_ui' => true, ]);

		/**
		 * If there are no taxonomies exposed to GraphQL, bail
		 */
		if ( empty( $graphql_taxonomies ) || ! is_array( $graphql_taxonomies ) ) {
			return;
		}

		/**
		 * Loop over the taxonomies exposed to GraphQL
		 */
		foreach ( $graphql_taxonomies as $taxonomy ) {

			/**
			 * Get the field groups associated with the taxonomy
			 */
			$field_groups = acf_filter_field_groups( $this->acf_field_groups, [ 'taxonomy' => $taxonomy ] );

			/**
			 * If there are no field groups for this taxonomy, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Get the Taxonomy object
			 */
			$tax_object = get_taxonomy( $taxonomy );

			if ( empty( $tax_object ) || ! isset( $tax_object->graphql_single_name ) ) {
				return;
			}

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ( $field_groups as $field_group ) {
				$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
				$this->set_graphql_type( $field_name, $tax_object->graphql_single_name );
			}
		}

	}

	/**
	 * Determine the Schema location for comment location rules
	 *
	 * @return void
	 */
	public function comment_location_rules() {

		$comment_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = $this->acf_field_groups;

		foreach ( $field_groups as $field_group ) {

			$comment_field_groups = [];

			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'comment' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'comment' === $location['param'] && '==' === $location['operator'] ) {
								$comment_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $comment_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $comment_field_groups as $field_group ) {
			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$this->set_graphql_type( $field_name, 'Comment' );
		}

	}

	/**
	 * Determine the Schema location for menu location rules
	 *
	 * @return void
	 */
	public function menu_location_rules() {

		$menu_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = $this->acf_field_groups;

		foreach ( $field_groups as $field_group ) {


			$menu_field_groups = [];

			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'nav_menu' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'nav_menu' === $location['param'] && '==' === $location['operator'] ) {
								$menu_field_groups[] = $field_group;
								break;
							}
						}
					}
				}
			}
		}

		if ( empty( $menu_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $menu_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$this->set_graphql_type( $field_name, 'Menu' );

		}

	}

	/**
	 * Determine the Schema location for Menu Item location rules
	 *
	 * @return void
	 */
	public function menu_item_location_rules() {

		$menu_item_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = $this->acf_field_groups;
		foreach ( $field_groups as $field_group ) {

			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'nav_menu_item' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'nav_menu_item' === $location['param'] && '==' === $location['operator'] ) {
								$menu_item_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $menu_item_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $menu_item_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];
			$this->set_graphql_type( $field_name, 'MenuItem' );

		}

	}

	/**
	 * Determine the Schema location for Media location rules
	 *
	 * @return void
	 */
	public function media_location_rules() {

		$media_item_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = $this->acf_field_groups;

		foreach ( $field_groups as $field_group ) {

			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'attachment' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'attachment' === $location['param'] && '==' === $location['operator'] ) {
								$media_item_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $media_item_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $media_item_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$this->set_graphql_type( $field_name, 'MediaItem' );

		}

	}

	/**
	 * Determine the Schema location for Individual Post location rules
	 *
	 * @return void
	 */
	public function individual_post_location_rules() {

		$post_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = $this->acf_field_groups;

		$allowed_post_types = get_post_types( [
			'show_ui'         => true,
			'show_in_graphql' => true
		] );

		/**
		 * Remove the `attachment` post_type, as it's treated special and we don't
		 * want to add field groups in the same way we do for other post types
		 */
		unset( $allowed_post_types['attachment'] );


		foreach ( $field_groups as $field_group ) {

			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {

							/**
							 * If the operator is not equal to, we don't need to do anything,
							 * so we can just continue
							 */
							if ( '!=' === $location['operator'] ) {
								continue;
							}

							/**
							 * If the param (the post_type) is in the array of allowed_post_types
							 */
							if ( in_array( $location['param'], $allowed_post_types, true ) && '==' === $location['operator'] ) {

								$post_field_groups[] = [
									'type'        => $location['param'],
									'field_group' => $field_group,
									'post_id'     => $location['value']
								];
							}
						}
					}
				}
			}
		}


		/**
		 * If no field groups are assigned to a specific post, we don't need to modify the Schema
		 */
		if ( empty( $post_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups assigned to a specific post
		 * and register them to the Schema
		 */
		foreach ( $post_field_groups as $key => $group ) {

			if ( empty( $group['field_group'] ) || ! is_array( $group['field_group'] ) ) {
				continue;
			}

			$post_object = get_post( (int) $group['post_id'] );

			$allowed_post_types = get_post_types( [ 'show_in_graphql' => true ] );
			if ( ! $post_object instanceof \WP_Post || ! in_array( $post_object->post_type, $allowed_post_types, true ) ) {
				continue;
			}

			$field_group      = $group['field_group'];
			$post_type_object = get_post_type_object( $post_object->post_type );

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$this->set_graphql_type( $field_name, $post_type_object->graphql_single_name );

		}

	}

	/**
	 * Determine the Schema location for User location rules
	 *
	 * @return void
	 */
	public function user_location_rules() {

		/**
		 * Get the field groups associated with the User edit form
		 */
		$user_edit_field_groups = acf_filter_field_groups( $this->acf_field_groups, [
			'user_form' => 'edit',
		] );

		/**
		 * Get the field groups associated with the User register form
		 */
		$user_register_field_groups = acf_filter_field_groups( $this->acf_field_groups, [
			'user_form' => 'register',
		] );

		$user_register_field_groups = ! empty( $user_register_field_groups ) ? $user_register_field_groups : [];
		$user_edit_field_groups = ! empty( $user_edit_field_groups ) ? $user_edit_field_groups : [];

		/**
		 * Get a unique list of groups that match the register and edit user location rules
		 */
		$field_groups = array_merge( $user_edit_field_groups, $user_register_field_groups );
		$field_groups = array_intersect_key( $field_groups, array_unique( array_map( 'serialize', $field_groups ) ) );

		if ( empty( $field_groups ) ) {
			return;
		}

		foreach ( $field_groups as $field_group ) {

			$field_name          = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$this->set_graphql_type( $field_name, 'User' );
		}

	}

	/**
	 * Determine the Schema location for Option Page location rules
	 *
	 * @return void
	 */
	public function option_page_location_rules() {


		global $acf_options_page;

		if ( ! isset( $acf_options_page ) ) {
			return ;
		}

		/**
		 * Get a list of post types that have been registered to show in graphql
		 */
		$graphql_options_pages = acf_get_options_pages();

		/**
		 * If there are no post types exposed to GraphQL, bail
		 */
		if ( empty( $graphql_options_pages ) || ! is_array( $graphql_options_pages ) ) {
			return;
		}

		/**
		 * Loop over the post types exposed to GraphQL
		 */
		foreach ( $graphql_options_pages as $options_page_key => $options_page ) {
			if ( empty( $options_page['show_in_graphql'] ) ) {
				continue;
			}

			/**
			 * Get options page properties.
			 */
			$page_title = $options_page['page_title'];
			$page_slug  = $options_page['menu_slug'];

			/**
			 * Get the field groups associated with the options page
			 */
			$field_groups = acf_get_field_groups(
				[
					'options_page' => $options_page['menu_slug'],
				]
			);

			/**
			 * If there are no field groups for this options page, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Loop over the field groups for this options page.
			 */
			$options_page_fields = array();
			foreach ( $field_groups as $field_group ) {
				$field_name            = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
				$options_page_fields[] = $field_name;

			}

			/**
			 * Continue if no options to show in GraphQL
			 */
			if ( empty( $options_page_fields ) ) {
				continue;
			}

			/**
			 * Create field and type names. Use explicit graphql_field_name
			 * if available and fallback to generating from title if not available.
			 */
			if ( ! empty( $options_page['graphql_field_name'] ) ) {
				$field_name = $options_page['graphql_field_name'];
				$type_name  = ucfirst( $options_page['graphql_field_name'] );
			} else {
				$field_name = Config::camel_case( $page_title );
				$type_name  = ucfirst( Config::camel_case( $page_title ) );
			}

			/**
			 * Register option page fields to the option page type.
			 */
			foreach ( $options_page_fields as $name => $config ) {
				$this->set_graphql_type( $field_name, $type_name );
			}

		}

	}

}
