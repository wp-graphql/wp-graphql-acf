<?php
namespace WPGraphQL\ACF\Fields;

use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

class Gallery extends AcfField {

	public function get_graphql_type() {

		$type_name = $this->get_parent_type();

		$type_registry = $this->registry->get_type_registry();

		$type_registry->register_connection([
			'fromType' => $type_name,
			'toType' => 'MediaItem',
			'fromFieldName' => $this->field_name,
			'oneToOne' => false,
			'resolve' => function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! is_array( $value ) ) {
					return null;
				}

				$value = array_map(function( $id ) {
					return absint( $id );
				}, $value );

				$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'attachment' );
				return $resolver
					->set_query_arg( 'post__in', $value )
					->set_query_arg( 'orderby', 'post__in' )
					->get_connection();
			}
		]);

		return null;
	}

}
