<?php
namespace WPGraphQL\ACF\Fields;

use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

class Gallery extends AcfField {

	public function get_graphql_type() {

		$type_name = $this->get_parent_type_fields_interface();

		$type_registry = $this->registry->get_type_registry();

		$connection_name = $this->registry->get_connection_name( $type_name, 'MediaItem', $this->field_name );

		// If the connection already exists, don't register it again
		if ( null !== $type_registry->get_type( $connection_name ) ) {
			return $type_registry->get_type( $connection_name );
		}

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
