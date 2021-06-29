<?php

namespace WPGraphQL\ACF\Fields;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\ACF\Registry;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;
use WPGraphQL\Utils\Utils;

/**
 * Class AcfField
 *
 * @package WPGraphQL\ACF\Fields
 */
class AcfField {

	/**
	 * Stores the ACF Field config array
	 * @var array
	 */
	public $field_config;

	/**
	 * Stores the ACF Field Group Config array for the field group the field belongs to
	 * @var array
	 */
	public $field_group;

	/**
	 * The GraphQL Name of the field
	 * @var string
	 */
	public $field_name;

	/**
	 * The internal ACF Type of the field (checkbox, text, select, etc)
	 * @var mixed
	 */
	public $field_type;

	/**
	 * The ACF Registry
	 *
	 * @var Registry
	 */
	public $registry;

	/**
	 * Whether the field should format when using get_field() to ask ACF for the field value
	 * @var false
	 */
	public $should_format;

	/**
	 * AcfField constructor.
	 *
	 * @param array    $field
	 * @param array    $field_group
	 * @param Registry $registry
	 */
	public function __construct( array $field, array $field_group, Registry $registry ) {

		$this->registry      = $registry;
		$this->field_group   = $field_group;
		$this->field_config  = $field;
		$this->should_format = false;
		$this->field_name    = isset( $this->field_config['graphql_field_name'] ) ? Utils::format_field_name( $this->field_config['graphql_field_name'] ) : Utils::format_field_name( $this->field_config['name'] );
		$this->field_type    = $this->field_config['type'];
		return $this;
	}

	/**
	 * Get the field name for the GraphQL Field mapped to the schema
	 *
	 * @return string
	 */
	public function get_field_name() {
		return $this->field_name;
	}

	/**
	 * Given a node and an ACF Field Config, this determines the ID to use to resolve the field
	 *
	 * @param mixed $node      The node the field belongs to
	 * @param array $acf_field The ACF Field config
	 *
	 * @return int|mixed|string
	 */
	public function get_acf_node_id( $node, array $acf_field ) {

		if ( is_array( $node ) && isset( $node['node'] ) && isset( $node['node']->ID ) ) {
			return absint( $node['node']->ID );
		}

		switch ( true ) {
			case $node instanceof Term:
				$id = 'term_' . $node->term_id;
				break;
			case $node instanceof Post:
				$id = absint( $node->databaseId );
				break;
			case $node instanceof MenuItem:
				$id = absint( $node->menuItemId );
				break;
			case $node instanceof Menu:
				$id = 'term_' . $node->menuId;
				break;
			case $node instanceof User:
				$id = 'user_' . absint( $node->userId );
				break;
			case $node instanceof Comment:
				$id = 'comment_' . absint( $node->databaseId );
				break;
			case is_array( $node ) && isset ( $node['post_id'] ) && 'options' === $node['post_id']:
				$id = $node['post_id'];
				break;
			default:
				$id = 0;
				break;
		}

		return $id;

	}

	/**
	 * Returns the GraphQL Type of the parent field group
	 *
	 * @return string
	 */
	public function get_parent_type() {
		return $this->registry->get_field_group_type_name( $this->field_group );
	}

	/**
	 * Returns the name of the Parent Type's fields Interface
	 *
	 * @return mixed|string|null
	 */
	public function get_parent_type_fields_interface() {
		return ! empty( $this->get_parent_type() ) ? 'With_' . $this->get_parent_type() . '_Fields' : null;
	}

	/**
	 * Returns the config array for the ACF Field
	 *
	 * @return array
	 */
	public function get_field_config() {
		return $this->field_config;
	}

	/**
	 * Determine if the field should ask ACF to format the response when retrieving
	 * the field using get_field()
	 *
	 * @return bool
	 */
	public function should_format_field_value() {

		if ( 'wysiwyg' === $this->field_type || 'select' === $this->field_type ) {
			$this->should_format = true;
		}

		return $this->should_format;
	}

	/**
	 * Get the GraphQL Type to return for the field
	 *
	 * @return string|string[]
	 */
	public function get_graphql_type() {

		switch ( $this->field_config['type'] ) {
			case 'number':
			case 'range':
				$type = 'float';
				break;
			case 'true_false':
				$type = 'boolean';
				break;
			case 'link':
				$type = 'AcfLink';
				break;
			case 'checkbox':
				$type = [ 'list_of' => 'String' ];
				break;
			case 'gallery':
			case 'date_picker':
			case 'time_picker':
			case 'date_time_picker':
			case 'button_group':
			case 'color_picker':
			case 'email':
			case 'text':
			case 'message':
			case 'oembed':
			case 'password':
			case 'wysiwyg':
			case 'url':
			case 'textarea':
			case 'radio':
			default:
				$type = 'String';
				break;
		}

		return $type;
	}

