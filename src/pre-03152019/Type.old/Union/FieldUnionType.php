<?php
namespace WPGraphQL\Extensions\ACF\Type\Union;

use GraphQL\Type\Definition\UnionType;
use WPGraphQL\Extensions\ACF\Types as ACFTypes;

class FieldUnionType extends UnionType {

	private static $possible_types;

	public function __construct() {

		$config = [
			'name' => 'fieldUnion',
			'types' => function() {
				return self::getPossibleTypes();
			},
			'resolveType' => function( $field ) {
				return ! empty( $field ) ? ACFTypes::field_type( $field ) : null;
			},
		];

		parent::__construct( $config );
	}

	public function getPossibleTypes() {

		if ( null === self::$possible_types ) {
			self::$possible_types = [];
		}

		$acf_field_types = acf_get_field_types();

		if ( ! empty( $acf_field_types ) && is_array( $acf_field_types ) ) {
			foreach ( $acf_field_types as $type_key => $type ) {
				if ( ! empty( $type['graphql_label'] ) && empty( self::$possible_types[ $type['graphql_label'] ] ) ) {
					self::$possible_types[ $type['graphql_label'] ] = ACFTypes::field_type( $type );
				}
			}
		}

		return ! empty( self::$possible_types ) ? self::$possible_types : null;

	}

	public function getResolveTypeFn() {
		return $this->resolveTypeFn;
	}

}
