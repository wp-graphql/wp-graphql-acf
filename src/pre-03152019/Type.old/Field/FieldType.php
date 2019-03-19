<?php
namespace WPGraphQL\Extensions\ACF\Type\Field;

use \WPGraphQL\Extensions\ACF\Types as ACFTypes;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

class FieldType extends WPObjectType {

	private static $fields;
	private static $type_name;
	private static $type;

	public function __construct( $type ) {

		/**
		 * Set the name of the field
		 */
		self::$type = $type;
		self::$type_name = ! empty( self::$type['graphql_label'] ) ? 'acf' . ucwords( self::$type['graphql_label'] ) . 'Field' : null;

		/**
		 * Merge the fields passed through the config with the default fields
		 */
		$config = [
			'name' => self::$type_name,
			'fields' => self::fields( self::$type ),
			// Translators: the placeholder is the name of the ACF Field type
			'description' => sprintf( __( 'ACF Field of the %s type', 'wp-graphql-acf' ), self::$type_name ),
			'interfaces'  => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	private function fields( $type ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields[ $type['graphql_label'] ] ) ) {

			self::$fields[ $type['graphql_label'] ] = function() use ( $type ) {

				$fields = [
					'id' => [
						'type' => Types::non_null( Types::id() ),
						'description' => __( 'The global ID for the field', 'wp-graphql-acf' ),
						'resolve' => function( array $field, array $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $field['ID'] ) && absint( $field['ID'] ) ) ? Relay::toGlobalId( self::$type_name, $field['ID'] ) : null;
						},
					],
					$type['graphql_label'] . 'Id' => [
						'type' => Types::non_null( Types::int() ),
						'description' => __( 'The database ID for the field', 'wp-graphql-acf' ),
						'resolve' => function( array $field ) {
							return ( ! empty( $field['ID'] ) && absint( $field['ID'] ) ) ? absint( $field['ID'] ) : null;
						},
					],
					'label' => [
						'type' => Types::non_null( Types::string() ),
						'description' => __( 'This is the name which will appear on the EDIT page', 'wp-graphql-acf' ),
					],
					'name' => [
						'type' => Types::non_null( Types::string() ),
						'description' => __( 'The name of the field. Single word, no spaces. Underscores and dashes allowed.', 'wp-graphql-acf' ),
					],
					'instructions' => [
						'type' => Types::string(),
						'description' => __( 'Instructions for authors. Shown when submitting data', 'wp-graphql-acf' ),
					],
					'prefix' => [
						'type' => Types::string(),
					],
					'value' => [
						'type' => Types::string(),
						'resolve' => function( array $field ) {
							return get_field( $field['key'], $field['object_id'], true );
						},
					],
//					'order' => [],
					'required' => [
						'type' => Types::boolean(),
					],
					'key' => [
						'type' => Types::string(),
					],
					'class' => [
						'type' => Types::string(),
					],
					// @todo: Add conditional logic
					'group' => [
						'type' => ACFTypes::field_group_type(),
						'description' => __( 'The field group this field is part of', 'wp-graphql-acf' ),
						'resolve' => function( array $field ) {
							$field_group = acf_get_field_group( $field['parent'] );
							return ! empty( $field_group ) ? $field_group : null;
						},
					],
				];

				return self::prepare_fields( $fields, $type['graphql_label'] );

			};

		} // End if().

		return ! empty( self::$fields[ $type['graphql_label'] ] ) ? self::$fields[ $type['graphql_label'] ] : null;

	}

}
