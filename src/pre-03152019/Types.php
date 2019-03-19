<?php
namespace WPGraphQL\Extensions\ACF;

use WPGraphQL\Extensions\ACF\Type\Field\FieldType;
use WPGraphQL\Extensions\ACF\Type\FieldGroup\FieldGroupType;
use WPGraphQL\Extensions\ACF\Type\LocationRule\LocationRuleType;
use WPGraphQL\Extensions\ACF\Type\Union\FieldUnionType;


class Types {

	private static $field_group_type;
	private static $location_rule_type;
	private static $field_type;
	private static $field_union_type;

	public static function field_group_type() {
		return self::$field_group_type ? : ( self::$field_group_type = new FieldGroupType() );
	}

	public static function location_rule_type() {
		return self::$location_rule_type ? : ( self::$location_rule_type = new LocationRuleType() );
	}

	public static function field_type( $type ) {

		if ( null === self::$field_type ) {
			self::$field_type = [];
		}

		if ( ! empty( $type['graphql_label'] ) && empty( self::$field_type[ $type['graphql_label'] ] ) ) {
			self::$field_type[ $type['graphql_label'] ] = new FieldType( $type );
		}

		return ! empty( self::$field_type[ $type['graphql_label'] ] ) ? self::$field_type[ $type['graphql_label'] ] : null;

	}

	public static function field_union_type() {
		return self::$field_union_type ? : ( self::$field_union_type = new FieldUnionType() );
	}
}
