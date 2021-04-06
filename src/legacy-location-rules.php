<?php

namespace WPGraphQL\ACF;

use WPGraphQL\Utils\Utils;

/**
 * Class LegacyLoctionRules
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
class LegacyLoctionRules {

	/**
	 * The field groups that have legacy rules mapped.
	 *
	 * @var array
	 */
	public $field_groups = [];

	/**
	 * Given the name of a GraphqL Field Group and the name of a GraphQL Type, this sets the
	 * field group to show in that Type
	 *
	 * @param string $field_group_name The name of the ACF Field Group
	 * @param string $graphql_type_name The name of the GraphQL Type
	 */
	public function set_graphql_type( string $field_group_name, string $graphql_type_name ) {
		$this->field_groups[ Utils::format_field_name( $field_group_name ) ][] = Utils::format_type_name( $graphql_type_name );
	}

	/**
	 * Get the rules
	 *
	 * @return array
	 */
	public function get_rules() {
		return isset( $this->field_groups ) && ! empty( $this->field_groups ) ? $this->field_groups : [];
	}

	/**
	 * Determine GraphQL Schema location rules based on ACF Location rules for field groups
	 * that are configured with no `graphql_types` field.
	 *
	 * @return void
	 */
	public function determine_legacy_location_rules() {

		$this->post_object_location_rules();
		$this->term_object_location_rules();
		$this->comment_location_rules();
		$this->menu_location_rules();
		$this->menu_item_location_rules();
		$this->media_location_rules();
		$this->individual_post_location_rules();
		$this->user_location_rules();
		$this->option_page_location_rules();

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
			$field_groups = acf_get_field_groups(
				[
					'post_type' => $post_type,
				]
			);

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

				if ( isset( $field_group['graphql_types' ] ) ) {
					 continue;
				}

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
			$field_groups = acf_get_field_groups(
				[
					'taxonomy' => $taxonomy,
				]
			);

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

				if ( isset( $field_group['graphql_types' ] ) ) {
					continue;
				}

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
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {

			if ( isset( $field_group['graphql_types' ] ) ) {
				continue;
			}

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
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {

			if ( isset( $field_group['graphql_types'] ) ) {
				continue;
			}

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
		$field_groups = acf_get_field_groups();
		foreach ( $field_groups as $field_group ) {

			if ( isset( $field_group['graphql_types'] ) ) {
				continue;
			}

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
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {

			if ( isset( $field_group['graphql_types'] ) ) {
				continue;
			}

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
		$field_groups = acf_get_field_groups();

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

			if ( isset( $field_group['graphql_types'] ) ) {
				continue;
			}

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
		$user_edit_field_groups = acf_get_field_groups( [
			'user_form' => 'edit',
		] );

		/**
		 * Get the field groups associated with the User register form
		 */
		$user_register_field_groups = acf_get_field_groups( [
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

			if ( isset( $field_group['graphql_types'] ) ) {
				continue;
			}

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
			if ( empty( $options_page['show_in_graphql'] ) || isset( $options_page['graphql_types'] ) ) {
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
