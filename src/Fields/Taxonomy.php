<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;

/**
 * Class Taxonomy
 *
 * @package WPGraphQL\ACF\Fields
 */
class Taxonomy extends AcfField {

	/**
	 * Determines the GraphQL Type for Taxonomy Fields
	 *
	 * @return void
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$type_name = $this->get_parent_type_fields_interface();

		$type_registry = $this->registry->get_type_registry();

		$connection_config = [
			'fromType' => $type_name,
			'toType' => 'TermNode',
			'fromFieldName' => $this->field_name,
			'resolve' => function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! is_array( $value ) ) {
					return null;
				}

				$value = array_map(function( $id ) {
					return absint( $id );
				}, $value );

				$resolver = new TermObjectConnectionResolver( $root, $args, $context, $info );
				return $resolver
					->set_query_arg( 'include', $value )
					->set_query_arg( 'orderby', 'include' )
					->get_connection();
			}
		];

		$connection_name = $this->registry->get_connection_name( $type_name, 'TermNode', $this->field_name );

		// If the connection already exists, don't register it again
		if ( null !== $type_registry->get_type( $connection_name ) ) {
			return $type_registry->get_type( $connection_name );
		}

		$type_registry->register_connection( $connection_config );

	}

}
