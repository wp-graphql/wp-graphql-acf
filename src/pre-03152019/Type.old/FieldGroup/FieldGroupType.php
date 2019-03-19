<?php
namespace WPGraphQL\Extensions\ACF\Type\FieldGroup;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;
use \WPGraphQL\Extensions\ACF\Types as ACFTypes;

class FieldGroupType extends WPObjectType {

	private static $type_name;
	private static $fields;

	public function __construct() {

		self::$type_name = 'fieldGroup';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'A Group of fields', 'wp-graphql-acf' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {

				$fields = [
					'id' => [
						'type' => Types::non_null( Types::id() ),
						'description' => __( 'The global ID for the field', 'wp-graphql-acf' ),
						'resolve' => function( array $group, array $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $group['ID'] ) && absint( $group['ID'] ) ) ? Relay::toGlobalId( self::$type_name, absint( $group['ID'] ) ) : null;
						},
					],
					self::$type_name . 'Id' => [
						'type' => Types::non_null( Types::int() ),
						'description' => __( 'The database ID for the field', 'wp-graphql-acf' ),
						'resolve' => function( array $group ) {
							return ( ! empty( $group['ID'] ) && absint( $group['ID'] ) ) ? absint( $group['ID'] ) : null;
						},
					],
					'key' => [
						'type' => Types::string(),
					],
					'title' => [
						'type' => Types::string(),
					],
					// @todo: add "fields" which should be a list_of( acf_field_union )
					'locationRules' => [
						'type' => Types::list_of( ACFTypes::location_rule_type() ),
						'description' => __( 'Rules to determine which edit screens will use these advanced custom fields', 'wp-graphql-acf' ),
						'resolve' => function( array $group ) {
							return ! empty( $group['location'][0] ) ? $group['location'][0] : null;
						},
					],
					'order' => [
						'type' => Types::int(),
						'description' => __( 'Field groups with a lower order will appear first', 'wp-graphql-acf' ),
						'resolve' => function( array $group ) {
							return ( ! empty( $group['menu_order'] ) && absint( $group['menu_order'] ) ) ? absint( $group['menu_order'] ) : null;
						},
					],
					// @todo: convert position to enum
					'position' => [
						'type' => Types::string(),
					],
					// @todo: convert style to enum
					'style' => [
						'type' => Types::string(),
					],
					// @todo: convert labelPlacement to enum
					'labelPlacement' => [
						'type' => Types::string(),
						'resolve' => function( array $group ) {
							return ! empty( $group['label_placement'] ) ? $group['label_placement'] : null;
						},
					],
					// @todo: convert instructionPlacement to enum
					'instructionPlacement' => [
						'type' => Types::string(),
						'resolve' => function( array $group ) {
							return ! empty( $group['instruction_placement'] ) ? $group['instruction_placement'] : null;
						},
					],
					// @todo: convert hideOnScreen to enum
					'hideOnScreen' => [
						'type' => Types::boolean(),
						'resolve' => function( array $group ) {
							return false !== $group['hide_on_screen'] ? true : false;
						},
					],
					'active' => [
						'type' => Types::boolean(),
					],
					'description' => [
						'type' => Types::string(),
					],
					'fields' => [
						'type' => Types::list_of( ACFTypes::field_union_type() ),
						'resolve' => function( array $group ) {
							$fields = acf_get_fields( $group['ID'] );
							return ! empty( $fields ) ? $fields : null;
						},
					],
				];
				return self::prepare_fields( $fields, self::$type_name );

			};

		} // End if().

		return ! empty( self::$fields ) ? self::$fields : null;

	}

}