	/**
	 * Extending classes must implement this function
	 *
	 * @param mixed       $node    The node the field being resolved is connected with
	 * @param array       $args    The arguments passed to the field in the GraphQL query
	 * @param AppContext  $context The AppContext passed down to all resolvers
	 * @param ResolveInfo $info    The ResolveInfo passed down to all resolvers
	 *
	 * @return mixed
	 */
	public function resolve( $node, array $args, AppContext $context, ResolveInfo $info ) {

		// If the node is an array, and the type is options page, return the value early
		// 🤔 This seems fragile 🤔
		if ( is_array( $node ) && ! ( ! empty( $node['type'] ) && 'options_page' === $node['type'] ) ) {

			if ( isset( $node[ $this->field_config['key'] ] ) ) {
				$value = $node[ $this->field_config['key'] ];

				if ( 'wysiwyg' === $this->field_config['type'] ) {
					return apply_filters( 'the_content', $value );
				}

			}
		}

		$node_id = $this->get_acf_node_id( $node, $this->field_config );
		$value   = null;

		if ( is_array( $node ) && isset( $node[ $this->field_config['key'] ] ) ) {
			return $this->prepare_acf_field_value( $node[ $this->field_config['key'] ], $node, $node_id );
		}

		if ( empty( $node_id ) ) {
			return null;
		}

		/**
		 * Filter the field value before resolving.
		 *
		 * @param mixed            $value     The value of the ACF Field stored on the node
		 * @param mixed            $node      The object the field is connected to
		 * @param mixed|string|int $node_id   The ACF ID of the node to resolve the field with
		 * @param array            $acf_field The ACF Field config
		 * @param bool             $format    Whether to apply formatting to the field
		 */
		$value = apply_filters( 'graphql_acf_pre_resolve_acf_field', $value, $node, $node_id, $this->get_field_config(), $this->should_format_field_value() );

		if ( empty( $value ) ) {

			/**
			 * Check if cloned field and retrieve the key accordingly.
			 */
			if ( ! empty( $this->field_config['_clone'] ) ) {
				$key = $this->field_config['__key'];
			} else {
				$key = $this->field_config['key'];
			}

			$value = get_field( $key, $node_id, $this->should_format_field_value() );

		}

		$value = $this->prepare_acf_field_value( $value, $node, $node_id );

		/**
		 * Filters the returned ACF field value
		 *
		 * @param mixed $value     The resolved ACF field value
		 * @param array $acf_field The ACF field config
		 * @param mixed $node      The node being resolved. The ID is typically a property of this object.
		 * @param int   $node_id   The ID of the node
		 */
		return apply_filters( 'graphql_acf_field_value', $value, $this->field_config, $node, $node_id );

	}

	/**
	 * Prepares the ACF Field Value to be returned.
	 *
	 * @param mixed            $value   The value of the ACF field to return
	 * @param mixed            $node    The node the field belongs to
	 * @param mixed|string|int $node_id The ID of the node the field belongs to
	 *
	 * @return mixed
	 */
	public function prepare_acf_field_value( $value, $node, $node_id ) {

		if ( isset( $this->field_config['new_lines'] ) ) {
			if ( 'wpautop' === $this->field_config['new_lines'] ) {
				$value = wpautop( $value );
			}
			if ( 'br' === $this->field_config['new_lines'] ) {
				$value = nl2br( $value );
			}
		}

		// @todo: This was ported over, but I'm not 💯 sure what this is solving and
		// why it's only applied on options pages and not other pages 🤔
		if ( is_array( $node ) && ! ( ! empty( $node['type'] ) && 'options_page' === $node['type'] ) ) {

			if ( isset( $root[ $this->field_config['key'] ] ) ) {
				$value = $root[ $this->field_config['key'] ];
				if ( 'wysiwyg' === $this->field_config['type'] ) {
					$value = apply_filters( 'the_content', $value );
				}

			}
		}

		if ( in_array( $this->field_type, [
			'date_picker',
			'time_picker',
			'date_time_picker'
		], true ) ) {

			if ( ! empty( $value ) && isset( $this->field_config['return_format'] ) && ! empty( $this->field_config['return_format'] ) ) {
				$value = date( $this->field_config['return_format'], strtotime( $value ) );
			}
		}

		if ( in_array( $this->field_type, [ 'number', 'range' ], true ) ) {
			return (float) $value ?: null;
		}

		return $value;
	}

	/**
	 * Registers a field to the WPGraphQL Schema
	 *
	 * @return array
	 */
	public function get_graphql_field_config() {

		if ( ! empty( $this->get_graphql_type() ) ) {

			return [
				'type'    => $this->get_graphql_type(),
				'resolve' => function( $source, $args, $context, $info ) {
					return $this->resolve( $source, $args, $context, $info );
				}
			];
		}

		return null;
	}

}
