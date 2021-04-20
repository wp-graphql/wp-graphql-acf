<?php
namespace WPGraphQL\ACF\Fields;

use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

class Relationship extends AcfField {

	public function get_graphql_type() {

		$type_name = $this->get_parent_type();

		$type_registry = $this->registry->get_type_registry();

		$connection_config = [
			'fromType' => $type_name,
			'toType' => 'ContentNode',
			'fromFieldName' => $this->field_name,
			'resolve' => function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! is_array( $value ) ) {
					return null;
				}

				$value = array_map(function( $id ) {
					return absint( $id );
				}, $value );

				$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'any' );
				return $resolver
					->set_query_arg( 'post__in', $value )
					->set_query_arg( 'orderby', 'post__in' )
					->get_connection();
			}
		];

		$type_registry->register_connection( $connection_config );




	}

}
